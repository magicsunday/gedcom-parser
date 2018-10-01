<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces;

use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;
use MagicSunday\Gedcom\Interfaces\NoteRecord\ReferenceNumberInterface;

/**
 * The NOTE record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface NoteRecordInterface extends ChangeDateInterface, SourceCitationInterface
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
     * A number assigned to a record by an originating automated system that can be used by a receiving
     * system to report results pertaining to that record.
     */
    const TAG_RIN = 'RIN';

    /**
     * @return null|string
     */
    public function getXref();

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
