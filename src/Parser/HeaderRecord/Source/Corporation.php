<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\HeaderRecord\Source;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\AddressStructureInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\Source\CorporationInterface;
use MagicSunday\Gedcom\Model\HeaderRecord\Source\Corporation as CorporationModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\AddressStructure;

/**
 * A CORP parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Corporation extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            AddressStructureInterface::TAG_ADDR  => AddressStructure::class,
            AddressStructureInterface::TAG_PHON  => Common::class,
            AddressStructureInterface::TAG_EMAIL => Common::class,
            AddressStructureInterface::TAG_FAX   => Common::class,
            AddressStructureInterface::TAG_WWW   => Common::class,
        ];
    }

    /**
     * Parses a CORP block.
     *
     * @return CorporationModel
     */
    public function parse(): CorporationModel
    {
        $corporation = new CorporationModel();
        $corporation->setValue(CorporationInterface::TAG_NAME_OF_BUSINESS, $this->reader->value());

        $this->process($corporation);

        return $corporation;
    }
}
