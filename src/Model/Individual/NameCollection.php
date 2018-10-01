<?php
declare(strict_types = 1);
/**
 * See LICENSE.md file for further details.
 */
namespace MagicSunday\Webtrees\Gedcom\Individual;

use MagicSunday\Webtrees\AbstractCollection;

/**
 * A names collection.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NameCollection extends AbstractCollection
{
    /**
     * Constructs a list of names.
     *
     * @param array $names List of names
     */
    public function __construct(array $names = [])
    {
        parent::__construct(
            array_map(
                function ($name) {
                    return new Name($name);
                },
                $names
            )
        );
    }
}
