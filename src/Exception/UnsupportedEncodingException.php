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
 * Thrown when a document declares a character encoding the reader cannot decode — for
 * example a BOM-less `CHAR UNICODE` stream, which cannot be distinguished from single-byte
 * input and must be rejected rather than silently mis-parsed.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class UnsupportedEncodingException extends RuntimeException implements ExceptionInterface
{
    /**
     * The declared character set that could not be handled.
     *
     * @var string
     */
    private string $characterSet;

    /**
     * @param string         $characterSet The declared, unhandled character set.
     * @param Throwable|null $previous     The underlying cause, if any.
     */
    public function __construct(string $characterSet, ?Throwable $previous = null)
    {
        $this->characterSet = $characterSet;

        parent::__construct(
            sprintf('Unsupported or undetectable character encoding: %s', $characterSet),
            0,
            $previous
        );
    }

    /**
     * Returns the declared character set that could not be handled.
     *
     * @return string The offending character set.
     */
    public function getCharacterSet(): string
    {
        return $this->characterSet;
    }
}
