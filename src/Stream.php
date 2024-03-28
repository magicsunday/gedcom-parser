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
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function is_int;
use function is_resource;
use function is_string;

/**
 * Default implementation for the StreamInterface of the PSR-7 standard
 * Acts mainly as a decorator class for streams/resources.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-reader/
 */
class Stream implements StreamInterface
{
    /**
     * The actual PHP resource.
     *
     * @var resource|null
     */
    protected $resource;

    /**
     * @var string|resource
     */
    protected $stream;

    /**
     * Constructor setting up the PHP resource.
     *
     * @param string|resource $stream A filename or resource
     * @param string          $mode   Mode with which to open stream
     *
     * @throws InvalidArgumentException
     */
    public function __construct($stream, string $mode = 'r')
    {
        $this->stream = $stream;

        if (is_resource($stream)) {
            $this->resource = $stream;
        } elseif (is_string($stream)) {
            $this->resource = fopen($stream, $mode) ?: null;
        } else {
            throw new InvalidArgumentException(
                'Invalid stream provided; must be a string stream identifier or resource'
            );
        }
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception to conform with PHP's
     * string casting operations.
     *
     * @see https://php.net/manual/en/language.oop5.magic.php#object.tostring
     *
     * @return string
     */
    public function __toString(): string
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            $this->rewind();

            return $this->getContents();
        } catch (RuntimeException $exception) {
            return '';
        }
    }

    /**
     * Closes the stream and any underlying resources.
     */
    public function close(): void
    {
        if (!is_resource($this->resource)) {
            return;
        }

        $resource = $this->detach();

        if ($resource === null) {
            return;
        }

        fclose($resource);
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $resource       = $this->resource;
        $this->resource = null;

        return $resource;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown
     */
    public function getSize(): ?int
    {
        if ($this->resource === null) {
            return null;
        }

        $stats = fstat($this->resource);

        return $stats !== false ? $stats['size'] : null;
    }

    /**
     * Returns the current position of the file read/write pointer.
     *
     * @return int Position of the file pointer
     *
     * @throws RuntimeException on error
     */
    public function tell(): int
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('No resource available; cannot tell position');
        }

        $result = ftell($this->resource);

        if (!is_int($result)) {
            throw new RuntimeException('Error occurred during tell operation');
        }

        return $result;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof(): bool
    {
        if (!is_resource($this->resource)) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * Returns whether the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        return (bool) $this->getMetadata('seekable');
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     *
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *                    based on the seek offset. Valid values are identical to the built-in
     *                    PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *                    offset bytes SEEK_CUR: Set position to current location plus offset
     *                    SEEK_END: Set position to end-of-stream plus offset.
     *
     * @throws RuntimeException on failure
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('No resource available; cannot seek position');
        }

        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable');
        }

        $result = fseek($this->resource, $offset, $whence);

        if ($result !== 0) {
            throw new RuntimeException('Error seeking within stream');
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     *
     * @throws RuntimeException on failure
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * Returns whether the stream is writable.
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $uri = $this->getMetadata('uri');

        return is_writable($uri);
    }

    /**
     * Write data to the stream.
     *
     * @param string $string the string that is to be written
     *
     * @return int returns the number of bytes written to the stream
     *
     * @throws RuntimeException on failure
     */
    public function write(string $string): int
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('No resource available; cannot write');
        }

        $result = fwrite($this->resource, $string);

        if ($result === false) {
            throw new RuntimeException('Error writing to stream');
        }

        return $result;
    }

    /**
     * Returns whether the stream is readable.
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $mode = $this->getMetadata('mode');

        return str_contains($mode, 'r') || str_contains($mode, '+');
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *                    them. Fewer than $length bytes may be returned if underlying stream
     *                    call returns fewer bytes.
     *
     * @return string returns the data read from the stream, or an empty string
     *                if no bytes are available
     *
     * @throws RuntimeException if an error occurs
     */
    public function read(int $length): string
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('No resource available; cannot read');
        }

        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }

        $result = fread($this->resource, $length);

        if ($result === false) {
            throw new RuntimeException('Error reading stream');
        }

        return $result;
    }

    /**
     * Returns the remaining contents in a string.
     *
     * @return string
     *
     * @throws RuntimeException if unable to read or an error occurs while
     *                          reading
     */
    public function getContents(): string
    {
        if (!is_resource($this->resource) || !$this->isReadable()) {
            return '';
        }

        $result = stream_get_contents($this->resource);

        if ($result === false) {
            throw new RuntimeException('Error reading from stream');
        }

        return $result;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link https://php.net/manual/en/function.stream-get-meta-data.php
     *
     * @param string $key specific metadata to retrieve
     *
     * @return array|mixed|null Returns an associative array if no key is
     *                          provided. Returns a specific key value if a key is provided and the
     *                          value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        $metadata = stream_get_meta_data($this->resource);

        if ($key === null) {
            return $metadata;
        }

        return $metadata[$key] ?? null;
    }

    /**
     * Gets line from the file pointer.
     *
     * @return string
     *
     * @throws RuntimeException if an error occurs
     */
    public function fgets(): string
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('No resource available; cannot read');
        }

        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }

        $result = fgets($this->resource);

        if ($result === false) {
            if ($this->eof()) {
                return '';
            }

            throw new RuntimeException('Error reading stream');
        }

        return $result;
    }
}
