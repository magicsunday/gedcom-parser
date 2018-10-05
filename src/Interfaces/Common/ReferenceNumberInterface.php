<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common;

/**
 * The REFN structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface ReferenceNumberInterface
{
    /**
     * A user-defined number or text that the submitter uses to identify this record. For instance, it may be a
     * record number within the submitter's automated or manual system, or it may be a page and position
     * number on a pedigree chart.
     */
    const TAG_USER_REFERENCE_NUMBER = 'USER_REFERENCE_NUMBER';

    /**
     * A user-defined definition of the USER_REFERENCE_NUMBER.
     */
    const TAG_TYPE = 'TYPE';

    /**
     * @return null|string
     */
    public function getNumber();

    /**
     * @return null|string
     */
    public function getType();
}
