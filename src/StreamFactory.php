<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom;

use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function in_array;
use function is_resource;

/**
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-reader/
 */
class StreamFactory implements StreamFactoryInterface
{
    /**
     * Create a new stream from a string.
     *
     * @param string $content String content with which to populate the stream
     *
     * @return StreamInterface
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $stream = new Stream('php://temp', 'r+');

        if ($content !== '') {
            $stream->write($content);
        }

        return $stream;
    }

    /**
     * Create a stream from an existing file. The `$filename` may be any string supported by `fopen()`.
     *
     * @param string $filename Filename or stream URI to use as a basis of stream
     * @param string $mode     Mode with which to open the underlying filename/stream
     *
     * @return StreamInterface
     *
     * @throws RuntimeException         If the file cannot be opened
     * @throws InvalidArgumentException If the mode is invalid
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = @fopen($filename, $mode);

        if ($resource === false) {
            if ($mode === '' || in_array($mode[0], ['r', 'w', 'a', 'x', 'c'], true) === false) {
                throw new InvalidArgumentException('The mode ' . $mode . ' is invalid.');
            }

            throw new RuntimeException('The file ' . $filename . ' cannot be opened.');
        }

        return new Stream($resource);
    }

    /**
     * Create a new stream from an existing resource. The stream must be readable and may be writable.
     *
     * @param resource $resource PHP resource to use as a basis of stream
     *
     * @return StreamInterface
     *
     * @throws InvalidArgumentException
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        if (!is_resource($resource) || get_resource_type($resource) !== 'stream') {
            throw new InvalidArgumentException('Invalid stream provided; must be a stream resource');
        }

        return new Stream($resource);
    }
}
