[![Latest version](https://img.shields.io/github/v/release/magicsunday/gedcom-parser?sort=semver)](https://github.com/magicsunday/gedcom-parser/releases/latest)
[![License](https://img.shields.io/github/license/magicsunday/gedcom-parser)](https://github.com/magicsunday/gedcom-parser/blob/main/LICENSE)
[![Security](https://github.com/magicsunday/gedcom-parser/actions/workflows/security.yml/badge.svg)](https://github.com/magicsunday/gedcom-parser/actions/workflows/security.yml)


# GEDCOM parser
A [GEDCOM](https://en.wikipedia.org/wiki/GEDCOM) 5.5.1 file parser for PHP. It reads a
GEDCOM stream line by line and exposes the records (individuals, families, sources,
notes, …) as an object model.


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

The parser reads any readable stream — including non-seekable ones such as a pipe
(`cat tree.ged | your-app`) or a network response body — and accepts all four GEDCOM 5.5.1
line terminators (CR, LF, CRLF and LFCR), so classic-Mac (CR-only) files parse correctly.

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

### Run tests
All PHP tooling runs through the build container. Run the full check with
`composer ci:test`, or invoke the individual steps:

```shell
composer update

# everything at once
composer ci:test

# …or step by step
composer ci:test:php:lint
composer ci:test:php:phpstan
composer ci:test:php:rector
composer ci:test:php:unit
```
