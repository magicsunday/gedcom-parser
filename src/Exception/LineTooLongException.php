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

/**
 * Thrown when a single physical line has no terminator within the maximum permitted length,
 * signalling a malformed, binary or hostile stream. Bounding the line length keeps the
 * reader's memory usage record-by-record instead of materialising the whole stream as one
 * line.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class LineTooLongException extends RuntimeException implements ExceptionInterface
{
    /**
     * The 1-based number of the offending line within the document.
     *
     * @var int
     */
    private int $lineNumber;

    /**
     * @param int            $lineNumber the 1-based number of the offending line
     * @param int            $maxLength  the maximum permitted line length in bytes
     * @param Throwable|null $previous   the underlying cause, if any
     */
    public function __construct(int $lineNumber, int $maxLength, ?Throwable $previous = null)
    {
        $this->lineNumber = $lineNumber;

        parent::__construct(
            sprintf(
                'GEDCOM line %d exceeds the maximum length of %d bytes without a terminator.',
                $lineNumber,
                $maxLength
            ),
            0,
            $previous
        );
    }

    /**
     * Returns the 1-based number of the offending line within the document.
     *
     * @return int the line number the failure occurred on
     */
    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }
}
