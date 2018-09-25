<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom;

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
     * Returns TRUE if the level of the current line matches a previous one indicating we
     * completed a whole block of data.
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
}
