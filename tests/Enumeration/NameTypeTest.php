<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Enumeration;

use MagicSunday\Gedcom\Enumeration\NameType;
use MagicSunday\Gedcom\Model\PersonalName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests the NAME-TYPE enumeration constant holder: its constants carry the raw spec values, its
 * value list stays in sync with the vendored registry, and the carrying model field remains a
 * tolerant string.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(NameType::class)]
#[UsesClass(PersonalName::class)]
class NameTypeTest extends TestCase
{
    /**
     * Every constant's value is its own name — the NAME-TYPE tokens are legal identifiers — which
     * catches a value typo or a name/value swap the registry set comparison would miss.
     */
    #[Test]
    public function eachConstantNameEqualsItsRawSpecValue(): void
    {
        EnumerationRegistry::assertConstantNamesEqualValues(NameType::class);
    }

    /**
     * The holder's value list matches the vendored `enumset-NAME-TYPE` registry exactly.
     */
    #[Test]
    public function theValueListMatchesTheRegistry(): void
    {
        EnumerationRegistry::assertMatchesRegistry('NAME-TYPE', NameType::values());
    }

    /**
     * The carrying model field stays a tolerant string: a known value round-trips as its constant,
     * and an unlisted or extension value is preserved rather than rejected.
     */
    #[Test]
    public function theModelFieldRemainsATolerantString(): void
    {
        self::assertSame(NameType::AKA, (new PersonalName(type: NameType::AKA))->type);

        self::assertNotContains('_CUSTOM', NameType::values(), 'an extension value is not a known standard value');
        self::assertSame('_CUSTOM', (new PersonalName(type: '_CUSTOM'))->type, 'an extension value is preserved on the model');
    }
}
