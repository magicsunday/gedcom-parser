# Plan — Honour HEAD.CHAR / BOM encoding (#10)

## Problem

`Reader` reads raw bytes and never transcodes to UTF-8 (`// TODO`). GEDCOM 5.5.1 allows
**ANSEL** (default), **UTF-8**, **UNICODE** (16-bit UCS-2 per §UNICODE — a BMP-only superset of
which UTF-16 is a safe decoder), **ASCII**. Non-UTF-8 files decode as mojibake. `HEAD.CHAR` is
parsed into the model but never drives decoding. `test/files/ansel.ged` (1 CHAR ANSEL, CR endings,
1508 combining-diacritic bytes, self-labelling each char incl. its target Unicode scalar) cannot
be read correctly today.

## Platform facilities (use, don't hand-roll — [[feedback_platform_facility_and_all_users_first]])

Buildbox has `ext-mbstring`, `ext-iconv`, `ext-intl` (verified). UTF-16→UTF-8 =
`mb_convert_encoding(..., 'UTF-8', 'UTF-16LE'|'UTF-16BE')`; NFC = `Normalizer::normalize($s,
Normalizer::FORM_C)`. ANSEL has **no** extension → the ONLY hand-rolled table, from the normative
**ANSI Z39.47-1985 / MARC-8 Latin** repertoire (the `ansel.ged` labels are a strong oracle for the
combining marks — each states its target scalar, e.g. `E2 → acute U+0301` — but only English names
for the base letters, so resolve base code points from Z39.47, not the fixture alone).

## Detection — resolve the encoding UPFRONT, before emitting any line

