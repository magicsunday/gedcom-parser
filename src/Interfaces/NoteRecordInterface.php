<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces;

use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\Common\ReferenceNumberInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;

/**
 * The NOTE record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface NoteRecordInterface extends
    ChangeDateInterface,
    SourceCitationInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a note record.
     */
    const TAG_XREF_NOTE = 'XREF:NOTE';

    /**
     * Comments or opinions from the submitter.
     */
    const TAG_SUBMITTER_TEXT = 'SUBMITTER_TEXT';

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
     * @return string
     */
    public function getXref(): string;

    /**
     * @return null|string
     */
    public function getText();

    /**
     * @return null|ReferenceNumberInterface
     */
    public function getReferenceNumber();

    /**
     * @return null|string
     */
    public function getRecordIdNumber();
}
