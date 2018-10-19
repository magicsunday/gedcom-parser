<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common\SourceCitation;

/**
 * The SOUR (source citation) DATA (data) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface DataInterface
{
    /**
     * The date that this event data was entered into the original source document.
     */
    const TAG_DATE = 'DATE';

    /**
     * A verbatim copy of any description contained within the source.
     */
    const TAG_TEXT = 'TEXT';

    /**
     * @return null|string
     */
    public function getDate();

    /**
     * @return string[]
     */
    public function getText(): array;
}
