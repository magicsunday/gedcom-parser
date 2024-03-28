<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces;

use MagicSunday\Gedcom\Interfaces\Common\AddressStructureInterface;
use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLinkInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;

/**
 * The SUBM (submitter) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SubmitterRecordInterface extends AddressStructureInterface, ChangeDateInterface, MultimediaLinkInterface, NoteInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a SUBMitter record.
     */
    public const TAG_XREF_SUBM = 'XREF:SUBM';

    /**
     * The name of the submitter formatted for display and address generation.
     */
    public const TAG_NAME = 'NAME';

    /**
     * The language in which a person prefers to communicate. Multiple language preference is shown by
     * using multiple occurrences in order of priority.
     */
    public const TAG_LANG = 'LANG';

    /**
     * A registered number of a submitter of Ancestral File data. This number is used in subsequent
     * submissions or inquiries by the submitter for identification purposes.
     */
    public const TAG_RFN = 'RFN';

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
     * @return string
     */
    public function getName(): string;

    /**
     * @return string[]
     */
    public function getLanguage(): array;

    /**
     * @return string|null
     */
    public function getRegisterNumber(): ?string;

    /**
     * @return string|null
     */
    public function getRecordIdNumber(): ?string;
}
