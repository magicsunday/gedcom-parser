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
    public const TAG_XREF_SUBN = 'XREF:SUBN';

    /**
     * A pointer to, or a cross-reference identifier of, a SUBMitter record.
     */
    public const TAG_SUBM = 'SUBM';

    /**
     * Name under which family names for ordinances are stored in the temple's family file.
     */
    public const TAG_FAMF = 'FAMF';

    /**
     * An abbreviation of the temple in which LDS temple ordinances were performed.
     */
    public const TAG_TEMP = 'TEMP';

    /**
     * The number of generations of ancestors included in this transmission. This value is usually provided
     * when FamilySearch programs build a GEDCOM file for a patron requesting a download of ancestors.
     */
    public const TAG_ANCE = 'ANCE';

    /**
     * The number of generations of descendants included in this transmission. This value is usually provided
     * when FamilySearch programs build a GEDCOM file for a patron requesting a download of descendants.
     */
    public const TAG_DESC = 'DESC';

    /**
     * A flag that indicates whether submission should be processed for clearing temple ordinances.
     */
    public const TAG_ORDI = 'ORDI';

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
    public function getSubmitterXref(): ?string;

    /**
     * @return string|null
     */
    public function getFamilyFile(): ?string;

    /**
     * @return string|null
     */
    public function getTempleCode(): ?string;

    /**
     * @return string|null
     */
    public function getAncestorGenerations(): ?string;

    /**
     * @return string|null
     */
    public function getDescendantGenerations(): ?string;

    /**
     * @return string|null
     */
    public function getOrdinanceFlag(): ?string;

    /**
     * @return string|null
     */
    public function getRecordIdNumber(): ?string;
}
