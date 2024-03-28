<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
    public const TAG_TYPE = 'EVENT_TYPE_CITED_FROM';

    /**
     * Indicates what role this person played in the event that is being cited in this context.
     */
    public const TAG_ROLE = 'ROLE';

    /**
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * @return string|null
     */
    public function getRole(): ?string;
}
