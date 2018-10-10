<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualAttributeStructure;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetailInterface;

/**
 * The individual attribute detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface IndividualAttributeDetailInterface extends IndividualEventDetailInterface
{
    /**
     * A description detail label.
     */
    const TAG_DETAIL = 'DETAIL';

    /**
     * @return null|string
     */
    public function getDetail();
}
