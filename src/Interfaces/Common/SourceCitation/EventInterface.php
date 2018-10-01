<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common\SourceCitation;

/**
 * The SOUR (source citation) EVEN (event) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface EventInterface
{
    /**
     * A code that indicates the type of event which was responsible for the source entry being recorded.
     */
    const TAG_TYPE = 'EVENT_TYPE_CITED_FROM';

    /**
     * Indicates what role this person played in the event that is being cited in this context.
     */
    const TAG_ROLE = 'ROLE';

    /**
     * @return null|string
     */
    public function getType();

    /**
     * @return null|string
     */
    public function getRole();
}
