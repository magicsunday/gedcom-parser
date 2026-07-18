<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Substructure\Common;

use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A typed postal address (`ADDR`) — where an event took place, where an attribute applies, or how to
 * reach a submitter or repository.
 *
 * The address is written twice over: {@see $value} carries it as free-form text the way the file
 * chose to format it, while the remaining fields refine it into its parts. A file may supply either
 * form alone, so neither can be derived from the other and both are kept. The structure is identical
 * in GEDCOM 5.5.1 and GEDCOM 7.0.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class Address
{
    /**
     * @param string|null           $value   The address as free-form text, or NULL when only its parts are given.
     * @param string|null           $adr1    The first address line (ADR1), or NULL when absent.
     * @param string|null           $adr2    The second address line (ADR2), or NULL when absent.
     * @param string|null           $adr3    The third address line (ADR3), or NULL when absent.
     * @param string|null           $city    The city (CITY), or NULL when absent.
     * @param string|null           $stae    The state or province (STAE), or NULL when absent.
     * @param string|null           $post    The postal code (POST), or NULL when absent.
     * @param string|null           $ctry    The country (CTRY), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $adr1 = null,
        public ?string $adr2 = null,
        public ?string $adr3 = null,
        public ?string $city = null,
        public ?string $stae = null,
        public ?string $post = null,
        public ?string $ctry = null,
        public array $unknown = [],
    ) {
    }
}
