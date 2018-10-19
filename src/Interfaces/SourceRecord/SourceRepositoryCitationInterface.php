<?php
/**
 * See LICENSE.md file for further details.
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
    const TAG_XREF_REPO = 'XREF:REPO';

    /**
     * The call number structure.
     */
    const TAG_CALN = 'CALN';

    /**
     * @return null|string
     */
    public function getXref();

    /**
     * @return SourceCallNumberInterface[]
     */
    public function getCallNumber(): array;
}
