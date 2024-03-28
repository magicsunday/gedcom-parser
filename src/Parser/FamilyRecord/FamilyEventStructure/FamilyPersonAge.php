<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\FamilyRecord\FamilyEventStructure;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyPersonAgeInterface;
use MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure\FamilyPersonAge as FamilyPersonAgeModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A FAM (family) event AGE structure parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyPersonAge extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            FamilyPersonAgeInterface::TAG_AGE => Common::class,
        ];
    }

    /**
     * Parse an AGE block.
     *
     * @return FamilyPersonAgeModel
     */
    public function parse(): FamilyPersonAgeModel
    {
        $age = new FamilyPersonAgeModel();

        $this->process($age);

        return $age;
    }
}
