<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Enumeration;

use MagicSunday\Gedcom\Enumeration\MediumType;
use MagicSunday\Gedcom\Model\Medium;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Yaml\Yaml;

use function dirname;
use function sort;
use function strlen;
use function substr;

/**
 * Tests the MEDI enumeration constant holder: its constants carry the raw spec values, its value
 * list stays in sync with the vendored registry, and the carrying model field remains a tolerant
 * string.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(MediumType::class)]
#[UsesClass(Medium::class)]
class MediumTypeTest extends TestCase
{
    /**
     * Every constant's value is its own name: the MEDI tokens are legal identifiers, so the holder
     * names each constant after its raw spec value. This catches a value typo or a name/value swap
     * that the registry set comparison — which only checks the set of values — would miss.
     */
    #[Test]
    public function eachConstantNameEqualsItsRawSpecValue(): void
    {
        $constants = (new ReflectionClass(MediumType::class))->getConstants();
        self::assertNotEmpty($constants);

        foreach ($constants as $name => $value) {
            self::assertSame($name, $value, 'the constant value must equal its name (the raw MEDI token)');
        }
    }

    /**
     * The holder's value list matches the vendored `enumset-MEDI` registry exactly, so a future
     * registry change that adds or removes a standard value is caught rather than silently drifting.
     */
    #[Test]
    public function theValueListMatchesTheRegistry(): void
    {
        $prefix = 'https://gedcom.io/terms/v7/enum-';
        $file   = dirname(__DIR__, 2)
            . '/docs/spec/gedcom7-registries/enumeration-set/standard/enumset-MEDI.yaml';

        $data = Yaml::parseFile($file);
        self::assertIsArray($data);
        self::assertArrayHasKey('enumeration values', $data);
        self::assertIsArray($data['enumeration values']);

        $expected = [];

        foreach ($data['enumeration values'] as $uri) {
            self::assertIsString($uri);
            self::assertStringStartsWith($prefix, $uri);
            $expected[] = substr($uri, strlen($prefix));
        }

        $actual = MediumType::values();

        sort($expected);
        sort($actual);

        self::assertSame($expected, $actual);
    }

    /**
     * The carrying model field stays a tolerant string: a known value round-trips as its constant,
     * and an unlisted or extension value is preserved rather than rejected.
     */
    #[Test]
    public function theModelFieldRemainsATolerantString(): void
    {
        self::assertSame(MediumType::PHOTO, (new Medium(MediumType::PHOTO))->value);

        self::assertNotContains('_CUSTOM', MediumType::values(), 'an extension value is not a known standard value');
        self::assertSame('_CUSTOM', (new Medium('_CUSTOM'))->value, 'an extension value is preserved on the model');
    }
}
