<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure;

use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;

/**
 * The personal name pieces tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface PersonalNamePiecesInterface extends NoteInterface, SourceCitationInterface
{
    /**
     * Text which appears on a name line before the given and surname parts of a name.
     *   i.e. (Lt. Cmndr.) Joseph /Allen/ jr.
     *
     * In this example Lt. Cmndr. is considered as the name prefix portion.
     */
    const TAG_NPFX = 'NPFX';

    /**
     * A given or earned name used for official identification of a person.
     */
    const TAG_GIVN = 'GIVN';

    /**
     * A descriptive or familiar that is used instead of, or in addition to, one's proper name.
     */
    const TAG_NICK = 'NICK';

    /**
     * A name piece used as a non-indexing pre-part of a surname.
     */
    const TAG_SPFX = 'SPFX';

    /**
     * A family name passed on or used by members of a family.
     */
    const TAG_SURN = 'SURN';

    /**
     * Text which appears on a name line after or behind the given and surname parts of a name.
     *   i.e. Lt. Cmndr. Joseph /Allen/ (jr.)
     *
     * In this example jr. is considered as the name suffix portion.
     */
    const TAG_NSFX = 'NSFX';

    /**
     * @return null|string
     */
    public function getNamePrefix();

    /**
     * @return null|string
     */
    public function getGivenName();

    /**
     * @return null|string
     */
    public function getNickName();

    /**
     * @return null|string
     */
    public function getSurnamePrefix();

    /**
     * @return null|string
     */
    public function getSurname();

    /**
     * @return null|string
     */
    public function getNameSuffix();
}
