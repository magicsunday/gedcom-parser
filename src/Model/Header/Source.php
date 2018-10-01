<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Header;

use MagicSunday\Gedcom\Model\DataObject;
use \MagicSunday\Gedcom\Model\Header\Source\Corporation;
use \MagicSunday\Gedcom\Model\Header\Source\Data;

/**
 * The source structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Source extends DataObject
{
    /**
     * A system identification name which was obtained through the GEDCOM registration process. This
     * name must be unique from any other product. Spaces within the name must be substituted with a 0x5F
     * (underscore _) so as to create one word.
     */
    const TAG_APPROVED_SYSTEM_ID = 'APPROVED_SYSTEM_ID';

    /**
     * An identifier that represents the version level assigned to the associated product. It is defined and
     * changed by the creators of the product.
     */
    const TAG_VERS = 'VERS';

    /**
     * The name of the software product that produced this transmission.
     */
    const TAG_NAME = 'NAME';

    /**
     * The corporation structure.
     */
    const TAG_CORP = 'CORP';

    /**
     * The data structure.
     */
    const TAG_DATA = 'DATA';

    /**
     * @return null|string
     */
    public function getApprovedSystemId()
    {
        return $this->getValue(self::TAG_APPROVED_SYSTEM_ID);
    }

    /**
     * @return null|string
     */
    public function getVersion()
    {
        return $this->getValue(self::TAG_VERS);
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->getValue(self::TAG_NAME);
    }

    /**
     * @return null|Corporation
     */
    public function getCorporation(): Corporation
    {
        return $this->getValue(self::TAG_CORP);
    }

    /**
     * @return null|Data
     */
    public function getData(): Data
    {
        return $this->getValue(self::TAG_DATA);
    }
}
