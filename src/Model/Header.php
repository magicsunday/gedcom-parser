<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Model\Common\DateExact;
use MagicSunday\Gedcom\Model\Header\CharacterSet;
use MagicSunday\Gedcom\Model\Header\GedcomInfo;
use MagicSunday\Gedcom\Model\Header\Note;
use MagicSunday\Gedcom\Model\Header\Place;
use MagicSunday\Gedcom\Model\Header\Source;

/**
 * The header structure provides information about the entire transmission.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Header extends DataObject
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
     * @return null|Source
     */
    public function getSource(): Source
    {
        return $this->getValue(self::TAG_SOUR);
    }

    /**
     * @return null|string
     */
    public function getDestination()
    {
        return $this->getValue(self::TAG_DEST);
    }

    /**
     * @return null|DateExact
     */
    public function getDate(): DateExact
    {
        return $this->getValue(self::TAG_DATE);
    }

    /**
     * @return null|string
     */
    public function getSubmitter()
    {
        return $this->getValue(self::TAG_SUBM);
    }

    /**
     * @return null|string
     */
    public function getSubmission()
    {
        return $this->getValue(self::TAG_SUBN);
    }

    /**
     * @return null|string
     */
    public function getFile()
    {
        return $this->getValue(self::TAG_FILE);
    }

    /**
     * @return null|GedcomInfo
     */
    public function getGedcomInfo(): GedcomInfo
    {
        return $this->getValue(self::TAG_GEDC);
    }

    /**
     * @return null|CharacterSet
     */
    public function getCharacterSet(): CharacterSet
    {
        return $this->getValue(self::TAG_CHAR);
    }

    /**
     * @return null|string
     */
    public function getLanguage()
    {
        return $this->getValue(self::TAG_LANG);
    }

    /**
     * @return null|Place
     */
    public function getPlace(): Place
    {
        return $this->getValue(self::TAG_PLAC);
    }

    /**
     * @return null|Note
     */
    public function getNote(): Note
    {
        return $this->getValue(self::TAG_NOTE);
    }
}
