<?php
/**
 * See LICENSE.md file for further details.
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
    const TAG_PLACE_NAME = 'PLACE_NAME';

    /**
     * This shows the jurisdictional entities that are named in a sequence from the lowest to the highest
     * jurisdiction. The jurisdictions are separated by commas, and any jurisdiction's name that is missing is
     * still accounted for by a comma.
     */
    const TAG_FORM = 'FORM';

    /**
     * The phonetic variation of the place name.
     */
    const TAG_FONE = 'FONE';

    /**
     * The romanized variation of the place name.
     */
    const TAG_ROMN = 'ROMN';

    /**
     * Pertains to a representation of measurements usually presented in a graphical form.
     */
    const TAG_MAP = 'MAP';

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return null|string
     */
    public function getFormat();

    /**
     * @return null|PlacePhoneticVariationInterface
     */
    public function getPhoneticVariation();

    /**
     * @return null|PlaceRomanizedVariationInterface
     */
    public function getRomanizedVariation();

    /**
     * @return null|MapInterface
     */
    public function getMap();
}
