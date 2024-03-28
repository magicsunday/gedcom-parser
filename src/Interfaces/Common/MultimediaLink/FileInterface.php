<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common\MultimediaLink;

use MagicSunday\Gedcom\Interfaces\Common\MultimediaLink\File\FormatInterface;

/**
 * The OBJE (object) structure tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface FileInterface
{
    /**
     * A complete local or remote file reference to the auxiliary data to be linked to the GEDCOM context.
     * Remote reference would include a network address where the multimedia data may be obtained.
     */
    public const TAG_FILE_REFN = 'FILE_REFN';

    /**
     * The format structure.
     */
    public const TAG_FORM = 'FORM';

    /**
     * @return string|null
     */
    public function getReference(): ?string;

    /**
     * @return FormatInterface
     */
    public function getFormat(): FormatInterface;
}
