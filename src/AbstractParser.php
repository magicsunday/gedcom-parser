<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Model\DataObject;
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
    protected $logger;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * The previous read level.
     *
     * @var int
     */
    private $previousLevel;

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
     *
     * @return string|DataObject
     */
    abstract public function parse();

    /**
     *
     * @return array
     */
    abstract protected function getClassMap(): array;

    /**
     * Creates an instance of a parser matching the given GEDCOM tag.
     *
     * @param string $gedcomTag A GEDCOM tag
     *
     * @return null|AbstractParser
     */
    public function create(string $gedcomTag)
    {
        $className = $this->getClassMap()[$gedcomTag] ?? null;

        return ($className && class_exists($className))
            ? new $className($this->reader, $this->logger)
            : null;
    }

    /**
     * @param DataObject $object
     *
     * @return object|DataObject
     */
    protected function process(DataObject $object)
    {
        while ($this->reader->read() && $this->valid()) {
            $gedcomTag = $this->reader->tag();
            $subParser = $this->create($gedcomTag);

            if ($subParser) {
                $object->setValue($gedcomTag, $subParser->parse());
            } else {
                $this->logger->error('Missing parser for tag <' . $gedcomTag . '>');
            }
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
     * @return string
     */
    protected function readContent(): string
    {
        $content = '';

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
            }
        }

        return $content;
    }
}
