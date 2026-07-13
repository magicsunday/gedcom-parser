# Plan — Read-path refactor (#11 CR-only line endings, #12 non-seekable streams)

## Problem

`Reader::read()` uses `Stream::fgets()` (splits on `\n` only) and `back()` uses
`Stream::seek()`. Consequences:

- **#11**: a CR-only (classic-Mac `\r`) file has no `\n`, so `fgets()` returns the whole
  file as one "line" → 0 records parsed. (CRLF / LFCR already work.)
- **#12**: `read()` starts with `if (!isSeekable) return false;` — a non-seekable stream
  (pipe, network body) silently yields an EMPTY `Gedcom` with no error.

The two are coupled: terminator-aware reading either needs seek-back of the read-ahead
excess (requires seekable → contradicts #12) or an internal buffer (breaks the Stream's
`tell()/read()` consistency for other consumers). The clean resolution for both is to make
the **Reader** own a byte buffer + line splitting over PSR-7 `read()`, and replace
`seek()`-back with a **line push-back**.

## Design

Reader state changes:
- Remove `$lastPosition`; the seek-based `back()` goes away.
- Add `private string $buffer = '';`  — unconsumed bytes read from the stream.
- Add `private array $pushback = [];` — whole lines pushed back by `back()`, LIFO.
- Add `private bool $eofReached = false;` — set once the stream is exhausted.

`read()`:
1. Reset per-line state (identifier/xref/value) as today.
2. `$this->lastLine = $this->pushback !== [] ? array_pop($this->pushback) : $this->nextLine();`
3. `++$this->lineCount` only for freshly-read lines (NOT for a re-served push-back line —
   otherwise `count()` and the BOM `lineCount === 1` guard double-count). Track with a flag:
   a push-back re-serve does not advance `lineCount`.
4. If `valid()`: BOM-strip on the very first physical line, `preg_match`, populate fields
   (unchanged from today).
5. Return `!($this->lastLine === '' && $this->eofReached)`.

`nextLine(): string` (private):
- Loop: if `$buffer` contains a **decidable** line terminator, cut and return the line
  **including** its terminator (to preserve current `current()`/value-trim behaviour). Else
  read another chunk via `$this->stream->read(self::CHUNK)`; on empty read set `$eofReached`
  and return whatever remains in `$buffer` (last line without terminator), or `''` at true
  EOF.
- **Terminator detection — explicit longest-match tie-break** (review R2/R3, both plan
  reviewers): locate the first `\r` *or* `\n` in the buffer, then peek the next byte to
  classify: `\r\n` / `\n\r` (2-byte pair) or a lone `\r` / `\n`. A naive
  `min(strpos(...))` over the four needles is WRONG — for CRLF `\r` and `\r\n` tie at the
  same offset and the 1-char match would win, cutting only `\r` and leaving an orphan `\n`
  as a phantom blank line. **At an equal earliest position the 2-char terminator wins.**
- **Chunk-boundary ambiguity — SYMMETRIC guard** (review R2, code-reviewer): a terminator
  pair can be split across a chunk read. If the buffer ends in `\r` **or `\n`** and the
  stream is not yet EOF, the trailing byte is "undecidable" (its partner may be the first
  byte of the next chunk: `\r`+`\n`, or `\n`+`\r`) — read one more chunk before deciding, so
  a `\r\n`/`\n\r` pair is never split. The earlier plan guarded only trailing `\r`; that
  misses the LFCR (`\n\r`) split (`LTERLFCR` terminates `\n\r`), which would emit a phantom
  `"\r"` line — and because `read()` does **not** reset `$level` between lines, a whitespace
  phantom line lets `AbstractParser::process()` see a stale level → mis-scoped blocks. For a
  pure-LF file the symmetric guard costs at most one harmless extra read at each real
  boundary.
- **Bounded buffer** (review R-G8, gedcom-parser-reviewer): a terminator-less / pathological
  input must not materialise the whole stream as one line (memory-unbounded, violates the
  record-by-record streaming intent). Cap the accumulated line length at a generous bound
  well above the GEDCOM 5.5.1 Appendix-A line limit (`self::MAX_LINE_LENGTH`); on overflow
  throw `UnableToParseLineException` rather than growing `$buffer` to stream size. The
  `$pushback` LIFO stays bounded (the parser backs at most one line between reads — verified
  `AbstractParser::valid()`/`readContent()` are the only `back()` sites, each followed by a
  `read()`).

`back(): bool`:
- `$this->pushback[] = $this->lastLine; return true;` — no stream access. (Incidentally
  fixes a latent bug: today's `back()` does `return $this->stream->seek(...) === 0`, but
  `Stream::seek()` returns `void`, so it always returned `false`; callers ignore it.)

