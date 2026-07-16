[![Latest version](https://img.shields.io/github/v/release/magicsunday/gedcom-parser?sort=semver)](https://github.com/magicsunday/gedcom-parser/releases/latest)
[![License](https://img.shields.io/github/license/magicsunday/gedcom-parser)](https://github.com/magicsunday/gedcom-parser/blob/main/LICENSE)
[![CI](https://github.com/magicsunday/gedcom-parser/actions/workflows/ci.yml/badge.svg)](https://github.com/magicsunday/gedcom-parser/actions/workflows/ci.yml)
[![Security](https://github.com/magicsunday/gedcom-parser/actions/workflows/security.yml/badge.svg)](https://github.com/magicsunday/gedcom-parser/actions/workflows/security.yml)


# GEDCOM parser
A [GEDCOM](https://en.wikipedia.org/wiki/GEDCOM) 5.5.1 and 7.0 file parser for PHP. It reads
a GEDCOM stream (or a GEDZIP `.gdz` archive) line by line and exposes the records
(individuals, families, sources, notes, …) as a fully typed object model, detecting the
document version from its own header.


## Requirements
- PHP 8.3 or newer (verified on 8.3, 8.4 and 8.5)
- The `ext-mbstring`, `ext-intl` and `ext-iconv` extensions (used to transcode the source encoding to UTF-8)
- The `ext-zip` extension (used to read GEDZIP `.gdz` archives)


## Installation
Install with [Composer](https://getcomposer.org/):

```shell
composer require magicsunday/gedcom-parser
```

To remove the parser again:

```shell
composer remove magicsunday/gedcom-parser
```


## Usage
Create a stream for your GEDCOM file, parse it, and traverse the resulting model:

```php
<?php

use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\StreamFactory;

require 'vendor/autoload.php';

$stream   = (new StreamFactory())->createStreamFromFile('/path/to/your/tree.ged');
$document = (new Parser($stream))->parse();

foreach ($document->individuals as $individual) {
    $name = $individual->name[0] ?? null;

    echo $individual->xref, ': ', $name ? $name->getDisplayName() : '(unknown)', "\n";
}
```

A GEDCOM 7.0 dataset may also be packaged as a **GEDZIP** (`.gdz`) archive — a ZIP container whose
mandated `gedcom.ged` entry carries the dataset alongside any embedded media files. Read one with
`GedcomZipReader`, which extracts that entry and returns the same typed `GedcomDocument`:

```php
use MagicSunday\Gedcom\GedcomZipReader;

$document = GedcomZipReader::readFile('/path/to/your/tree.gdz');
```

`GedcomZipReader::read()` accepts a PSR-7 stream instead of a path (it is spooled to a temporary
file, since the ZIP facility needs a seekable source). Reading GEDZIP requires the `ext-zip`
extension.

To also read the archive's embedded media, open it with `GedcomZipReader::openArchive()`, which
returns a `GedcomArchive` handle exposing the parsed document and resolving an `OBJE.FILE` reference
to a stream over its embedded entry. Close the handle when done — the media streams read lazily from
the archive:

```php
$archive = GedcomZipReader::openArchive('/path/to/your/tree.gdz');

try {
    foreach ($archive->getDocument()->multimedia as $object) {
        foreach ($object->file as $file) {
            $media = $archive->openFile($file->value); // null for a web URL or an unresolved reference
        }
    }
} finally {
    $archive->close();
}
```

`openFile()` returns `null` when the reference is a web/`file:` URL, an absolute or traversing path,
or names an entry the archive does not contain.

`Parser::parse()` returns the typed `GedcomDocument` aggregate — it detects the GEDCOM version
from the header and maps the standard records (INDI, FAM, SOUR, NOTE / the GEDCOM 7.0 shared-note
`SNOTE`, REPO, OBJE, SUBM) onto their typed records grouped by type (`$document->individuals`,
`->families`, `->notes`, …). The record dispatch is version-aware: a 5.5.1 document resolves its
`NOTE` record and a 7.0 document its `SNOTE`, both into `->notes`, while a cross-version tag is
tolerated and skipped rather than aborting the parse. A 7.0 shared note additionally carries its
text's language and media type (`NoteRecord::$lang` / `$mime`) and any translations of the text
(`$tran`, a list of typed `NoteTranslation`s), all left empty for a 5.5.1 note. A GEDCOM 7.0
document's header extension-tag
schema (`HEAD.SCHMA.TAG`) is exposed on `$document->extensionTags` as a map of each extension tag to
its declared URIs (a `list`, since 7.0 allows a tag to be documented more than once; empty for a
5.5.1 document). You can also parse an in-memory GEDCOM string with `StreamFactory::createStream()`.

#### Preserving unmodelled substructures

A substructure the typed model does not consume is not dropped: it is preserved verbatim on the
carrying object's `$unknown` list as a `MagicSunday\Gedcom\ValueObject\RawSubstructure` (`->tag`,
`->value`, `->xref`, `->children`), at every object-bearing level of the typed record model. This
covers both an extension (`_`-prefixed vendor tag such as `_WT_USER`) or out-of-place tag, and a tag
the schema *does* permit but that the carrying object does not yet model as a typed field — whether
at the **record** level (such as `OCCU` or `RESI` on an individual, landing on the record's
`$unknown`) or nested under a **modelled** substructure (such as an unmodelled tag under a birth
event, landing on that event's own `$unknown`). So an unrecognised or not-yet-modelled tag remains
reachable and walkable rather than being silently lost:

```php
foreach ($document->individuals[0]->unknown as $raw) {
    echo $raw->tag, ' = ', $raw->value ?? '', "\n"; // e.g. "_CUSTOM = …" or "OCCU = Baker"
}
```

The parsed value-object leaves (`DATE`/`PLAC`/`AGE`) also carry an `$unknown` list, so an
out-of-schema tag directly beneath one is preserved on that value object too (e.g.
`$individual->birt[0]->plac->unknown`) — wherever the leaf is shaped as a structured object (a
`PLAC` always; a `DATE`/`AGE` in GEDCOM 7.0, where they declare substructures).

Two narrow boundaries remain: a **scalar** field (such as `SEX`, a bare `?string`) has no object to
carry an `$unknown`, so a tag beneath it cannot be preserved without modelling that field as an
object; and a GEDCOM **5.5.1** `DATE`/`AGE`, which declares no substructures and is therefore shaped
as a plain string, drops a tag beneath it before it reaches the value object.

#### Bounding the parse (resource limit)

Parsing accumulates the dataset in memory, so an oversized or hostile input is a denial-of-service
surface — most sharply for GEDZIP, where a tiny archive's `gedcom.ged` entry can inflate to a huge
dataset (a decompression bomb). The reader therefore caps the total number of bytes it reads from a
source and aborts past the cap with a `MagicSunday\Gedcom\Exception\InputTooLargeException`. The cap
is enforced at the single byte-reading choke point, so it bounds every read path uniformly — the
plain `.ged` `Parser`, the version-fixed `TypedGedcomParser`, and the GEDZIP reader, where it counts
the **decompressed** entry bytes and so bounds the parse independently of the compressed transport
size.

The cap defaults to `Reader::DEFAULT_MAX_BYTES` (512 MiB) — above the range a legitimate large tree
occupies, so it does not reject real data out of the box while still turning an otherwise unbounded
parse into a bounded one. It bounds the bytes *read*, not the in-memory object graph the parse builds
(a multiple of the input size), so a service parsing untrusted input should size the cap against its
PHP `memory_limit` (roughly `memory_limit` divided by the input-to-heap expansion factor) rather than
rely on the default ceiling. It is caller-configurable through every entry point; lower it when
parsing untrusted input, or raise it (up to `PHP_INT_MAX` for effectively unbounded) for a genuinely
huge trusted tree:

```php
use MagicSunday\Gedcom\GedcomZipReader;
use MagicSunday\Gedcom\Parser;

$document = (new Parser($stream, 32 * 1024 * 1024))->parse();          // plain .ged, 32 MiB cap
$document = GedcomZipReader::readFile('/path/to/tree.gdz', 32 * 1024 * 1024); // decompressed cap
```

### Typed value objects

Genealogically structured values are exposed as typed, `final readonly` value objects (in
`MagicSunday\Gedcom\ValueObject`) alongside their raw strings, so you can sort, compare and
render them without re-parsing:

- `EventDetail::$date` (`?DateValue`) — the `DATE_VALUE` grammar (qualifiers `ABT` / `CAL` /
  `EST`, ranges `BEF` / `AFT` / `BET … AND …`, periods `FROM` / `TO`, interpreted and phrase
  dates) around one or two calendar-aware `CalendarDate`s (every GEDCOM calendar, plus `B.C.`
  and dual `1699/00` years). `CalendarDate::toJulianDay()` gives a calendar-independent Julian
  Day Number for sorting and comparison (Gregorian and Julian; the other calendars follow).
- `EventDetail::$plac` (`?PlaceValue`) — the comma-separated jurisdiction hierarchy, with a
  `mapped()` view onto the place `FORM` labels (the place's own `FORM`, or the `HEAD.PLAC.FORM`
  default the header declares once for every place that carries none) and the `MAP` geographic
  coordinates (`?MapCoordinates`, signed decimal degrees) when present.
- `EventDetail::$age` (`?AgeValue`) — the `AGE_AT_EVENT` grammar (`< 8y`, `72y 3m 2d`, the
  GEDCOM 7.0 weeks unit `8w`, `CHILD` / `INFANT` / `STILLBORN`).

Each property is `null` when its tag is absent or empty; the parsed object keeps the original
raw text.

The parser reads any readable stream — including non-seekable ones such as a pipe
(`cat tree.ged | your-app`) or a network response body — and accepts all four GEDCOM 5.5.1
line terminators (CR, LF, CRLF and LFCR), so classic-Mac (CR-only) files parse correctly.
Blank and whitespace-only lines — which some exporters append, most commonly after the
trailer — are skipped rather than mis-parsed, while line numbers in error messages stay
aligned with the physical file.

Reading a stream is bounded, so hostile or broken input cannot exhaust memory or hang the
reader: a single terminator-less line beyond `Reader::MAX_LINE_LENGTH` raises a
`LineTooLongException`, the total byte count is capped as described in
[Bounding the parse](#bounding-the-parse-resource-limit) above, and a stream that never
signals end of stream is treated as ended after a bounded number of empty reads rather than
spun forever. This matters most for the untrusted-network-stream case noted above.

The source encoding is detected from the byte-order mark or the `HEAD.CHAR` declaration and
transcoded to UTF-8: **ANSEL** (the 5.5.1 default, decoded via the bundled Z39.47 table),
**UTF-8**, **UNICODE** (UTF-16, little- or big-endian) and **ASCII**. As a real-world convenience
beyond the 5.5.1 charset set, a Windows export is honoured too: a bare `ANSI` / `WINDOWS` decodes
as Windows-1252, and an explicit codepage (`WINDOWS-1250`, `CP1257`, …) decodes with that exact
codepage when the platform's `iconv` carries it, falling back to Windows-1252 otherwise. Reading
requires the `ext-mbstring`, `ext-intl` and `ext-iconv` extensions.

Every exception the library throws implements
`MagicSunday\Gedcom\Exception\ExceptionInterface`, so all parser and stream failures can
be caught as a single group.


## Development

### Contributing
Contributor and AI-assistant guidelines — coding standards, the buildbox-based tooling,
the review workflow and the GEDCOM conformance rules — are documented in
[`AGENTS.md`](AGENTS.md). The authoritative GEDCOM specifications (5.5.1 and 7.0,
including the machine-readable 7.0 registry) are vendored under
[`docs/spec/`](docs/spec/) as the normative reference for conformance work.

### Schema-driven typed model
The parser is built on a schema-driven, fully typed model. A generic tree
reader (`MagicSunday\Gedcom\Parse`) turns the flat reader lines into an immutable node
tree; a declarative schema (`MagicSunday\Gedcom\Schema`) is compiled from the vendored
registry for either GEDCOM version; and a mapping layer (`MagicSunday\Gedcom\Mapping`)
shapes a node subtree through that schema and hydrates immutable `final readonly` records
(`MagicSunday\Gedcom\Model`) via [`magicsunday/jsonmapper`](https://github.com/magicsunday/jsonmapper),
with the value-object leaves (dates, places, ages) parsed by their own grammar. Each leaf is
resolved regardless of the GEDCOM version — a bare payload string in 5.5.1, or the shaped node
a 7.0 substructure-bearing leaf (a `DATE` with `PHRASE`/`TIME`, a `PLAC` with `FORM`/`MAP`)
produces. A GEDCOM 7.0 `DATE`/`AGE` carried only by its `PHRASE` substructure is threaded onto
the value object as a phrase rather than dropped, and a `PLAC`'s `MAP` coordinates are exposed as
signed decimal degrees. The analysis runs clean at PHPStan `level: max` with no baseline — enforcing
architecture boundaries via `phpat` — and `jscpd` finds no duplication, so both are hard CI gates.

`TypedGedcomParser` ties the pipeline together: give it the GEDCOM version and a map of
record tag to your typed record class, and it streams the level-0 records and maps each
recognised one (unmapped records such as `HEAD`/`TRLR` are skipped):

```php
use MagicSunday\Gedcom\Mapping\TypedGedcomParser;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Model\FamilyRecord;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\MultimediaRecord;
use MagicSunday\Gedcom\Model\NoteRecord;
use MagicSunday\Gedcom\Model\RepositoryRecord;
use MagicSunday\Gedcom\Model\SourceRecord;

$parser = TypedGedcomParser::create(GedcomVersion::V551, [
    'INDI' => IndividualRecord::class,
    'FAM'  => FamilyRecord::class,
    'SOUR' => SourceRecord::class,
    'NOTE' => NoteRecord::class,
    'REPO' => RepositoryRecord::class,
    'OBJE' => MultimediaRecord::class,
]);

foreach ($parser->parse($stream) as $record) {
    // one typed IndividualRecord or FamilyRecord at a time, in document order (parse() yields, so
    // a large file is never held in memory); wrap in iterator_to_array() if you need the full list
}
```

When you need random access rather than a single streaming pass, `parseDocument()` drains the
same pipeline eagerly into a typed `GedcomDocument` aggregate that groups the records by their
modelled type (`$document->individuals`, `->families`, `->sources`, `->notes`, `->repositories`,
`->multimedia`, `->submitters`); a record whose type is not modelled is kept in `->others` rather
than dropped:

```php
$document = $parser->parseDocument($stream);

foreach ($document->individuals as $individual) {
    echo $individual->xref, "\n";
}
```

`TypedGedcomParser` needs the version stated up front. When you would rather let the file say which
version it is, `GedcomDocumentReader` detects it from the header's `GEDC.VERS` line (defaulting to
5.5.1 for a version-less or unrecognised header) and reads the whole stream into the same typed
`GedcomDocument` — so you only supply the record-tag map:

```php
use MagicSunday\Gedcom\Mapping\GedcomDocumentReader;

$document = GedcomDocumentReader::create([
    'INDI' => IndividualRecord::class,
    'FAM'  => FamilyRecord::class,
])->read($stream);
```

The typed record set is still growing; only the modelled records are mapped today. Currently an
`IndividualRecord` exposes its names — each a typed `PersonalName` that derives the given name,
surname and suffix from the `John /Doe/` slash convention (an explicit `GIVN`/`SURN`/`NPFX`/`SPFX`/
`NSFX`/`NICK` piece always winning) and offers a slash-free `getDisplayName()` — its sex, its
birth, death and burial events, and its child- and
spouse-to-family links (`FAMC`/`FAMS`), a `FamilyRecord` exposes its partner and child
cross-references and its marriage events — each event a typed `EventDetail` (date, place, age) —
a `SourceRecord` exposes its descriptive fields (title, author, publication, abbreviation,
text), a `NoteRecord` exposes its shared-note text, a `RepositoryRecord` exposes its name and contact
details, and a `MultimediaRecord` exposes its file references (each with a typed format and
title). A file's format classifies what it depicts version-specifically: GEDCOM 5.5.1's free-text
`MediaFormat::$type` (`TYPE`) or GEDCOM 7.0's enumerated `MediaFormat::$medi` (`MEDI`, a typed
`Medium` carrying the enumerated value plus an optional `PHRASE` for the `OTHER` medium); each stays
`null` in the other version. Enumerated GEDCOM 7.0 values (a medium, an individual's sex, a
child-to-family pedigree, a name type) stay plain strings so an extension or unlisted value is
preserved, and the known standard values of each set are available as typed constants under
`MagicSunday\Gedcom\Enumeration` (`MediumType`, `Sex`, `Pedigree`, `NameType` — e.g.
`MediumType::PHOTO`, `Sex::FEMALE`) for discoverable comparison. Every record additionally exposes its GEDCOM 7.0 record-level external
identifiers — any number of `UID` values (a `list` of raw strings) and any number of `EXID`
identifiers (each a typed `ExternalIdentifier` carrying the identifier plus an optional `TYPE`
authority URI); both stay empty for a 5.5.1 record, which cannot carry them. A record's GEDCOM 7.0
creation timestamp (`CREA`) is exposed as a typed `CreationDate` nesting an `ExactDate` — the raw
exact date and optional time strings, kept unparsed because the timestamp uses the restricted
exact-date grammar rather than the genealogical date grammar; it is `null` for a 5.5.1 record. A
record's change timestamp (`CHAN`) is exposed as a typed `ChangeDate` — the same `ExactDate` plus any
inline notes (`Note`, with their 7.0 language, media type and translations) and 7.0 shared-note
references documenting the change; unlike the creation timestamp it exists in both GEDCOM versions, so
it is populated for a 5.5.1 record too. Substructures not yet modelled are ignored rather than mapped.

### Run tests
All PHP tooling runs through the build container. Run the full check with
`composer ci:test`, or invoke the individual steps:

```shell
composer update

# everything at once
composer ci:test

# …or step by step
composer ci:test:php:lint      # phplint
composer ci:test:php:unit      # PHPUnit
composer ci:test:php:phpstan   # PHPStan (static analysis)
composer ci:test:php:rector    # Rector (dry-run)
composer ci:test:php:cgl       # php-cs-fixer (dry-run)
composer ci:test:php:cpd       # jscpd (copy/paste detection)
```

The same steps run on PHP 8.3, 8.4 and 8.5 in GitHub Actions
([`.github/workflows/ci.yml`](.github/workflows/ci.yml)). The `phpstan` and
`cpd` steps are currently non-blocking while the typed-model refactor is in
progress.
