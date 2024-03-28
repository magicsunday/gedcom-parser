<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

use MagicSunday\Gedcom\Interfaces\Common\FamilyChildInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail\ChristeningInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

/**
 * The individual CHR (christening) event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Christening extends IndividualEventDetail implements ChristeningInterface
{
    /**
     * {@inheritDoc}
     */
    public function getFlag(): ?string
    {
        return $this->getValue(self::TAG_FLAG);
    }

    /**
     * {@inheritDoc}
     */
    public function getFamilyChild(): ?FamilyChildInterface
    {
        return $this->getValue(self::TAG_FAMC);
    }
}
