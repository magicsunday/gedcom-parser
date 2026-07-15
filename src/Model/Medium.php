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
 * The GEDCOM 7.0 medium classifying a multimedia file (the `MEDI` substructure of an `OBJE.FILE.FORM`).
 *
 * Names what the file depicts as one of the 7.0 enumerated media — `PHOTO`, `AUDIO`, `VIDEO`, … —
 * kept as a plain string so an extension or the catch-all `OTHER` value does not throw. When the
 * medium is `OTHER`, a {@see self::$phrase} carries its human-readable description.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class Medium
{
    /**
     * @param string|null $value  The enumerated medium value (MEDI), e.g. `PHOTO`, or NULL when absent.
     * @param string|null $phrase The human-readable description accompanying an `OTHER` medium (PHRASE),
     *                            or NULL when absent.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $phrase = null,
    ) {
    }
}
