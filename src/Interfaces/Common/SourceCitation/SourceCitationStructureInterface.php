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
 * @license https://opensource.org/licenses/MIT
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

    public function getXref(): ?string;

    public function getPage(): ?string;

    public function getEvent(): ?EventInterface;

    public function getData(): ?DataInterface;

    public function getQuality(): ?string;
}
