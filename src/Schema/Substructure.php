<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Schema;

/**
 * An allowed substructure of a {@see StructureDefinition}: the URI of the child structure it
 * refers to together with how often it may occur under its parent.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class Substructure
{
    /**
     * @param string      $uri         The URI of the referenced child structure definition
     * @param Cardinality $cardinality How often the child may occur under its parent
     */
    public function __construct(
        public string $uri,
        public Cardinality $cardinality,
    ) {
    }
}
