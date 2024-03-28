<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\HeaderRecord\Source;

use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;

/**
 * The data structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface DataInterface
{
    /**
     * The name of the electronic data source that was used to obtain the data in this transmission.
     */
    public const TAG_NAME_OF_SOURCE_DATA = 'NAME_OF_SOURCE_DATA';

    /**
     * The date this source was published or created.
     */
    public const TAG_DATE = 'DATE';

    /**
     * A copyright statement required by the owner of data from which this information was downloaded.
     */
    public const TAG_COPR = 'COPR';

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @return DateExactInterface|null
     */
    public function getPublicationDate(): ?DateExactInterface;

    /**
     * @return string|null
     */
    public function getCopyright(): ?string;
}
