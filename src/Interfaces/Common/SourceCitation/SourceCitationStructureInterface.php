<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common\SourceCitation;

use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLinkInterface;

/**
 * The SOUR (source citation) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SourceCitationStructureInterface extends
    MultimediaLinkInterface,
    NoteInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a SOURce record.
     */
    const TAG_XREF_SOUR = 'XREF:SOUR';

    /**
     * A number or description to identify where information can be found in a referenced work.
     */
    const TAG_PAGE = 'PAGE';

    /**
     * Pertaining to a noteworthy happening related to an individual, a group, or an organization.
     */
    const TAG_EVEN = 'EVEN';

    /**
     * Data assigned to the source.
     */
    const TAG_DATA = 'DATA';

    /**
     * An assessment of the certainty of the evidence to support the conclusion drawn from evidence.
     */
    const TAG_QUAY = 'QUAY';

    /**
     * @return null|string
     */
    public function getXref();

    /**
     * @return null|string
     */
    public function getPage();

    /**
     * @return null|EventInterface
     */
    public function getEvent();

    /**
     * @return null|DataInterface
     */
    public function getData();

    /**
     * @return null|string
     */
    public function getQuality();
}
