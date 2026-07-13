<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom;

use Psr\Http\Message\StreamInterface;

/**
 * A PSR-7 stream that can additionally yield a single line at a time, as required by the
 * line-oriented GEDCOM reader.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface ReadableStreamInterface extends StreamInterface
{
    /**
     * Reads a line from the stream, up to and including its line terminator.
     *
     * @return string The next line, or an empty string at the end of the stream.
     */
    public function fgets(): string;
}
