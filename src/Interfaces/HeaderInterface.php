<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces;

use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;
use MagicSunday\Gedcom\Interfaces\Header\CharacterSetInterface;
use MagicSunday\Gedcom\Interfaces\Header\GedcomInfoInterface;
use MagicSunday\Gedcom\Interfaces\Header\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Header\PlaceInterface;
use MagicSunday\Gedcom\Interfaces\Header\SourceInterface;

/**
 * The header structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface HeaderInterface
{
    /**
     * The source.
     */
    const TAG_SOUR = 'SOUR';

    /**
     * The destination system name identifies the intended receiving system.
     */
    const TAG_DEST = 'DEST';

    /**
     * The date that this transmission was created.
     */
    const TAG_DATE = 'DATE';

    /**
     * The submitter identifier. The submitter record identifies an individual or organization that contributed
     * information contained in the GEDCOM transmission.
     */
    const TAG_SUBM = 'SUBM';

    /**
     * The submission identifier. The sending system uses a submission record to send instructions and
     * information to the receiving system.
     */
    const TAG_SUBN = 'SUBN';

    /**
     * The name of the GEDCOM transmission file. If the file name includes a file extension it must be
     * shown in the form (filename.ext).
     */
    const TAG_FILE = 'FILE';

    /**
     * A copyright statement needed to protect the copyrights of the submitter of this GEDCOM file.
     */
    const TAG_COPR = 'COPR';

    /**
     * Information about the use of GEDCOM in a transmission.
     */
    const TAG_GEDC = 'GEDC';

    /**
     * A code value that represents the character set to be used to interpret this data.
     */
    const TAG_CHAR = 'CHAR';

    /**
     * The human language in which the data in the transmission is normally read or written.
     */
    const TAG_LANG = 'LANG';

    /**
     * A place. This shows the jurisdictional entities that are named in a sequence from the lowest to the
     * highest jurisdiction.
     */
    const TAG_PLAC = 'PLAC';

    /**
     * A note that a user enters to describe the contents of the lineage-linked file in terms of
     * "ancestors or descendants of" so that the person receiving the data knows what genealogical
     * information the transmission contains.
     */
    const TAG_NOTE = 'NOTE';

    /**
     * @return null|SourceInterface
     */
    public function getSource();

    /**
     * @return null|string
     */
    public function getDestination();

    /**
     * @return null|DateExactInterface
     */
    public function getTransmissionDate();

    /**
     * @return null|string
     */
    public function getSubmitter();

    /**
     * @return null|string
     */
    public function getSubmission();

    /**
     * @return null|string
     */
    public function getFile();

    /**
     * @return null|GedcomInfoInterface
     */
    public function getGedcomInfo();

    /**
     * @return null|CharacterSetInterface
     */
    public function getCharacterSet();

    /**
     * @return null|string
     */
    public function getLanguage();

    /**
     * @return null|PlaceInterface
     */
    public function getPlace();

    /**
     * @return null|NoteInterface
     */
    public function getNote();
}
