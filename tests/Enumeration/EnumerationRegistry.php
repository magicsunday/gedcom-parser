<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Enumeration;

use PHPUnit\Framework\Assert;
use ReflectionClass;
use Symfony\Component\Yaml\Yaml;

use function dirname;
use function sort;
use function strlen;
use function substr;

/**
 * Shared assertions for the enumeration constant holders, so each holder's test pins its value list
 * against the vendored registry and its constant names without duplicating the parsing logic.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class EnumerationRegistry
{
    /**
     * Private constructor; this is a static-only test helper.
     */
    private function __construct()
    {
    }

    /**
     * Reads the known standard values of an enumeration set from the vendored registry, stripping the
     * term-URI prefix down to each local token name.
     *
     * @param string $setName The enumeration-set name (e.g. `MEDI`).
     *
     * @return list<string> The registry's local token names for the set.
     */
    private static function values(string $setName): array
    {
        $prefix = 'https://gedcom.io/terms/v7/enum-';
        $file   = dirname(__DIR__, 2)
            . '/docs/spec/gedcom7-registries/enumeration-set/standard/enumset-' . $setName . '.yaml';

        $data = Yaml::parseFile($file);
        Assert::assertIsArray($data);
        Assert::assertArrayHasKey('enumeration values', $data);
        Assert::assertIsArray($data['enumeration values']);

        $values = [];

        foreach ($data['enumeration values'] as $uri) {
            Assert::assertIsString($uri);
            Assert::assertStringStartsWith($prefix, $uri);
            $values[] = substr($uri, strlen($prefix));
        }

        return $values;
    }

    /**
     * Asserts that a holder's value list matches the vendored registry for the set exactly, so a
     * registry change that adds or removes a standard value is caught rather than silently drifting.
     *
     * @param string       $setName      The enumeration-set name (e.g. `MEDI`).
     * @param list<string> $holderValues The values reported by the holder's `values()`.
     *
     * @return void
     */
    public static function assertMatchesRegistry(string $setName, array $holderValues): void
    {
        $expected = self::values($setName);

        sort($expected);
        sort($holderValues);

        Assert::assertSame($expected, $holderValues, 'the holder values must match the ' . $setName . ' registry');
    }

    /**
     * Asserts that every constant of a holder equals its own name, catching a value typo or a
     * name/value swap the registry set comparison cannot see. Use it only for a holder whose tokens
     * are legal identifiers, so each constant is named after its value; a set that instead names its
     * constants by meaning (such as SEX) is pinned by an explicit constant-to-token mapping.
     *
     * @param class-string $className The holder class whose constants are named after their tokens.
     *
     * @return void
     */
    public static function assertConstantNamesEqualValues(string $className): void
    {
        $constants = (new ReflectionClass($className))->getConstants();
        Assert::assertNotEmpty($constants);

        foreach ($constants as $name => $value) {
            Assert::assertSame($name, $value, 'the constant value must equal its name (the raw token)');
        }
    }
}
