<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces;

/**
 * The gedcom record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface GedcomInterface
{
    /**
     * Identifies information pertaining to an entire GEDCOM transmission.
     */
    const TAG_HEAD = 'HEAD';

    /**
     * Identifies a legal, common law, or other customary relationship of man and woman and their children, if
     * any, or a family created by virtue of the birth of a child to its biological father and mother.
     */
    const TAG_FAM = 'FAM';

    /**
     * A person.
     */
    const TAG_INDI = 'INDI';

    /**
     * Pertaining to a grouping of attributes used in describing something. Usually referring to the data
     * required to represent a multimedia object, such an audio recording, a photograph of a person, or an
     * image of a document.
     */
    const TAG_OBJE = 'OBJE';

    /**
     * Additional information provided by the submitter for understanding the enclosing data.
     */
    const TAG_NOTE = 'NOTE';

    /**
     * An institution or person that has the specified item as part of their collection(s).
     */
    const TAG_REPO = 'REPO';

    /**
     * The initial or original material from which information was obtained.
     */
    const TAG_SOUR = 'SOUR';

    /**
     * An individual or organization who contributes genealogical data to a file or transfers it to someone else.
     */
    const TAG_SUBM = 'SUBM';

    /**
     * Pertains to a collection of data issued for processing.
     */
    const TAG_SUBN = 'SUBN';

    /**
     * At level 0, specifies the end of a GEDCOM transmission.
     */
    const TAG_TRLR = 'TRLR';

    /**
     * @return HeaderRecordInterface
     */
    public function getHeader(): HeaderRecordInterface;

    /**
     * @return null|FamilyRecordInterface
     */
    public function getFamily();

    /**
     * @return null|IndividualRecordInterface
     */
    public function getIndividual();

    /**
     * @return null|MultimediaRecordInterface
     */
    public function getMultimedia();

    /**
     * @return null|NoteRecordInterface
     */
    public function getNote();

    /**
     * @return null|RepositoryRecordInterface
     */
    public function getRepository();

    /**
     * @return null|SourceRecordInterface
     */
    public function getSource();

    /**
     * @return null|SubmitterRecordInterface
     */
    public function getSubmitter();

    /**
     * @return null|SubmissionRecordInterface
     */
    public function getSubmission();
}
