<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common;

/**
 * The common event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface EventDetailInterface extends AddressStructureInterface
{
    const TAG_TYPE  = 'TYPE';
    const TAG_DATE  = 'DATE';
    const TAG_PLAC  = 'PLAC';
    const TAG_AGNC  = 'AGNC';
    const TAG_RELI  = 'RELI';
    const TAG_CAUS  = 'CAUS';
    const TAG_RESN  = 'RESN';
    const TAG_NOTE  = 'NOTE';
    const TAG_SOUR  = 'SOUR';
    const TAG_OBJE  = 'OBJE';
}
