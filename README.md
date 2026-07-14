[![Latest version](https://img.shields.io/github/v/release/magicsunday/gedcom-parser?sort=semver)](https://github.com/magicsunday/gedcom-parser/releases/latest)
[![License](https://img.shields.io/github/license/magicsunday/gedcom-parser)](https://github.com/magicsunday/gedcom-parser/blob/main/LICENSE)
[![CI](https://github.com/magicsunday/gedcom-parser/actions/workflows/ci.yml/badge.svg)](https://github.com/magicsunday/gedcom-parser/actions/workflows/ci.yml)
[![Security](https://github.com/magicsunday/gedcom-parser/actions/workflows/security.yml/badge.svg)](https://github.com/magicsunday/gedcom-parser/actions/workflows/security.yml)


# GEDCOM parser
A [GEDCOM](https://en.wikipedia.org/wiki/GEDCOM) 5.5.1 file parser for PHP. It reads a
GEDCOM stream line by line and exposes the records (individuals, families, sources,
notes, …) as an object model.


## Requirements
- PHP 8.3 or newer (verified on 8.3, 8.4 and 8.5)
- The `ext-mbstring` and `ext-intl` extensions (used to transcode the source encoding to UTF-8)


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

$stream = (new StreamFactory())->createStreamFromFile('/path/to/your/tree.ged');
$gedcom = (new Parser($stream))->parse();

foreach ($gedcom->getIndividual() as $individual) {
    $name = $individual->getNames()[0] ?? null;

    echo $individual->getXref(), ': ', $name ? $name->getDisplayName() : '(unknown)', "\n";
}
```

You can also parse an in-memory GEDCOM string with `StreamFactory::createStream()`.

### Typed value objects

Genealogically structured values are exposed as typed, `final readonly` value objects (in
`MagicSunday\Gedcom\ValueObject`) alongside their raw strings, so you can sort, compare and
render them without re-parsing:

- `EventDetail::getDateValue(): ?DateValue` — the `DATE_VALUE` grammar (qualifiers `ABT` /
  `CAL` / `EST`, ranges `BEF` / `AFT` / `BET … AND …`, periods `FROM` / `TO`, interpreted and
  phrase dates) around one or two calendar-aware `CalendarDate`s (every GEDCOM calendar, plus
  `B.C.` and dual `1699/00` years). `CalendarDate::toJulianDay()` gives a calendar-independent
  Julian Day Number for sorting and comparison (Gregorian and Julian; the other calendars follow).
- `PlaceStructure::getPlaceValue(): ?PlaceValue` — the comma-separated jurisdiction hierarchy,
  with a `mapped()` view onto the place `FORM` labels.
- `FamilyPersonAge`/`IndividualEventDetail::getAgeValue(): ?AgeValue` — the `AGE_AT_EVENT`
  grammar (`< 8y`, `72y 3m 2d`, `CHILD` / `INFANT` / `STILLBORN`).

Each accessor returns `null` when its tag is absent or empty; the parsed object keeps the
original raw text.

The parser reads any readable stream — including non-seekable ones such as a pipe
(`cat tree.ged | your-app`) or a network response body — and accepts all four GEDCOM 5.5.1
line terminators (CR, LF, CRLF and LFCR), so classic-Mac (CR-only) files parse correctly.
Blank and whitespace-only lines — which some exporters append, most commonly after the
trailer — are skipped rather than mis-parsed, while line numbers in error messages stay
aligned with the physical file.

The source encoding is detected from the byte-order mark or the `HEAD.CHAR` declaration and
transcoded to UTF-8: **ANSEL** (the 5.5.1 default, decoded via the bundled Z39.47 table),
**UTF-8**, **UNICODE** (UTF-16, little- or big-endian) and **ASCII**. Reading requires the
`ext-mbstring` and `ext-intl` extensions.

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

### Schema-driven typed model (in progress)
The parser is being refactored onto a schema-driven, fully typed model. A generic tree
reader (`MagicSunday\Gedcom\Parse`) turns the flat reader lines into an immutable node
tree; a declarative schema (`MagicSunday\Gedcom\Schema`) is compiled from the vendored
registry for either GEDCOM version; and a mapping layer (`MagicSunday\Gedcom\Mapping`)
shapes a node subtree through that schema and hydrates immutable `final readonly` records
(`MagicSunday\Gedcom\TypedModel`) via [`magicsunday/jsonmapper`](https://github.com/magicsunday/jsonmapper),
with the value-object leaves (dates, places, ages) parsed by their own grammar. Each leaf is
resolved regardless of the GEDCOM version — a bare payload string in 5.5.1, or the shaped node
a 7.0 substructure-bearing leaf (a `DATE` with `PHRASE`/`TIME`, a `PLAC` with `FORM`) produces.
Until the untyped result model is fully replaced, the `phpstan` and `cpd` CI steps stay
non-blocking.

`TypedGedcomParser` ties the pipeline together: give it the GEDCOM version and a map of
record tag to your typed record class, and it streams the level-0 records and maps each
recognised one (unmapped records such as `HEAD`/`TRLR` are skipped):

```php
use MagicSunday\Gedcom\Mapping\TypedGedcomParser;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\TypedModel\FamilyRecord;
use MagicSunday\Gedcom\TypedModel\IndividualRecord;

$parser = TypedGedcomParser::create(GedcomVersion::V551, [
    'INDI' => IndividualRecord::class,
    'FAM'  => FamilyRecord::class,
]);

foreach ($parser->parse($stream) as $record) {
    // one typed IndividualRecord or FamilyRecord at a time, in document order (parse() yields, so
    // a large file is never held in memory); wrap in iterator_to_array() if you need the full list
}
```

The typed record set is still growing; only the modelled records are mapped today. Currently an
`IndividualRecord` exposes its names, sex, its birth, death and burial events, and its child- and
spouse-to-family links (`FAMC`/`FAMS`), and a `FamilyRecord` exposes its partner and child
cross-references and its marriage events — each event a typed `EventDetail` (date, place, age).
Substructures not yet modelled are ignored rather than mapped.

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
