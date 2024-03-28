<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\HeaderRecord;

/**
 * The note structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface NoteInterface
{
    /**
     * A note that a user enters to describe the contents of the lineage-linked file in terms of
     * "ancestors or descendants of" so that the person receiving the data knows what genealogical information the
     * transmission contains.
     */
    public const TAG_GEDCOM_CONTENT_DESCRIPTION = 'GEDCOM_CONTENT_DESCRIPTION';

    /**
     * @return string|null
     */
    public function getContent(): ?string;
}
