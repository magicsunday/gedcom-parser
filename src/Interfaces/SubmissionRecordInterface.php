<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces;

use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;

/**
 * The SUBN (submission) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SubmissionRecordInterface extends ChangeDateInterface, NoteInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a SUBmissioN record.
     */
    const TAG_XREF_SUBN = 'XREF:SUBN';

    /**
     * A pointer to, or a cross-reference identifier of, a SUBMitter record.
     */
    const TAG_SUBM = 'SUBM';

    /**
     * Name under which family names for ordinances are stored in the temple's family file.
     */
    const TAG_FAMF = 'FAMF';

    /**
     * An abbreviation of the temple in which LDS temple ordinances were performed.
     */
    const TAG_TEMP = 'TEMP';

    /**
     * The number of generations of ancestors included in this transmission. This value is usually provided
     * when FamilySearch programs build a GEDCOM file for a patron requesting a download of ancestors.
     */
    const TAG_ANCE = 'ANCE';

    /**
     * The number of generations of descendants included in this transmission. This value is usually provided
     * when FamilySearch programs build a GEDCOM file for a patron requesting a download of descendants.
     */
    const TAG_DESC = 'DESC';

    /**
     * A flag that indicates whether submission should be processed for clearing temple ordinances.
     */
    const TAG_ORDI = 'ORDI';

    /**
     * A unique record identification number assigned to the record by the source system. This number is
     * intended to serve as a more sure means of identification of a record for reconciling differences in data
     * between two interfacing systems.
     */
    const TAG_RIN = 'RIN';

    /**
     * @return null|string
     */
    public function getSubmissionXref();

    /**
     * @return null|string
     */
    public function getSubmitterXref();

    /**
     * @return null|string
     */
    public function getFamilyFile();

    /**
     * @return null|string
     */
    public function getTempleCode();

    /**
     * @return null|string
     */
    public function getAncestorGenerations();

    /**
     * @return null|string
     */
    public function getDescendantGenerations();

    /**
     * @return null|string
     */
    public function getOrdinanceFlag();

    /**
     * @return null|string
     */
    public function getRecordIdNumber();
}
