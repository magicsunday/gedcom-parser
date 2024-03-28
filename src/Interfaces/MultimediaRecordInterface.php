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
use MagicSunday\Gedcom\Interfaces\Common\ReferenceNumberInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;
use MagicSunday\Gedcom\Interfaces\MultimediaRecord\FileInterface;

/**
 * The OBJE (multimedia object) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface MultimediaRecordInterface extends ChangeDateInterface, NoteInterface, SourceCitationInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, an OBJEct record.
     */
    public const TAG_XREF_OBJE = 'XREF:OBJE';

    /**
     * A complete local or remote file reference to the auxiliary data to be linked to the GEDCOM context.
     * Remote reference would include a network address where the multimedia data may be obtained.
     */
    public const TAG_FILE = 'FILE';

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
     * @return FileInterface[]
     */
    public function getFile(): array;

    /**
     * @return ReferenceNumberInterface[]
     */
    public function getReferenceNumber(): array;

    /**
     * @return string|null
     */
    public function getRecordIdNumber(): ?string;
}
