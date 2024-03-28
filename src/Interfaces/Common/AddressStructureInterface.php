<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
    public const TAG_ADDR = 'ADDR';

    /**
     * A phone number.
     */
    public const TAG_PHON = 'PHON';

    /**
     * An email address.
     */
    public const TAG_EMAIL = 'EMAIL';

    /**
     * A fax number.
     */
    public const TAG_FAX = 'FAX';

    /**
     * A website address.
     */
    public const TAG_WWW = 'WWW';

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
