<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common;

use MagicSunday\Gedcom\Interfaces\Common\PlaceStructure\MapInterface;
use MagicSunday\Gedcom\Interfaces\Common\PlaceStructure\PlacePhoneticVariationInterface;
use MagicSunday\Gedcom\Interfaces\Common\PlaceStructure\PlaceRomanizedVariationInterface;

/**
 * The PLAC (place) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface PlaceStructureInterface extends NoteInterface
{
    /**
     * The jurisdictional name of the place where the event took place. Jurisdictions are separated by commas.
     */
    public const TAG_PLACE_NAME = 'PLACE_NAME';

    /**
     * This shows the jurisdictional entities that are named in a sequence from the lowest to the highest
     * jurisdiction. The jurisdictions are separated by commas, and any jurisdiction's name that is missing is
     * still accounted for by a comma.
     */
    public const TAG_FORM = 'FORM';

    /**
     * The phonetic variation of the place name.
     */
    public const TAG_FONE = 'FONE';

    /**
     * The romanized variation of the place name.
     */
    public const TAG_ROMN = 'ROMN';

    /**
     * Pertains to a representation of measurements usually presented in a graphical form.
     */
    public const TAG_MAP = 'MAP';

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string|null
     */
    public function getFormat(): ?string;

    /**
     * @return PlacePhoneticVariationInterface[]
     */
    public function getPhoneticVariation(): array;

    /**
     * @return PlaceRomanizedVariationInterface[]
     */
    public function getRomanizedVariation(): array;

    /**
     * @return MapInterface|null
     */
    public function getMap(): ?MapInterface;
}
