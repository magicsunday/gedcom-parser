<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Tools\ModelGenerator;

/**
 * The specification of a single generated constructor property: its PHP type, its PHPDoc type
 * (which may be a generic such as `list<Note>` the native type cannot express), its default value
 * and its description.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class PropertySpec
{
    /**
     * @param string $name        The property name (the lowercased GEDCOM tag).
     * @param string $phpType      The native PHP type declaration (e.g. `?string`, `array`).
     * @param string $docType      The PHPDoc type (e.g. `string|null`, `list<Note>`).
     * @param string $default      The default value literal (e.g. `null`, `[]`).
     * @param string $description  The capitalised property description, ending with a period.
     */
    public function __construct(
        public string $name,
        public string $phpType,
        public string $docType,
        public string $default,
        public string $description,
    ) {
    }
}
