<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces;

use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLinkInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\ReferenceNumberInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;
use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructureInterface;
use MagicSunday\Gedcom\Interfaces\FamilyRecord\LdsSpouseSealingInterface;

/**
 * The FAM (family) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface FamilyRecordInterface extends ChangeDateInterface, FamilyEventStructureInterface, MultimediaLinkInterface, NoteInterface, SourceCitationInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a family record.
     */
    public const TAG_XREF_FAM = 'XREF:FAM';

    /**
     * A processing indicator signifying access to information has been denied or otherwise restricted.
     */
    public const TAG_RESN = 'RESN';

    /**
     * A pointer to, or a cross-reference identifier of, an individual record.
     */
    public const TAG_HUSB = 'HUSB';

    /**
     * A pointer to, or a cross-reference identifier of, an individual record.
     */
    public const TAG_WIFE = 'WIFE';

    /**
     * A pointer to, or a cross-reference identifier of, an individual record.
     */
    public const TAG_CHIL = 'CHIL';

    /**
     * The number of children that this person is known to be the parent of (all marriages) when subordinate
     * to an individual, or that belong to this family when subordinate to a FAM_RECORD.
     */
    public const TAG_NCHI = 'NCHI';

    /**
     * A pointer to, or a cross-reference identifier of, a SUBMitter record.
     */
    public const TAG_SUBM = 'SUBM';

    /**
     * A religious event pertaining to the sealing of a husband and wife in an LDS temple ceremony.
     */
    public const TAG_SLGS = 'SLGS';

    /**
     * A description or number used to identify an item for filing, storage, or other reference purposes.
     */
    public const TAG_REFN = 'REFN';

    /**
     * A unique record identification number assigned to the record by the source system. This number is
     * intended to serve as a more sure means of identification of a record for reconciling differences in data
     * between two interfacing systems.
     */
    public const TAG_RIN = 'RIN';

    /**
     * @return string
     */
    public function getXref(): string;

    /**
     * @return string|null
     */
    public function getRestrictionNotice(): ?string;

    /**
     * @return string|null
     */
    public function getHusbandXref(): ?string;

    /**
     * @return string|null
     */
    public function getWifeXref(): ?string;

    /**
     * @return string[]
     */
    public function getChildrenXref(): array;

    /**
     * @return string|null
     */
    public function getChildrenCount(): ?string;

    /**
     * @return string[]
     */
    public function getSubmitterXref(): array;

    /**
     * @return LdsSpouseSealingInterface[]
     */
    public function getSpouseSealing(): array;

    /**
     * @return ReferenceNumberInterface[]
     */
    public function getReferenceNumber(): array;

    /**
     * @return string|null
     */
    public function getRecordIdNumber(): ?string;
}
