<?php
/**
 * See LICENSE.md file for further details.
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
interface FamilyRecordInterface extends
    ChangeDateInterface,
    FamilyEventStructureInterface,
    MultimediaLinkInterface,
    NoteInterface,
    SourceCitationInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a family record.
     */
    const TAG_XREF_FAM = 'XREF:FAM';

    /**
     * A processing indicator signifying access to information has been denied or otherwise restricted.
     */
    const TAG_RESN = 'RESN';

    /**
     * A pointer to, or a cross-reference identifier of, an individual record.
     */
    const TAG_HUSB = 'HUSB';

    /**
     * A pointer to, or a cross-reference identifier of, an individual record.
     */
    const TAG_WIFE = 'WIFE';

    /**
     * A pointer to, or a cross-reference identifier of, an individual record.
     */
    const TAG_CHIL = 'CHIL';

    /**
     * The number of children that this person is known to be the parent of (all marriages) when subordinate
     * to an individual, or that belong to this family when subordinate to a FAM_RECORD.
     */
    const TAG_NCHI = 'NCHI';

    /**
     * A pointer to, or a cross-reference identifier of, a SUBMitter record.
     */
    const TAG_SUBM = 'SUBM';

    /**
     * A religious event pertaining to the sealing of a husband and wife in an LDS temple ceremony.
     */
    const TAG_SLGS = 'SLGS';

    /**
     * A description or number used to identify an item for filing, storage, or other reference purposes.
     */
    const TAG_REFN = 'REFN';

    /**
     * A unique record identification number assigned to the record by the source system. This number is
     * intended to serve as a more sure means of identification of a record for reconciling differences in data
     * between two interfacing systems.
     */
    const TAG_RIN = 'RIN';

    /**
     * @return null|string
     */
    public function getXref();

    /**
     * @return null|string
     */
    public function getRestrictionNotice();

    /**
     * @return null|string
     */
    public function getHusbandXref();

    /**
     * @return null|string
     */
    public function getWifeXref();

    /**
     * @return null|string
     */
    public function getChildrenXref();

    /**
     * @return null|string
     */
    public function getChildrenCount();

    /**
     * @return null|string
     */
    public function getSubmitterXref();

    /**
     * @return null|LdsSpouseSealingInterface
     */
    public function getSealingSpouse();

    /**
     * @return null|ReferenceNumberInterface
     */
    public function getReferenceNumber();

    /**
     * @return null|string
     */
    public function getRecordIdNumber();
}
