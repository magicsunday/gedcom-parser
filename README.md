[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/magicsunday/gedcom-parser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/magicsunday/gedcom-parser)
[![Code Coverage](https://scrutinizer-ci.com/g/magicsunday/gedcom-parser/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/magicsunday/gedcom-parser)
[![Build Status](https://travis-ci.org/magicsunday/gedcom-parser.svg?branch=master)](https://travis-ci.org/magicsunday/gedcom-parser)
[![Code Climate](https://codeclimate.com/github/magicsunday/gedcom-parser/badges/gpa.svg)](https://codeclimate.com/github/magicsunday/gedcom-parser)
[![Issue Count](https://codeclimate.com/github/magicsunday/gedcom-parser/badges/issue_count.svg)](https://codeclimate.com/github/magicsunday/gedcom-parser)

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
