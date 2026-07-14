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
 * A GEDCOM specification version whose declarative schema is compiled from the registry.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
enum GedcomVersion: string
{
    case V551 = '5.5.1';
    case V70  = '7.0';

    /**
     * Returns the URI path segment that identifies this version's terms in the registry, used to
     * select the version's slice of the shared structure files.
     *
     * @return string
     */
    public function uriInfix(): string
    {
        return match ($this) {
            self::V551 => '/v5.5.1/',
            self::V70  => '/v7/',
        };
    }
}
