<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function basename;
use function glob;

/**
 * Tests the high-level parser: it reads a GEDCOM stream into the typed GedcomDocument aggregate.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(Parser::class)]
class ParserTest extends TestCase
{
    /**
     * Parses an individual record into a typed IndividualRecord exposing its identifier, sex and
     * personal name (the raw slashed value plus the slash-free display name).
     */
    #[Test]
    public function parsePartial(): void
    {
        $stream = (new StreamFactory())->createStream(<<<GEDCOM
            0 @X116@ INDI
            1 SEX M
            1 BIRT
            2 DATE 01 JAN 1950
            1 DEAT
            2 DATE 01 JAN 2000
            1 FAMS @X118@
            1 NAME Lt. Cmndr. Max Joachim /der Edle/ von Musterhausen
            2 TYPE BIRTH
            2 GIVN Max Joachim
            2 SPFX der
            2 SURN Edle
            2 NPFX Lt. Cmndr.
            2 NSFX von Musterhausen
            GEDCOM);

        $stream->rewind();

        $document = (new Parser($stream))->parse();

        self::assertCount(1, $document->individuals);

        $individual = $document->individuals[0];

        self::assertSame('X116', $individual->xref);
        self::assertSame('M', $individual->sex);
        self::assertSame(
            'Lt. Cmndr. Max Joachim /der Edle/ von Musterhausen',
            $individual->name[0]->value
        );
        self::assertSame(
            'Lt. Cmndr. Max Joachim der Edle von Musterhausen',
            $individual->name[0]->getDisplayName()
        );
    }

    /**
     * A record carrying a substructure that is not modelled (here a child-sealing SLGC ordinance,
     * which is not in the typed model) is parsed without error, with the unmodelled substructure
     * simply ignored rather than failing the record.
     */
    #[Test]
    public function parsesAnUnmodelledSubstructureWithoutError(): void
    {
        $stream = (new StreamFactory())->createStream(<<<GEDCOM
            0 @I1@ INDI
            1 SLGC
            2 STAT BIC
            2 DATE 01 JAN 1970
            2 FAMC @F1@
            GEDCOM);

        $stream->rewind();

        $document = (new Parser($stream))->parse();

        self::assertCount(1, $document->individuals);
        self::assertSame('I1', $document->individuals[0]->xref);
    }

    /**
     * A GEDCOM 7.0 document declares its shared notes with the `SNOTE` record tag (renamed from
     * 5.5.1's `NOTE` record). The header version drives the 7.0 schema, and the SNOTE record maps
     * onto the same typed NoteRecord — including its 7.0 `LANG` and `MIME` substructures — grouped
     * under the document's notes.
     */
    #[Test]
    public function parsesA70SharedNoteRecord(): void
    {
        $stream = (new StreamFactory())->createStream(<<<GEDCOM
            0 HEAD
            1 GEDC
            2 VERS 7.0
            0 @N1@ SNOTE A shared note in a 7.0 document.
            1 MIME text/plain
            1 LANG en
            0 TRLR
            GEDCOM);

        $stream->rewind();

        $document = (new Parser($stream))->parse();

        self::assertCount(1, $document->notes);
        self::assertSame('N1', $document->notes[0]->xref);
        self::assertSame('A shared note in a 7.0 document.', $document->notes[0]->value);
        self::assertSame('en', $document->notes[0]->lang);
        self::assertSame('text/plain', $document->notes[0]->mime);
    }

    /**
     * A 5.5.1 shared-note record has no language or media-type substructure, so those 7.0-only
     * fields stay NULL.
     */
    #[Test]
    public function a551NoteRecordHasNoLanguageOrMediaType(): void
    {
        $stream = (new StreamFactory())->createStream(<<<GEDCOM
            0 HEAD
            1 GEDC
            2 VERS 5.5.1
            0 @N1@ NOTE A shared note in a 5.5.1 document.
            0 TRLR
            GEDCOM);

        $stream->rewind();

        $document = (new Parser($stream))->parse();

        self::assertCount(1, $document->notes);
        self::assertSame('A shared note in a 5.5.1 document.', $document->notes[0]->value);
        self::assertNull($document->notes[0]->lang);
        self::assertNull($document->notes[0]->mime);
    }

    /**
     * A GEDCOM 7.0 shared note may carry its text in other languages or media types as repeatable
     * `TRAN` substructures; each is mapped to a typed NoteTranslation with its own value, language
     * and media type, and a translation that omits the optional media type leaves it NULL.
     */
    #[Test]
    public function parsesA70SharedNoteTranslations(): void
    {
        $stream = (new StreamFactory())->createStream(<<<GEDCOM
            0 HEAD
            1 GEDC
            2 VERS 7.0
            0 @N1@ SNOTE The original note text.
            1 LANG en
            1 MIME text/plain
            1 TRAN Translated note text one.
            2 LANG de
            2 MIME text/html
            1 TRAN Segundo texto traducido.
            2 LANG es
            0 TRLR
            GEDCOM);

        $stream->rewind();

        $note = (new Parser($stream))->parse()->notes[0];

        self::assertCount(2, $note->tran);

        self::assertSame('Translated note text one.', $note->tran[0]->value);
        self::assertSame('de', $note->tran[0]->lang);
        self::assertSame('text/html', $note->tran[0]->mime);

        self::assertSame('Segundo texto traducido.', $note->tran[1]->value);
        self::assertSame('es', $note->tran[1]->lang);
        self::assertNull($note->tran[1]->mime);
    }

    /**
     * A GEDCOM 7.0 header documents its custom extension tags with `HEAD.SCHMA.TAG` lines; the
     * parser surfaces them as a tag-to-URI map on the aggregate.
     */
    #[Test]
    public function parsesA70HeaderExtensionTagSchema(): void
    {
        $stream = (new StreamFactory())->createStream(<<<GEDCOM
            0 HEAD
            1 GEDC
            2 VERS 7.0
            1 SCHMA
            2 TAG _LOC https://gedcom.io/terms/v7/LOC
            0 @I1@ INDI
            1 SEX M
            0 TRLR
            GEDCOM);

        $stream->rewind();

        $document = (new Parser($stream))->parse();

        self::assertSame(['_LOC' => ['https://gedcom.io/terms/v7/LOC']], $document->extensionTags);
        self::assertCount(1, $document->individuals);
    }

    /**
     * A cross-version record tag — a 7.0 `SNOTE` in a document whose header declares 5.5.1 (a
     * mixed-version file) — is not a record in the detected 5.5.1 schema, so it is tolerated and
     * skipped rather than aborting the parse; the records after it still map.
     */
    #[Test]
    public function toleratesACrossVersionRecordTag(): void
    {
        $stream = (new StreamFactory())->createStream(<<<GEDCOM
            0 HEAD
            1 GEDC
            2 VERS 5.5.1
            0 @N1@ SNOTE A 7.0 shared note in a 5.5.1 document.
            0 @I1@ INDI
            1 SEX M
            0 TRLR
            GEDCOM);

        $stream->rewind();

        $document = (new Parser($stream))->parse();

        self::assertSame([], $document->notes);
        self::assertCount(1, $document->individuals);
        self::assertSame('I1', $document->individuals[0]->xref);
    }

    /**
     * Provides every bundled GEDCOM fixture.
     *
     * @return array<string, array{0: string}>
     */
    public static function fixtureProvider(): array
    {
        $cases = [];
        $files = glob(__DIR__ . '/files/*.ged');

        foreach ($files === false ? [] : $files as $file) {
            $cases[basename($file)] = [$file];
        }

        return $cases;
    }

    /**
     * Every bundled fixture parses into a typed GedcomDocument without raising an exception.
     *
     * @param string $file The absolute path to the GEDCOM fixture.
     */
    #[DataProvider('fixtureProvider')]
    #[Test]
    public function parsesFixtureWithoutError(string $file): void
    {
        // parse() throws on any malformed record, so reaching the end without an exception is the
        // assertion; the return type already guarantees a GedcomDocument.
        $this->expectNotToPerformAssertions();

        $stream = (new StreamFactory())->createStreamFromFile($file);
        (new Parser($stream))->parse();
    }
}