Constructor (**#12, expanded — the isSeekable drop alone is NOT sufficient**):
- **Drop the `isSeekable()` requirement** — the reader no longer seeks, so any readable
  stream works.
- **Fix the `.ged`-extension guard for non-file streams** (review R1 CRITICAL,
  code-reviewer): the current guard rejects a stream when `getMetadata('stream_type') ===
  'STDIO'` and the `uri`'s last 3 chars aren't `GED`. A `popen()` pipe reports
  `stream_type='STDIO'`, `uri=null`, `seekable=false` → the guard evaluates
  `substr(null,-3) !== 'GED'` → **throws `UnsupportedFileException` in the constructor**, so
  the non-seekable pipe of test #2 never reaches the buffer loop. Enforce the `.ged`
  extension **only when a filename `uri` is actually present** (a real on-disk file); a
  non-file STDIO stream (`uri === null`: pipe, `php://memory`, network body) is accepted.
  This is the real fix for #12 and must be pinned by its own test.

`ReadableStreamInterface` / `Stream::fgets()`: the reader no longer calls `fgets()`, and
`fgets()` was the ONLY method `ReadableStreamInterface` added over PSR-7 `StreamInterface`.
Removing it would leave an empty marker interface — a KISS/YAGNI smell. Decision (deviation
from the first-draft plan, which kept the empty interface): **remove
`ReadableStreamInterface` entirely** and type `Reader`/`Parser`/`Stream`/`StreamFactory`
directly against `Psr\Http\Message\StreamInterface` (which already declares the
`read()`/`eof()`/`getMetadata()` the reader uses). `StreamFactory` then returns
`StreamInterface`, matching PSR-17 `StreamFactoryInterface` exactly (no covariant narrowing).
This fully supersedes GH-31 (whose sole purpose was the fgets typing). **BC note** (review
R4, code-reviewer): GH-31's `ReadableStreamInterface` is **unreleased** (no tag yet — the
first tagged release is #18), so removing it before it ever ships is safe. State this in the
PR body and land it before the #18 release.

## Tests (TDD, add first)

1. `parsesAllLineEndingsIdentically` — `LTERCR`/`LTERLF`/`LTERCRLF`/`LTERLFCR` fixtures all
   parse to the SAME individual count (>0). RED today for `LTERCR` (0 vs 3). (#11)
2. `acceptsNonFileStreamWithoutGedExtension` — construct a `Reader`/`Parser` over a `Stream`
   wrapping a non-file resource (`php://memory` or `popen()` pipe, `uri === null`); assert
   NO `UnsupportedFileException` in the constructor. RED today (the `.ged` guard throws on
   `uri === null`). Pins review R1 CRITICAL. (#12)
3. `parsesNonSeekableStream` — feed a non-seekable readable stream (a `popen()` pipe wrapped
   in `Stream`, `isSeekable() === false`) with a small GEDCOM; assert records parse. RED
   today (silent empty). Depends on test 2's guard fix. (#12)
4. `parsesLineEndingSplitAcrossChunkBoundary` — drive `nextLine()` with a `CHUNK` small
   enough (or a crafted fixture) that a `\r\n` **and** a `\n\r` pair each straddle a chunk
   boundary; assert the record count matches the un-split parse (no phantom blank line).
   Guards review R2 (symmetric `\r`/`\n` boundary rule + longest-match tie-break). RED with
   a naive single-`\r` guard.
5. `backReReadsThePreviousLine` (Reader-level) — read two lines, `back()`, read again → same
   line; and `count()` is not double-advanced by the re-read.
6. `throwsOnLineExceedingMaxLength` — a terminator-less oversized input throws
   `UnableToParseLineException`, not OOM. Guards review R-G8 (bounded buffer).
7. Existing `ReaderTest::back()` (currently seek-based) keeps passing behaviourally.

## Out of scope (follow-on, same PR, separate plan)

- **#10** encoding (BOM/HEAD.CHAR → UTF-8/UTF-16/ASCII/ANSEL transcoding) — planned
  separately once the read loop lands. **Layering constraint the read loop must respect**
  (review R-G6, gedcom-parser-reviewer): single-byte encodings (ANSEL/ASCII/UTF-8, all with
  0x00–0x7F identical) tokenise pre-transcode and only their *values* need transcoding — a
  per-line hook in `nextLine()` is fine for those. But `CHAR UNICODE` = UTF-16 (LE **and**
  BE) carries a **2-byte** terminator (`\n` = `0A 00` LE / `00 0A` BE); scanning the raw
  single-byte buffer for `0x0A`/`0x0D` would land mid-code-unit and mis-split every line. So
  the byte→UTF-8 boundary for multi-byte encodings must sit **below** line-splitting: detect
  UTF-16 from the leading BOM at stream start and transcode each chunk to UTF-8 **before**
  the terminator scan. The read loop built here therefore scans **UTF-8/single-byte bytes
  only**; #10 adds a chunk-level transcode in front of the scan for UTF-16, NOT a rewrite of
  `nextLine()`. Designing the scan on that assumption now keeps #10 additive.

## Risks

- `count()` / BOM `lineCount === 1` must not be perturbed by push-back re-reads — covered by
  test 5 and the flag in `read()` step 3. (`count()` has no consumer in `src/`/`test/`; the
  only visible effect is the error line number in `UnableToParseLineException`.)
- Chunk-boundary terminator split (both `\r\n` and `\n\r`) — covered by the **symmetric**
  "read one more chunk if buffer ends in `\r` or `\n`" rule + longest-match tie-break, pinned
  by test 4. A phantom blank line would mis-scope blocks because `read()` does not reset
  `$level`.
- Non-file STDIO stream rejected by the `.ged` guard before the buffer loop — the constructor
  fix (enforce extension only when a filename `uri` is present) is the actual #12 remedy,
  pinned by test 2.
- Unbounded single-line growth on pathological input — capped via `self::MAX_LINE_LENGTH`,
  pinned by test 6.
- Removing `fgets()` churns GH-31; acceptable and intentional (unreleased interface, before
  the #18 release), noted in the PR body.
- #10 (UTF-16) layering — the scan is designed for UTF-8/single-byte only; #10 transcodes
  UTF-16 chunks to UTF-8 *before* the scan (see Out of scope), so no `nextLine()` rewrite.
