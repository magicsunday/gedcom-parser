<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\SourceRecord;

use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\SourceRecord\SourceRepositoryCitation\SourceCallNumberInterface;

/**
 * The SOUR (source), REPO (repository) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SourceRepositoryCitationInterface extends NoteInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a repository record.
     */
    public const TAG_XREF_REPO = 'XREF:REPO';

    /**
     * The call number structure.
     */
    public const TAG_CALN = 'CALN';

    /**
     * @return string|null
     */
    public function getXref(): ?string;

    /**
     * @return SourceCallNumberInterface[]
     */
    public function getCallNumber(): array;
}
