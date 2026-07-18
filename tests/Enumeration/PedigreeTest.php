<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Enumeration;

use MagicSunday\Gedcom\Enumeration\PedigreeType;
use MagicSunday\Gedcom\Model\ChildToFamilyLink;
use MagicSunday\Gedcom\Model\Substructure\Common\Pedigree;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PEDI enumeration constant holder: its constants carry the raw spec values, its value
 * list stays in sync with the vendored registry, and the carrying model keeps the value tolerantly
 * on a typed pedigree.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(PedigreeType::class)]
#[UsesClass(Pedigree::class)]
#[UsesClass(ChildToFamilyLink::class)]
class PedigreeTest extends TestCase
{
    /**
     * Every constant's value is its own name — the PEDI tokens are legal identifiers — which catches
     * a value typo or a name/value swap the registry set comparison would miss.
     */
    #[Test]
    public function eachConstantNameEqualsItsRawSpecValue(): void
    {
        EnumerationRegistry::assertConstantNamesEqualValues(PedigreeType::class);
    }

    /**
     * The holder's value list matches the vendored `enumset-PEDI` registry exactly.
     */
    #[Test]
    public function theValueListMatchesTheRegistry(): void
    {
        EnumerationRegistry::assertMatchesRegistry('PEDI', PedigreeType::values());
    }

    /**
     * The carrying model keeps the value tolerantly: a known one round-trips as its constant, and an
     * unlisted or extension value is preserved rather than rejected. The value now lives on a typed
     * pedigree so the phrase qualifying it has a home too, but its tolerance is unchanged.
     */
    #[Test]
    public function theModelFieldRemainsATolerantValue(): void
    {
        $known = new ChildToFamilyLink('F1', new Pedigree(PedigreeType::BIRTH));
        self::assertSame(PedigreeType::BIRTH, $known->pedi?->value);

        self::assertNotContains('_CUSTOM', PedigreeType::values(), 'an extension value is not a known standard value');

        $extension = new ChildToFamilyLink('F1', new Pedigree('_CUSTOM'));
        self::assertSame('_CUSTOM', $extension->pedi?->value, 'an extension value is preserved on the model');
    }
}
