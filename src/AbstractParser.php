<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Parser\Custom;
use Psr\Log\LoggerInterface;

/**
 * A gedcom 5.5.1 parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
abstract class AbstractParser
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var Reader
     */
    protected Reader $reader;

    /**
     * The previous read level.
     *
     * @var int
     */
    private int $previousLevel;

    /**
     * Individual constructor.
     *
     * @param Reader          $reader
     * @param LoggerInterface $logger
     */
    public function __construct(Reader $reader, LoggerInterface $logger)
    {
        $this->logger        = $logger;
        $this->reader        = $reader;
        $this->previousLevel = $this->reader->level();
    }

    /**
     * @return string|DataObject
     */
    abstract public function parse();

    /**
     * This maps the GEDCOM tags to the proper parser classes.
     *
     * @return array<string, string>
     */
    abstract protected function getClassMap(): array;

    /**
     * Creates an instance of a parser matching the given GEDCOM tag.
     *
     * @param string $gedcomTag A GEDCOM tag
     *
     * @return AbstractParser|null
     */
    private function create(string $gedcomTag): ?AbstractParser
    {
        $className = $this->getClassMap()[$gedcomTag] ?? null;

        return ($className && class_exists($className))
            ? new $className($this->reader, $this->logger)
            : null;
    }

    /**
     * @param DataObject $object
     *
     * @return DataObject
     */
    protected function process(DataObject $object): DataObject
    {
        while ($this->reader->read() && $this->valid()) {
            $gedcomTag = $this->reader->tag();

            // Trailer found, stop further processing
            if ($gedcomTag === 'TRLR') {
                break;
            }

            $subParser = $this->create($gedcomTag);

            if (!$subParser instanceof AbstractParser) {
                $this->logger->info('Invalid GEDCOM 5.5.1 tag <' . $gedcomTag . '> found.');

                $subParser = new Custom($this->reader, $this->logger);
            }

            $object->setValue($gedcomTag, $subParser->parse());
        }

        return $object;
    }

    /**
     * Returns TRUE if the level of the current line matches a previous one
     * indicating we completed a whole block of data.
     *
     * @return bool
     */
    protected function valid(): bool
    {
        if ($this->reader->level() <= $this->previousLevel) {
            $this->reader->back();

            return false;
        }

        return true;
    }

    /**
     * Returns the complete content of CONT and CONC.
     *
     * @return string|null
     */
    protected function readContent(): ?string
    {
        $content = $this->reader->value();

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->tag()) {
                // Continuation
                case 'CONT':
                    if ($content !== '') {
                        $content .= "\n";
                    }

                    $content .= $this->reader->value();
                    break;

                    // Concatenation
                case 'CONC':
                    $content .= $this->reader->value();
                    break;

                    // Otherwise stop reading this block
                default:
                    // Go back one line
                    $this->reader->back();
                    break 2;
            }
        }

        return $content;
    }
}
