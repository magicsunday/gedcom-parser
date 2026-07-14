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
 * A typed GEDCOM multimedia object (OBJE) record.
 *
 * Groups one or more external multimedia files, each a typed {@see MultimediaFile}, so they may
 * be referenced by cross-reference from other records. GEDCOM 5.5.1 requires at least one file
 * ({1:M}).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class MultimediaRecord
{
    /**
     * @param string               $xref The record cross-reference identifier
     * @param list<MultimediaFile> $file The referenced multimedia files
     */
    public function __construct(
        public string $xref,
        public array $file = [],
    ) {
    }
}
