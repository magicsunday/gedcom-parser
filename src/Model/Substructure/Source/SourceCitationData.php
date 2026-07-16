<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Substructure\Source;

use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * The data (transcribed text and its date) of a source citation.
 *
 * This class is generated from the GEDCOM registry. Do not edit it by hand.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class SourceCitationData
{
    /**
     * @param DateValue|null        $date    The DATE value.
     * @param list<string>          $text    The TEXT values.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-place tags), preserved verbatim.
     */
    public function __construct(
        public ?DateValue $date = null,
        public array $text = [],
        public array $unknown = [],
    ) {
    }
}
