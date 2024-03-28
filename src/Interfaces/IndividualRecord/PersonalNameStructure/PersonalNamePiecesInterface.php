<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
     * In this example, "Lt. Cmndr." is considered as the name prefix portion.
     */
    public const TAG_NPFX = 'NPFX';

    /**
     * A given or earned name used for official identification of a person.
     */
    public const TAG_GIVN = 'GIVN';

    /**
     * A descriptive or familiar that is used instead of, or in addition to, one's proper name.
     */
    public const TAG_NICK = 'NICK';

    /**
     * A name piece used as a non-indexing pre-part of a surname.
     */
    public const TAG_SPFX = 'SPFX';

    /**
     * A family name passed on or used by members of a family.
     */
    public const TAG_SURN = 'SURN';

    /**
     * Text which appears on a name line after or behind the given and surname parts of a name.
     *   i.e. Lt. Cmndr. Joseph /Allen/ (jr.).
     *
     * In this example, "jr." is considered as the name suffix portion.
     */
    public const TAG_NSFX = 'NSFX';

    /**
     * @return string|null
     */
    public function getNamePrefix(): ?string;

    /**
     * @return string|null
     */
    public function getGivenName(): ?string;

    /**
     * @return string|null
     */
    public function getNickName(): ?string;

    /**
     * @return string|null
     */
    public function getSurnamePrefix(): ?string;

    /**
     * @return string|null
     */
    public function getSurname(): ?string;

    /**
     * @return string|null
     */
    public function getNameSuffix(): ?string;
}
