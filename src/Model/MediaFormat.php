<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

/**
 * The typed format of a multimedia file (the `FORM` substructure of an `OBJE.FILE`).
 *
 * Carries the multimedia format itself — a GEDCOM 5.5.1 code such as `jpg`/`tif` or a 7.0 media
 * type such as `image/jpeg` — together with the classifier of what the file depicts. That
 * classifier is version-specific: GEDCOM 5.5.1 uses a free-text `TYPE` ({@see self::$type}), while
 * GEDCOM 7.0 uses the enumerated `MEDI` ({@see self::$medi}); each stays NULL in the other version.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class MediaFormat
{
    /**
     * @param string|null $value The multimedia format (FORM), or NULL when absent.
     * @param string|null $type  The GEDCOM 5.5.1 free-text source media type (TYPE) classifying the file, or NULL.
     * @param Medium|null $medi  The GEDCOM 7.0 enumerated medium (MEDI) classifying the file, or NULL.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $type = null,
        public ?Medium $medi = null,
    ) {
    }
}
