<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\ChangeDate;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\ChangeDate\ChangeDateStructureInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Model\Common\ChangeDate\ChangeDateStructure as ChangeDateStructureModel;
use MagicSunday\Gedcom\Parser\Common\DateExact;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;

/**
 * A CHAN parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class ChangeDateStructure extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            ChangeDateStructureInterface::TAG_DATE => DateExact::class,
            NoteInterface::TAG_NOTE                => NoteStructure::class,
        ];
    }

    /**
     * Parses a CHAN block.
     *
     * @return ChangeDateStructureModel
     */
    public function parse(): ChangeDateStructureModel
    {
        $changeDateStructure = new ChangeDateStructureModel();

        $this->process($changeDateStructure);

        return $changeDateStructure;
    }
}
