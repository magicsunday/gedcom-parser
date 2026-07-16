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
 * Thrown when the number of bytes read from a source stream exceeds the configured maximum,
 * signalling an oversized or hostile input. Bounding the total byte count caps the work a single
 * parse can do independently of the transport size, which in particular defeats a decompression
 * bomb on the GEDZIP path (a tiny archive whose `gedcom.ged` entry inflates to a huge dataset)
 * as well as an unbounded plain `.ged` upload.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class InputTooLargeException extends RuntimeException implements ExceptionInterface
{
    /**
     * The configured maximum number of bytes that may be read from the source.
     *
     * @var int
     */
    private int $maxBytes;

    /**
     * @param int            $maxBytes The configured maximum number of bytes.
     * @param Throwable|null $previous The underlying cause, if any.
     */
    public function __construct(int $maxBytes, ?Throwable $previous = null)
    {
        $this->maxBytes = $maxBytes;

        parent::__construct(
            sprintf('GEDCOM input exceeds the maximum of %d bytes.', $maxBytes),
            0,
            $previous
        );
    }

    /**
     * Returns the configured maximum number of bytes that may be read from the source.
     *
     * @return int The configured byte cap the input exceeded.
     */
    public function getMaxBytes(): int
    {
        return $this->maxBytes;
    }
}
