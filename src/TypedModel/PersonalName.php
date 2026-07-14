<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\TypedModel;

/**
 * A typed GEDCOM personal name (the `NAME` structure of an individual).
 *
 * The `NAME` structure carries both a value — the slash-delimited name string such as
 * `John /Doe/` — and optional name-part substructures, so it exposes the raw value alongside the
 * explicit given-name (`GIVN`) and surname (`SURN`) parts when present.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class PersonalName
{
    /**
     * @param string|null $value The slash-delimited name value, or NULL when absent
     * @param string|null $givn  The given name from the GIVN substructure, or NULL
     * @param string|null $surn  The surname from the SURN substructure, or NULL
     */
    public function __construct(
        public ?string $value = null,
        public ?string $givn = null,
        public ?string $surn = null,
    ) {
    }
}
