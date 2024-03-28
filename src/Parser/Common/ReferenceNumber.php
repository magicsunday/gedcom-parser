<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\ReferenceNumberInterface;
use MagicSunday\Gedcom\Model\Common\ReferenceNumber as ReferenceNumberModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A REFN parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class ReferenceNumber extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            ReferenceNumberInterface::TAG_TYPE => Common::class,
        ];
    }

    /**
     * Parses a REFN block.
     *
     * @return ReferenceNumberModel
     */
    public function parse(): ReferenceNumberModel
    {
        $referenceNumber = new ReferenceNumberModel();
        $referenceNumber->setValue(ReferenceNumberInterface::TAG_USER_REFERENCE_NUMBER, $this->reader->value());

        $this->process($referenceNumber);

        return $referenceNumber;
    }
}
