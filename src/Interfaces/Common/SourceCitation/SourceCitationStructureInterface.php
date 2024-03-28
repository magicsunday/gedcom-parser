<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common\SourceCitation;

use MagicSunday\Gedcom\Interfaces\Common\MultimediaLinkInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;

/**
 * The SOUR (source citation) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SourceCitationStructureInterface extends MultimediaLinkInterface, NoteInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a SOURce record.
     */
    public const TAG_XREF_SOUR = 'XREF:SOUR';

    /**
     * A number or description to identify where information can be found in a referenced work.
     */
    public const TAG_PAGE = 'PAGE';

    /**
     * Pertaining to a noteworthy happening related to an individual, a group, or an organization.
     */
    public const TAG_EVEN = 'EVEN';

    /**
     * Data assigned to the source.
     */
    public const TAG_DATA = 'DATA';

    /**
     * An assessment of the certainty of the evidence to support the conclusion drawn from evidence.
     */
    public const TAG_QUAY = 'QUAY';

    /**
     * @return string|null
     */
    public function getXref(): ?string;

    /**
     * @return string|null
     */
    public function getPage(): ?string;

    /**
     * @return EventInterface|null
     */
    public function getEvent(): ?EventInterface;

    /**
     * @return DataInterface|null
     */
    public function getData(): ?DataInterface;

    /**
     * @return string|null
     */
    public function getQuality(): ?string;
}
