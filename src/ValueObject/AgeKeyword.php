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
 * The symbolic age keywords defined by the GEDCOM AGE_AT_EVENT grammar.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
enum AgeKeyword: string
{
    /**
     * Less than eight years old.
     */
    case Child = 'CHILD';

    /**
     * Less than one year old.
     */
    case Infant = 'INFANT';

    /**
     * Born dead.
     */
    case Stillborn = 'STILLBORN';
}
