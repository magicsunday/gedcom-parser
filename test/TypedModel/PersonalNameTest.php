<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\TypedModel;

use MagicSunday\Gedcom\TypedModel\PersonalName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit test of the slash-convention derivation on the typed personal name: given / surname /
 * suffix are read from the `given /surname/ suffix` value, explicit name-part pieces win, and the
 * display name is always slash-free.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(PersonalName::class)]
class PersonalNameTest extends TestCase
{
    /**
     * The slash convention `given /surname/ suffix` is decomposed into its parts; a missing
     * trailing slash is tolerated, a value with no slashes yields a given name only, and each
     * empty fragment collapses to NULL.
     *
     * @param string|null $value           The raw slash-delimited name value
     * @param string|null $expectedGiven   The expected derived given name
     * @param string|null $expectedSurname The expected derived surname
     * @param string|null $expectedSuffix  The expected derived suffix
     */
    #[Test]
    #[DataProvider('slashValueProvider')]
    public function derivesPartsFromTheSlashValue(
        ?string $value,
        ?string $expectedGiven,
        ?string $expectedSurname,
        ?string $expectedSuffix,
    ): void {
        $name = new PersonalName($value);

        self::assertSame($expectedGiven, $name->getGivenName(), 'given name');
        self::assertSame($expectedSurname, $name->getSurname(), 'surname');
        self::assertSame($expectedSuffix, $name->getSuffix(), 'suffix');
    }

    /**
     * Data provider for the slash-convention derivation: a raw value and its expected given /
     * surname / suffix parts.
     *
     * @return array<string, array{0: ?string, 1: ?string, 2: ?string, 3: ?string}>
     */
    public static function slashValueProvider(): array
    {
        return [
            'given and surname'         => ['John /Doe/', 'John', 'Doe', null],
            'given, surname and suffix' => ['John /Doe/ Jr', 'John', 'Doe', 'Jr'],
            'multi-word surname'        => ['John /van Doe/', 'John', 'van Doe', null],
            'surrounding whitespace'    => ['  John   /Doe/   Jr  ', 'John', 'Doe', 'Jr'],
            'missing trailing slash'    => ['John /Doe', 'John', 'Doe', null],
            'surname only'              => ['/Doe/', null, 'Doe', null],
            'empty surname'             => ['John // Jr', 'John', null, 'Jr'],
            'no slashes at all'         => ['Madonna', 'Madonna', null, null],
            'empty value'               => ['', null, null, null],
            'absent value'              => [null, null, null, null],
        ];
    }

    /**
     * An explicit name-part piece always wins over the slash-derived fragment, even when the value
     * would derive a different part.
     */
    #[Test]
    public function explicitPiecesWinOverTheSlashDerivation(): void
    {
        $name = new PersonalName(
            value: 'Johnny /Doe/ Jr',
            givn: 'Jonathan',
            surn: 'Smith',
            nsfx: 'PhD',
        );

        self::assertSame('Jonathan', $name->getGivenName());
        self::assertSame('Smith', $name->getSurname());
        self::assertSame('PhD', $name->getSuffix());
    }

    /**
     * The display name strips the surname-delimiting slashes from the raw value and collapses the
     * resulting whitespace, regardless of any explicit pieces.
     */
    #[Test]
    public function displayNameStripsSlashesFromTheValue(): void
    {
        $name = new PersonalName(value: 'John  /van Doe/  Jr', surn: 'Smith');

        self::assertSame('John van Doe Jr', $name->getDisplayName());
        self::assertStringNotContainsString('/', $name->getDisplayName());
    }

    /**
     * Non-space whitespace in the value — a tab or a newline, as continuation lines can produce —
     * is collapsed to single spaces just like ordinary spaces, so the display name is always
     * cleanly separated.
     */
    #[Test]
    public function displayNameCollapsesNonSpaceWhitespace(): void
    {
        $name = new PersonalName(value: "John\t/Doe/\nJr");

        self::assertSame('John Doe Jr', $name->getDisplayName());
    }

    /**
     * With no value present, the display name is assembled from the explicit pieces in reading
     * order (prefix, given, surname prefix, surname, suffix).
     */
    #[Test]
    public function displayNameAssemblesTheExplicitPiecesWhenNoValueIsPresent(): void
    {
        $name = new PersonalName(
            givn: 'Jonathan',
            surn: 'Doe',
            npfx: 'Dr',
            spfx: 'van',
            nsfx: 'PhD',
        );

        self::assertSame('Dr Jonathan van Doe PhD', $name->getDisplayName());
    }

    /**
     * With neither a value nor any piece present, the display name is an empty string.
     */
    #[Test]
    public function displayNameIsEmptyWhenNothingIsKnown(): void
    {
        self::assertSame('', (new PersonalName())->getDisplayName());
    }
}