The stream is non-seekable (#12), so there is no rewind-and-sniff: the Reader must decide the
encoding from the **buffered head** on the first `read()`, before it decodes/emits a single line.
This is the key correctness fix — the earlier "default-ANSEL, switch on the CHAR line" design
mojibakes any non-ASCII value that appears **before** `CHAR`, and 5.5.1 puts `SOUR`/`CORP`/`COPR`/
`FILE` (all free-text, routinely non-ASCII) **before** the (required) `CHAR` field, so this is real,
not theoretical (and `ansel.ged`'s line-2 CHAR would hide it).

`resolveEncoding()` (run once, lazily, on the first `read()`, after buffering ≥ a small prime of
bytes — loop `read()` until ≥ 4 bytes are buffered or EOF, to survive 1-byte/non-blocking reads):

1. **BOM** on the leading bytes: `EF BB BF` → UTF-8 (consume 3); `FF FE` → UTF-16LE (consume 2);
   `FE FF` → UTF-16BE (consume 2).
2. **BOM-less UTF-16 null heuristic** (5.5.1 predates the BOM convention and does not mandate one;
   a null-interleaved stream never yields a matchable `CHAR` line): first two bytes `00 XX` →
   UTF-16BE, `XX 00` → UTF-16LE (XX a plausible structural byte, i.e. `0x30`='0' or a digit/space).
3. **Bounded raw-byte `CHAR` pre-scan** (single-byte stream): keep buffering (cap at
   `CHAR_SNIFF_LIMIT`, e.g. 64 KiB, or the first level-0 record after HEAD) and match
   `^\s*\d+\s+CHAR\s+(\S+)`mi on the **undecoded** bytes — level/tag framing is 0x00–0x7F under
   ANSEL/ASCII/UTF-8 alike, so the raw scan is valid. Map `ANSEL`/`ASCII`/`UTF-8`/`UNICODE`
   (a BOM-less `CHAR UNICODE` that reached here without the null heuristic → treat as ANSEL-framed
   error, throw a specific `UnsupportedEncodingException`).
4. **Absent CHAR** → default **ANSEL** (the 5.5.1 default; CHAR is actually *required*, so this is
   malformed-input recovery — acceptable, no throw).

## Transcode paths

- **UTF-8 / ASCII** → pass-through (BOM already consumed at detection). No per-line BOM strip needed.
- **UTF-16 (LE/BE)** — transcode raw chunks to UTF-8 **before** the terminator scan, so everything
  downstream is single-byte UTF-8:
  - Accumulate raw bytes in `$utf16Pending`. Transcode only up to the last **complete Unicode
    scalar**: an even byte count, and if the last 16-bit code unit is a high surrogate
    (0xD800–0xDBFF) hold those 2 bytes too (a split surrogate pair straddling a chunk boundary
    would otherwise corrupt an astral char — carry the *scalar*, not just the code unit). Carry the
    remainder (≤ 3 bytes).
  - `mb_convert_encoding($completePrefix, 'UTF-8', $enc)`; append the UTF-8 to `$buffer`.
  - Gate the empty-read/eof retry on the **raw** `read()` returning `''` (not the transcoded output
    — a chunk fully consumed into the carry transcodes to `''` but is not EOF).
  - At true EOF a leftover `$utf16Pending` (truncated/odd) is malformed: best-effort
    `mb_convert_encoding` of what remains (mbstring substitutes), then done. Pin the behaviour.
  - The BOM bytes are consumed at detection, so the decoded stream has no leading U+FEFF.
- **ANSEL** — decode the whole physical line via `AnselDecoder::decode()` **before** the `PATTERN`
  match. 0x00–0x7F pass through and NFC is identity over ASCII, so level/tag/`@`/terminator and the
  `@@`→`@` (G3) / pointer (G2) handling stay intact; only value bytes transcode.

## `MagicSunday\Gedcom\Encoding\AnselDecoder` (final, `decode(string): string`)

Full **256-byte-defined** behaviour (an undefined high byte must not leak raw into "UTF-8" output —
that breaks `mb_check_encoding`):
- **0x00–0x7F** → identity (ASCII).
- **BASE graphic letters/symbols** (Z39.47: `0xA1`=Ł, `0xA2`=Ø, `0xA4`=Þ, `0xB1`=ł, `0xCF`=ß, … the
  full 0xA1–0xC5/0xC0–0xCF ranges) → one Unicode scalar each.
- **COMBINING diacritics** (0xE0–0xFF; NOT all of Appendix C — include the fixture-exercised
  0xE0 hook, 0xEB/0xEC ligature halves → U+FE20/U+FE21, 0xEF candrabindu, 0xF2–0xFF) → Unicode
  combining marks. ANSEL stores the mark **before** the base; buffer consecutive marks and flush as
  `base + marks` (Unicode order), preserving stacking order per Z39.47, then rely on NFC canonical
  reordering only for differing combining classes.
- **Non-sorting delimiters 0x8D/0x8E and the fill character 0x8F** → stripped (produce no output),
  per Z39.47/MARC-8.
- **Every other unmapped byte (0x80–0xA0 controls, 0xD0–0xDF, gaps)** → deterministic fallback:
  U+FFFD replacement (never raw). Log nothing (parser is quiet).
- Finish with `Normalizer::normalize($out, Normalizer::FORM_C)`.
- **Dangling trailing mark** (mark with no following base — malformed / CONC-split, see below): emit
  the standalone combining mark applied to U+0020 (space) so output stays valid UTF-8. Pin a test.

## Known limitation (documented, pinned)

A non-spacing mark split across a **CONC/CONT** boundary (mark = last byte of line N, base = first
byte of the continuation N+1) cannot compose: the Reader decodes per physical line, but CONC/CONT
concatenation happens later in the parser. Rare (sender-discretion line break mid-diacritic-pair).
Pin the current behaviour with a test and note the correct fix (decode values after CONC/CONT
assembly) lands with the typed-value-object work (#25/#20). File a follow-up ticket.

## `composer.json`

Add the runtime deps actually used: `ext-mbstring`, `ext-intl` (Normalizer). Not `ext-iconv` unless
used.

## Tests (TDD)

1. `AnselDecoderTest` — table-driven: base chars vs the Z39.47 code points (`0xCF`→`ß`, `0xA4`→`Þ`,
   `0xA2`→`Ø`, …); combining mark-before-base → precomposed (`E2 65`→`é`); stacked marks; EB/EC
   ligature halves → U+FE20/FE21; non-sort/fill stripped; an unmapped byte → U+FFFD; ASCII
   pass-through; every output is valid UTF-8; dangling trailing mark.
2. `parsesAnselFixtureToUtf8` — parse `test/files/ansel.ged`; assert a self-labelled value decodes
   to its named scalar (the "es zet" place contains `ß`), and the whole parse is valid UTF-8.
3. `parsesUtf16` — UTF-16 **LE and BE**, **with and without** a BOM (null heuristic), each parses to
   the same records as its UTF-8 twin; a non-ASCII value round-trips; an **astral char at a chunk
   boundary** (via the one-byte-read stream double) is not corrupted (guards the surrogate carry).
4. `detectsFromBomBeforeChar` / `resolvesCharAfterNonAsciiHeaderField` (a no-BOM UTF-8 file with a
   non-ASCII `COPR`/`FILE` **before** `CHAR UTF-8` decodes correctly — the discriminator the fixture
   hides) / `defaultsToAnselWhenCharAbsent` / `passthroughForAscii`.
5. `concSplitDiacriticIsAKnownLimitation` — pins the documented gap.
6. Regression: `allged.ged`, all `LTER*`, `simple.ged` parse unchanged.

## Risks (all raised in plan review, now designed for)

- Pre-CHAR non-ASCII → **upfront pre-scan** (not mid-stream switch).
- BOM-less UTF-16 → **null heuristic**.
- Split surrogate pair across a chunk → **carry the last complete scalar**, plus astral-boundary test.
- UTF-16 transcode-to-empty vs EOF → gate on **raw** read empty.
- 256-byte completeness → every byte defined, U+FFFD fallback, non-sort/fill stripped.
- CONC-split diacritic → documented limitation + pinned test + follow-up ticket.
- `back()`/push-back holds a decoded line → harmless now, because the encoding is fixed **before**
  any line is emitted.

## Out of scope

- GEDCOM 7.0 (UTF-8 only) `CHAR` handling — trivial, lands with #19.
- Writing/encoding GEDCOM (read-only parser).
- Correct CONC-split-diacritic composition — deferred to #25/#20 (value-object assembly).
