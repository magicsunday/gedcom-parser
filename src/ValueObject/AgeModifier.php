<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\ValueObject;

/**
 * The relational qualifier that may precede a GEDCOM AGE_AT_EVENT value.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
enum AgeModifier: string
{
    /**
     * Less than the indicated age (GEDCOM `<`).
     */
    case LessThan = '<';

    /**
     * Greater than the indicated age (GEDCOM `>`).
     */
    case GreaterThan = '>';
}
