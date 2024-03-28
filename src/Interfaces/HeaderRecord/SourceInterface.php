<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\HeaderRecord;

use MagicSunday\Gedcom\Interfaces\HeaderRecord\Source\CorporationInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\Source\DataInterface;

/**
 * The source structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SourceInterface
{
    /**
     * A system identification name which was obtained through the GEDCOM registration process. This
     * name must be unique from any other product. Spaces within the name must be substituted with a 0x5F
     * (underscore _) so as to create one word.
     */
    public const TAG_APPROVED_SYSTEM_ID = 'APPROVED_SYSTEM_ID';

    /**
     * An identifier that represents the version level assigned to the associated product. It is defined and
     * changed by the creators of the product.
     */
    public const TAG_VERS = 'VERS';

    /**
     * The name of the software product that produced this transmission.
     */
    public const TAG_NAME = 'NAME';

    /**
     * The corporation structure.
     */
    public const TAG_CORP = 'CORP';

    /**
     * The data structure.
     */
    public const TAG_DATA = 'DATA';

    /**
     * @return string
     */
    public function getApprovedSystemId(): string;

    /**
     * @return string|null
     */
    public function getVersion(): ?string;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @return CorporationInterface|null
     */
    public function getCorporation(): ?CorporationInterface;

    /**
     * @return DataInterface|null
     */
    public function getData(): ?DataInterface;
}
