<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\HeaderRecord;

/**
 * The place structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface PlaceInterface
{
    /**
     * This shows the jurisdictional entities that are named in a sequence from the lowest to the highest
     * jurisdiction. The jurisdictions are separated by commas, and any jurisdiction's name that is missing is
     * still accounted for by a comma. When a PLAC.FORM structure is included in the HEADER of a
     * GEDCOM transmission, it implies that all place names follow this jurisdictional format and each
     * jurisdiction is accounted for by a comma, whether the name is known or not. When the PLAC.FORM
     * is subordinate to an event, it temporarily overrides the implications made by the PLAC.FORM
     * structure stated in the HEADER. This usage is not common and, therefore, not encouraged. It should
     * only be used when a system has over-structured its place-names.
     */
    public const TAG_FORM = 'FORM';

    /**
     * @return string
     */
    public function getForm(): string;
}
