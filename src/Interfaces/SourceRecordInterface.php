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
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLinkInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\ReferenceNumberInterface;
use MagicSunday\Gedcom\Interfaces\SourceRecord\DataInterface;
use MagicSunday\Gedcom\Interfaces\SourceRecord\SourceRepositoryCitationInterface;

/**
 * The SOUR (source) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SourceRecordInterface extends ChangeDateInterface, MultimediaLinkInterface, NoteInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a SOURce record.
     */
    public const TAG_XREF_SOUR = 'XREF:SOUR';

    /**
     * Pertaining to stored automated information.
     */
    public const TAG_DATA = 'DATA';

    /**
     * The person, agency, or entity who created the record. For a published work, this could be the author,
     * compiler, transcriber, abstractor, or editor. For an unpublished source, this may be an individual, a
     * government agency, church organization, or private organization, etc.
     */
    public const TAG_AUTH = 'AUTH';

    /**
     * The title of the work, record, or item and, when appropriate, the title of the larger work or series of
     * which it is a part.
     */
    public const TAG_TITL = 'TITL';

    /**
     * A short name of a title, description, or name.
     */
    public const TAG_ABBR = 'ABBR';

    /**
     * When and where the record was created. For published works, this includes information such as the
     * city of publication, name of the publisher, and year of publication.
     */
    public const TAG_PUBL = 'PUBL';

    /**
     * A verbatim copy of any description contained within the source. This indicates notes or text that are
     * actually contained in the source document, not the submitter's opinion about the source.
     */
    public const TAG_TEXT = 'TEXT';

    /**
     * An institution or person that has the specified item as part of their collection(s).
     */
    public const TAG_REPO = 'REPO';

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
     * @return DataInterface|null
     */
    public function getData(): ?DataInterface;

    /**
     * @return string|null
     */
    public function getAuthor(): ?string;

    /**
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * @return string|null
     */
    public function getAbbreviation(): ?string;

    /**
     * @return string|null
     */
    public function getPublication(): ?string;

    /**
     * @return string|null
     */
    public function getText(): ?string;

    /**
     * @return SourceRepositoryCitationInterface[]
     */
    public function getRepository(): array;

    /**
     * @return ReferenceNumberInterface[]
     */
    public function getReferenceNumber(): array;

    /**
     * @return string|null
     */
    public function getRecordIdNumber(): ?string;
}
