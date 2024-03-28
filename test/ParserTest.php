<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\TestCase;

/**
 * Unit test.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-reader/
 */
class ParserTest extends TestCase
{
//    /**
//     * @test
//     */
//    public function parseFile(): void
//    {
//        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/simple.ged');
//        $parser = new Parser($stream);
//        $gedcom = $parser->parse();
//
//ini_set('xdebug.var_display_max_depth', '-1');
//ini_set('xdebug.var_display_max_children', '-1');
//ini_set('xdebug.var_display_max_data', '-1');
//
//var_dump($gedcom);
//    }

    /**
     * @test
     */
    public function parsePartial(): void
    {
        $stream = (new StreamFactory())->createStream(<<<GEDCOM
0 @X116@ INDI
1 SEX M
1 BIRT
2 DATE 01 JAN 1950
1 DEAT
2 DATE 01 JAN 2000
1 FAMS @X118@
1 NAME Lt. Cmndr. Max Joachim /der Edle/ von Musterhausen
2 TYPE BIRTH
2 GIVN Max Joachim
2 SPFX der
2 SURN Edle
2 NPFX Lt. Cmndr.
2 NSFX von Musterhausen
GEDCOM
        );

        $stream->rewind();

        $parser = new Parser($stream);
        $gedcom = $parser->parse();

ini_set('xdebug.var_display_max_depth', '-1');
ini_set('xdebug.var_display_max_children', '-1');
ini_set('xdebug.var_display_max_data', '-1');

var_dump($gedcom);

var_dump($gedcom->getIndividual()[0]->getNames()[0]->getName());
    }
}
