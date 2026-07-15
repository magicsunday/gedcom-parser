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
 * A typed GEDCOM source (SOUR) record.
 *
 * Describes a source of genealogical information — its title, author, publication facts and any
 * verbatim source text. Each descriptive field is a single optional text value ({0:1}).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class SourceRecord
{
    /**
     * @param string      $xref The record cross-reference identifier.
     * @param string|null $titl The source title (TITL), or NULL when absent.
     * @param string|null $auth The source author (AUTH), or NULL when absent.
     * @param string|null $publ The publication facts (PUBL), or NULL when absent.
     * @param string|null $abbr The short abbreviated title (ABBR), or NULL when absent.
     * @param string|null $text The verbatim source text (TEXT), or NULL when absent.
     */
    public function __construct(
        public string $xref,
        public ?string $titl = null,
        public ?string $auth = null,
        public ?string $publ = null,
        public ?string $abbr = null,
        public ?string $text = null,
    ) {
    }
}
