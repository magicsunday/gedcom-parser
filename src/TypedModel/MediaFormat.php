<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\TypedModel;

/**
 * The typed format of a multimedia file (the `FORM` substructure of an `OBJE.FILE`).
 *
 * Carries the multimedia format itself — for example `jpeg` or `pdf` — and the optional source
 * media `TYPE` that classifies what the file depicts, such as `photo` or `document`.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class MediaFormat
{
    /**
     * @param string|null $value The multimedia format (FORM), or NULL when absent
     * @param string|null $type  The source media type (TYPE) classifying the file, or NULL
     */
    public function __construct(
        public ?string $value = null,
        public ?string $type = null,
    ) {
    }
}
