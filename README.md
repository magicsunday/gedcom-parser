[![Latest version](https://img.shields.io/github/v/release/magicsunday/gedcom-parser?sort=semver)](https://github.com/magicsunday/gedcom-parser/releases/latest)
[![License](https://img.shields.io/github/license/magicsunday/gedcom-parser)](https://github.com/magicsunday/gedcom-parser/blob/main/LICENSE)
[![CI](https://github.com/magicsunday/gedcom-parser/actions/workflows/ci.yml/badge.svg)](https://github.com/magicsunday/gedcom-parser/actions/workflows/ci.yml)


# GEDCOM parser
This module provides a [GEDCOM](https://de.wikipedia.org/wiki/GEDCOM) 5.5.1 compatible file parser.


## Installation
### Using Composer
To install using [composer](https://getcomposer.org/), just run the following command from the command line 
at the root directory of your installation.

``` 
composer require magicsunday/gedcom-parser
```

To remove the parser, run:
```
composer remove magicsunday/gedcom-parser 
```

## Usage
To allow reading of GEDCOM files encoded with a Macintosh line ending (\r) set the following PHP runtime
configuration.  

```php
<?php

// Allow handling of Macintosh line endings (\r)
ini_set('auto_detect_line_endings', '1');

?>
````


## Development

### Run tests
```shell
composer update

composer ci:test
composer ci:test:php:phpstan
composer ci:test:php:lint
composer ci:test:php:unit
composer ci:test:php:rector
```
