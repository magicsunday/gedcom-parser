<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common;

use MagicSunday\Gedcom\Interfaces\Common\AddressStructure\AddressBlockInterface;

/**
 * The address structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface AddressStructureInterface
{
    /**
     * The address block.
     */
    const TAG_ADDR = 'ADDR';

    /**
     * A phone number.
     */
    const TAG_PHON = 'PHON';

    /**
     * A email address.
     */
    const TAG_EMAIL = 'EMAIL';

    /**
     * A fax number.
     */
    const TAG_FAX = 'FAX';

    /**
     * A website address.
     */
    const TAG_WWW = 'WWW';

    /**
     * @return AddressBlockInterface
     */
    public function getAddress(): AddressBlockInterface;

    /**
     * @return string[]
     */
    public function getPhoneNumber(): array;

    /**
     * @return string[]
     */
    public function getEmailAddress(): array;

    /**
     * @return string[]
     */
    public function getFaxNumber(): array;

    /**
     * @return string[]
     */
    public function getWwwAddress(): array;
}
