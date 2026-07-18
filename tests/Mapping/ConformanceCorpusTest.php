<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Mapping;

use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function array_map;
use function array_unique;
use function basename;
use function count;
use function dirname;
use function glob;

/**
 * Every GEDCOM file the repository ships is parsed into the typed model (#177).
 *
 * The targeted tests each pin one structure from a fixture built for it. This does the opposite: it
 * runs the whole corpus — real files from several vendors, both versions, every line ending, ANSEL
 * and UTF-16 encodings, and the deliberately awkward continuation cases — through the parser and
 * checks that what came out matches what went in.
 *
 * The expectation is not a golden number but a count derived from the file itself: it is read a
 * second time at the line level, and every level-0 record line must have produced a record. That is
 * what makes this a net rather than a smoke test — a mapping that silently drops records fails here,
 * and the count adapts on its own to a corpus file added later.
 *
 * Two boundaries, so the net is not mistaken for more than it is. The second read uses the same
 * reader the parse does, so it nets the MAPPING layer alone: were the reader itself to drop or
 * mis-split a line, expectation and result would shift together and this would pass. The encoding and
 * line-ending behaviour the corpus exercises is pinned by the reader's own tests, not here. And a
 * record that maps but comes back hollow passes too — only the targeted tests speak to what a record
 * contains.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversNothing]
class ConformanceCorpusTest extends TestCase
{
    /**
     * The corpus files the net must not shrink below, so a mis-pointed directory is loud rather than
     * an empty run.
     */
    private const int MINIMUM_CORPUS_FILES = 20;

    /**
     * Every level-0 individual and family line of every corpus file produces a typed record, and the
     * cross-references stay unique.
     *
     * @param string $path The corpus file to parse.
     */
    #[Test]
    #[DataProvider('corpusFiles')]
    public function everyCorpusRecordSurvivesTheMapping(string $path): void
    {
        $name     = basename($path);
        $document = $this->parseFile($path);

        // Counted from the same file at the line level, so the expectation carries no hardcoded
        // numbers and follows the corpus rather than a snapshot of it.
        $expected = $this->countLevel0Tags($path);

        self::assertCount($expected['INDI'], $document->individuals, $name . ': every INDI line maps to an individual');
        self::assertCount($expected['FAM'], $document->families, $name . ': every FAM line maps to a family');
        self::assertCount($expected['SOUR'], $document->sources, $name . ': every SOUR line maps to a source');
        self::assertCount($expected['REPO'], $document->repositories, $name . ': every REPO line maps to a repository');
        self::assertCount($expected['OBJE'], $document->multimedia, $name . ': every OBJE line maps to a multimedia record');
        self::assertCount($expected['SUBM'], $document->submitters, $name . ': every SUBM line maps to a submitter');

        // The two spellings of a note record fold into one bucket, which the corpus spans.
        self::assertCount(
            $expected['NOTE'] + $expected['SNOTE'],
            $document->notes,
            $name . ': every note record line maps to a note'
        );

        // The corpus holds genealogy, so a file mapping no individual at all means the mapping
        // collapsed rather than that the file is unusual.
        self::assertNotSame([], $document->individuals, $name . ' maps to no individuals');

        // A cross-reference identifies its record; two records sharing one means the mapping mixed
        // them up, which no count would reveal.
        $xrefs = array_map(static fn (object $record): string => $record->xref, $document->individuals);
        self::assertSameSize($xrefs, array_unique($xrefs), $name . ': individual cross-references are unique');
    }

    /**
     * The corpus files shipped with the repository.
     *
     * The fixture directory is shared with the reader and archive tests, so every `.ged` added there
     * joins this net and must hold at least one individual. A header-only or deliberately malformed
     * fixture therefore belongs in its own directory rather than beside the corpus.
     *
     * @return iterable<string, array{string}> The file path, keyed by file name.
     */
    public static function corpusFiles(): iterable
    {
        $directory = dirname(__DIR__) . '/files';
        $paths     = glob($directory . '/*.ged');

        if ($paths === false) {
            throw new RuntimeException('The corpus directory is unreadable: ' . $directory);
        }

        if (count($paths) < self::MINIMUM_CORPUS_FILES) {
            throw new RuntimeException('The corpus shrank unexpectedly, found ' . count($paths) . ' files');
        }

        foreach ($paths as $path) {
            yield basename($path) => [$path];
        }
    }

    /**
     * Counts the level-0 record lines of the given file, reading it independently of the mapping
     * under test so the expectation is derived rather than remembered.
     *
     * @param string $path The corpus file to read.
     *
     * @return array<string, int> The line count per record tag.
     */
    private function countLevel0Tags(string $path): array
    {
        $reader = new Reader((new StreamFactory())->createStreamFromFile($path));
        $counts = ['INDI' => 0, 'FAM' => 0, 'SOUR' => 0, 'REPO' => 0, 'OBJE' => 0, 'SUBM' => 0, 'NOTE' => 0, 'SNOTE' => 0];

        while ($reader->read()) {
            if (($reader->level() === 0) && isset($counts[$reader->tag()])) {
                ++$counts[$reader->tag()];
            }
        }

        return $counts;
    }

    /**
     * Parses the given corpus file into the typed document.
     *
     * @param string $path The corpus file to parse.
     *
     * @return GedcomDocument The parsed document.
     */
    private function parseFile(string $path): GedcomDocument
    {
        return (new Parser((new StreamFactory())->createStreamFromFile($path)))->parse();
    }
}
