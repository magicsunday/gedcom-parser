<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common\ChangeDate;

use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;

/**
 * The CHAN structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface ChangeDateStructureInterface extends NoteInterface
{
    /**
     * The date that this data was changed.
     */
    const TAG_DATE = 'DATE';

    /**
     * @return DateExactInterface
     */
    public function getDateExact(): DateExactInterface;
}
