<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Mapping;

use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Reader;
use Psr\Http\Message\StreamInterface;

/**
 * A GEDCOM record stream opened up to its first level-0 record, with the header identified.
 *
 * Both {@see GedcomDocumentReader} and {@see TypedGedcomParser} start the same way — wrap the stream
 * in a tree reader, pull the first record and, when it is the `HEAD`, keep it for the version, place
 * hierarchy and extension-tag metadata declared there. This value object captures that shared
 * prologue so each reader can go straight to its own mapping loop.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class RecordStream
{
    /**
     * The header record tag whose substructures carry the document-level metadata.
     */
    private const string TAG_HEAD = 'HEAD';

    /**
     * @param GedcomTreeReader $reader      The tree reader positioned after the first record.
     * @param GedcomNode       $firstRecord The first level-0 record (the header when present).
     * @param GedcomNode|null  $header      The parsed HEAD record, or NULL when the document has none.
     */
    private function __construct(
        public GedcomTreeReader $reader,
        public GedcomNode $firstRecord,
        public ?GedcomNode $header,
    ) {
    }

    /**
     * Opens the stream and reads its first level-0 record, or NULL when the stream carries none.
     *
     * @param StreamInterface $stream The GEDCOM stream to open.
     *
     * @return self|null The opened record stream, or NULL when it is empty.
     */
    public static function open(StreamInterface $stream): ?self
    {
        $reader = new GedcomTreeReader(new Reader($stream));
        $node   = $reader->readRecord();

        if (!$node instanceof GedcomNode) {
            return null;
        }

        return new self($reader, $node, $node->tag === self::TAG_HEAD ? $node : null);
    }
}
