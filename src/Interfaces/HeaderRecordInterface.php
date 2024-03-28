<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces;

use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\CharacterSetInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\GedcomInfoInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\NoteInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\PlaceInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\SourceInterface;

/**
 * The HEAD (header) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface HeaderRecordInterface
{
    /**
     * The source.
     */
    public const TAG_SOUR = 'SOUR';

    /**
     * The destination system name identifies the intended receiving system.
     */
    public const TAG_DEST = 'DEST';

    /**
     * The date that this transmission was created.
     */
    public const TAG_DATE = 'DATE';

    /**
     * The submitter identifier. The submitter record identifies an individual or organization that contributed
     * information contained in the GEDCOM transmission.
     */
    public const TAG_SUBM = 'SUBM';

    /**
     * The submission identifier. The sending system uses a submission record to send instructions and
     * information to the receiving system.
     */
    public const TAG_SUBN = 'SUBN';

    /**
     * The name of the GEDCOM transmission file. If the file name includes a file extension, it must be
     * shown in the form (filename.ext).
     */
    public const TAG_FILE = 'FILE';

    /**
     * A copyright statement needed to protect the copyrights of the submitter of this GEDCOM file.
     */
    public const TAG_COPR = 'COPR';

    /**
     * Information about the use of GEDCOM in a transmission.
     */
    public const TAG_GEDC = 'GEDC';

    /**
     * A code value that represents the character set to be used to interpret this data.
     */
    public const TAG_CHAR = 'CHAR';

    /**
     * The human language in which the data in the transmission is normally read or written.
     */
    public const TAG_LANG = 'LANG';

    /**
     * A place. This shows the jurisdictional entities that are named in a sequence from the lowest to the
     * highest jurisdiction.
     */
    public const TAG_PLAC = 'PLAC';

    /**
     * A note that a user enters to describe the contents of the lineage-linked file in terms of
     * "ancestors or descendants of" so that the person receiving the data knows what genealogical
     * information the transmission contains.
     */
    public const TAG_NOTE = 'NOTE';

    /**
     * @return SourceInterface
     */
    public function getSource(): SourceInterface;

    /**
     * @return string|null
     */
    public function getDestination(): ?string;

    /**
     * @return DateExactInterface|null
     */
    public function getTransmissionDate(): ?DateExactInterface;

    /**
     * @return string
     */
    public function getSubmitter(): string;

    /**
     * @return string|null
     */
    public function getSubmission(): ?string;

    /**
     * @return string|null
     */
    public function getFile(): ?string;

    /**
     * @return string|null
     */
    public function getCopyright(): ?string;

    /**
     * @return GedcomInfoInterface
     */
    public function getGedcomInfo(): GedcomInfoInterface;

    /**
     * @return CharacterSetInterface
     */
    public function getCharacterSet(): CharacterSetInterface;

    /**
     * @return string|null
     */
    public function getLanguage(): ?string;

    /**
     * @return PlaceInterface|null
     */
    public function getPlace(): ?PlaceInterface;

    /**
     * @return NoteInterface|null
     */
    public function getNote(): ?NoteInterface;
}
