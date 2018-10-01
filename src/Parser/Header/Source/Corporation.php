<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Header\Source;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header\Source\Corporation as CorporationModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\Address;

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
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return [
            CorporationModel::TAG_ADDR  => Address::class,
            CorporationModel::TAG_PHON  => Common::class,
            CorporationModel::TAG_EMAIL => Common::class,
            CorporationModel::TAG_FAX   => Common::class,
            CorporationModel::TAG_WWW   => Common::class,
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
        $corporation->setValue(CorporationModel::TAG_NAME_OF_BUSINESS, $this->reader->value());

        $this->process($corporation);

        return $corporation;
    }
}
