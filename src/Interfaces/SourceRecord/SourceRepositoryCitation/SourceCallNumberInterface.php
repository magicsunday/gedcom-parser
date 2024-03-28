<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\SourceRecord\SourceRepositoryCitation;

/**
 * The SOUR (source), REPO (repository), CALN (call number) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SourceCallNumberInterface
{
    /**
     * An identification or reference description used to file and retrieve items from the holdings
     * of a repository.
     */
    public const TAG_SOURCE_CALL_NUMBER = 'SOURCE_CALL_NUMBER';

    /**
     * A code, selected from one of the media classifications choices above, that indicates the type of
     * material in which the referenced source is stored.
     *
     * - audio
     * - book
     * - card
     * - electronic
     * - fiche
     * - film
     * - magazine
     * - manuscript
     * - map
     * - newspaper
     * - photo
     * - tombstone
     * - video
     */
    public const TAG_MEDI = 'MEDI';

    /**
     * @return string|null
     */
    public function getNumber(): ?string;

    /**
     * @return string|null
     */
    public function getMediaType(): ?string;
}
