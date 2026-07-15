<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\ValueObject;

use MagicSunday\Gedcom\ValueObject\MapCoordinates;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit test of the GEDCOM 5.5.1 MAP coordinate parsing: the hemisphere-prefixed `LATI`/`LONG`
 * strings become signed decimal degrees, and malformed or incomplete input yields no coordinates.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(MapCoordinates::class)]
class MapCoordinatesTest extends TestCase
{
    /**
     * The hemisphere prefix (`N`/`S` for latitude, `E`/`W` for longitude) drives the sign of the
     * decimal degrees: north and east are positive, south and west negative.
     *
     * @param string $latitude          The raw LATI payload.
     * @param string $longitude         The raw LONG payload.
     * @param float  $expectedLatitude  The expected signed latitude.
     * @param float  $expectedLongitude The expected signed longitude.
     */
    #[Test]
    #[DataProvider('coordinateProvider')]
    public function parsesHemispherePrefixedDegrees(
        string $latitude,
        string $longitude,
        float $expectedLatitude,
        float $expectedLongitude,
    ): void {
        $coordinates = MapCoordinates::fromGedcom($latitude, $longitude);

        self::assertInstanceOf(MapCoordinates::class, $coordinates);
        self::assertSame($expectedLatitude, $coordinates->latitude);
        self::assertSame($expectedLongitude, $coordinates->longitude);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: float, 3: float}>
     */
    public static function coordinateProvider(): array
    {
        return [
            'north-east'         => ['N42.3601', 'E71.0589', 42.3601, 71.0589],
            'south-west'         => ['S42.3601', 'W71.0589', -42.3601, -71.0589],
            'integer degrees'    => ['N42', 'W71', 42.0, -71.0],
            'surrounding spaces' => ['  N42.36  ', '  W71.05  ', 42.36, -71.05],
            'boundary degrees'   => ['N90', 'W180', 90.0, -180.0],
            'south pole'         => ['S90', 'E180', -90.0, 180.0],
        ];
    }

    /**
     * A malformed, out-of-range or incomplete coordinate — a missing hemisphere prefix, a
     * wrong-axis prefix, a non-numeric body, an empty payload, or degrees beyond the ±90 latitude
     * / ±180 longitude bounds — yields no coordinates rather than a partial, mis-signed or
     * impossible value.
     *
     * @param string $latitude  The raw LATI payload.
     * @param string $longitude The raw LONG payload.
     */
    #[Test]
    #[DataProvider('malformedProvider')]
    public function rejectsMalformedInput(string $latitude, string $longitude): void
    {
        self::assertNull(MapCoordinates::fromGedcom($latitude, $longitude));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function malformedProvider(): array
    {
        return [
            'no prefix on latitude'   => ['42.3601', 'W71.0589'],
            'wrong axis on latitude'  => ['E42.3601', 'W71.0589'],
            'wrong axis on longitude' => ['N42.3601', 'N71.0589'],
            'non-numeric body'        => ['Nfoo', 'W71.0589'],
            'empty latitude'          => ['', 'W71.0589'],
            'empty longitude'         => ['N42.3601', ''],
            'latitude beyond 90'      => ['N90.5', 'W71.0589'],
            'longitude beyond 180'    => ['N42.3601', 'E180.5'],
            'south beyond 90'         => ['S90.5', 'W71.0589'],
            'west beyond 180'         => ['N42.3601', 'W180.5'],
        ];
    }
}
