<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces;

use MagicSunday\Gedcom\Interfaces\Common\AddressStructureInterface;
use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\ReferenceNumberInterface;

/**
 * The REPO record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface RepositoryRecordInterface extends AddressStructureInterface, ChangeDateInterface, NoteInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a repository record.
     */
    const TAG_XREF_REPO = 'XREF:REPO';

    /**
     * The official name of the archive in which the stated source material is stored.
     */
    const TAG_NAME = 'NAME';

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
    public function getName();

    /**
     * @return null|ReferenceNumberInterface
     */
    public function getReferenceNumber();

    /**
     * @return null|string
     */
    public function getRecordIdNumber();
}
