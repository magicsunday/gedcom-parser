<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\ValueObject;

use function preg_match;
use function strtoupper;
use function trim;

/**
 * The geographic coordinates of a place (the `MAP` substructure of a `PLACE_STRUCTURE`).
 *
 * GEDCOM 5.5.1 expresses each axis as a hemisphere letter followed by decimal degrees — `LATI`
 * as `N`/`S` (e.g. `N42.3601`) and `LONG` as `E`/`W` (e.g. `W71.0589`). Both are converted to
 * signed decimal degrees, north and east positive, south and west negative.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class MapCoordinates
{
    /**
     * @param float $latitude  The latitude in signed decimal degrees (north positive, south negative)
     * @param float $longitude The longitude in signed decimal degrees (east positive, west negative)
     */
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
    }

    /**
     * Parses a raw GEDCOM MAP latitude/longitude pair into typed coordinates.
     *
     * @param string $latitude  The raw LATI payload, e.g. `N42.3601`
     * @param string $longitude The raw LONG payload, e.g. `W71.0589`
     *
     * @return self|null The parsed coordinates, or NULL when either axis is malformed or absent
     */
    public static function fromGedcom(string $latitude, string $longitude): ?self
    {
        $parsedLatitude  = self::parseAxis($latitude, 'N', 'S', 90.0);
        $parsedLongitude = self::parseAxis($longitude, 'E', 'W', 180.0);

        if (($parsedLatitude === null) || ($parsedLongitude === null)) {
            return null;
        }

        return new self($parsedLatitude, $parsedLongitude);
    }

    /**
     * Parses one hemisphere-prefixed axis into signed decimal degrees within its valid bounds.
     *
     * @param string $value      The raw axis payload
     * @param string $positive   The hemisphere letter denoting a positive value (`N` or `E`)
     * @param string $negative   The hemisphere letter denoting a negative value (`S` or `W`)
     * @param float  $maxDegrees The inclusive upper bound on the magnitude (90 latitude, 180
     *                           longitude); a value beyond it is an impossible coordinate
     *
     * @return float|null The signed degrees, or NULL when the payload does not match the axis or
     *                    exceeds its bounds
     */
    private static function parseAxis(string $value, string $positive, string $negative, float $maxDegrees): ?float
    {
        if (preg_match('/^([' . $positive . $negative . '])(\d+(?:\.\d+)?)$/i', trim($value), $matches) !== 1) {
            return null;
        }

        $degrees = (float) $matches[2];

        if ($degrees > $maxDegrees) {
            return null;
        }

        return strtoupper($matches[1]) === $positive ? $degrees : -$degrees;
    }
}
