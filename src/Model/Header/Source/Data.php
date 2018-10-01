<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Header\Source;

use MagicSunday\Gedcom\Model\Common\DateExact;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The data structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Data extends DataObject
{
    /**
     * The name of the electronic data source that was used to obtain the data in this transmission.
     */
    const TAG_NAME_OF_SOURCE_DATA = 'NAME_OF_SOURCE_DATA';

    /**
     * The date this source was published or created.
     */
    const TAG_DATE = 'DATE';

    /**
     * A copyright statement required by the owner of data from which this information was downloaded.
     */
    const TAG_COPR = 'COPR';

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->getValue(self::TAG_NAME_OF_SOURCE_DATA);
    }

    /**
     * @return null|DateExact
     */
    public function getDate()
    {
        return $this->getValue(self::TAG_DATE);
    }

    /**
     * @return null|string
     */
    public function getCopyright()
    {
        return $this->getValue(self::TAG_COPR);
    }
}
