<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Exception;

use RuntimeException;
use Throwable;

use function sprintf;
use function trim;

/**
 * Thrown when a physical GEDCOM line does not conform to the grammar and cannot be
 * tokenised into its level, cross-reference, tag and value components.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class UnableToParseLineException extends RuntimeException implements ExceptionInterface
{
    /**
     * The raw, unparsable line as read from the input.
     *
     * @var string
     */
    private string $rawLine;

    /**
     * The 1-based number of the offending line within the document.
     *
     * @var int
     */
    private int $lineNumber;

    /**
     * @param string         $rawLine    The raw line that failed to tokenise.
     * @param int            $lineNumber The 1-based number of the offending line.
     * @param Throwable|null $previous   The underlying cause, if any.
     */
    public function __construct(string $rawLine, int $lineNumber, ?Throwable $previous = null)
    {
        $this->rawLine    = $rawLine;
        $this->lineNumber = $lineNumber;

        parent::__construct(
            sprintf('Unable to parse GEDCOM line %d: <%s>', $lineNumber, trim($rawLine)),
            0,
            $previous
        );
    }

    /**
     * Returns the raw line that could not be tokenised.
     *
     * @return string The offending line as read from the input.
     */
    public function getRawLine(): string
    {
        return $this->rawLine;
    }

    /**
     * Returns the 1-based number of the offending line within the document.
     *
     * @return int The line number the failure occurred on.
     */
    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }
}
