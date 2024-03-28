<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance;

use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;

/**
 * The common LDS individual ordinance interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface CommonIndividualOrdinanceInterface extends NoteInterface, SourceCitationInterface
{
    /**
     * LDS ordinance dates use only the Gregorian date and most often use the form of day, month, and
     * year. Only in rare instances is there a partial date.
     */
    public const TAG_DATE = 'DATE';

    /**
     * An abbreviation of the temple in which LDS temple ordinances were performed.
     */
    public const TAG_TEMP = 'TEMP';

    /**
     * The locality of the place where a living LDS ordinance took place. Typically, a living LDS baptism
     * place would be recorded in this field.
     */
    public const TAG_PLAC = 'PLAC';

    /**
     * The date status.
     */
    public const TAG_STAT = 'STAT';

    /**
     * @return string|null
     */
    public function getDate(): ?string;

    /**
     * @return string|null
     */
    public function getTempleCode(): ?string;

    /**
     * @return string|null
     */
    public function getPlace(): ?string;

    /**
     * @return CommonDateStatusInterface|null
     */
    public function getDateStatus(): ?CommonDateStatusInterface;
}
