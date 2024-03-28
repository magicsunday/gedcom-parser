<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use ArrayAccess;

use function array_key_exists;
use function is_array;

/**
 * A data object.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class DataObject implements ArrayAccess
{
    /**
     * The internal data array.
     *
     * @var array
     */
    private array $data = [];

    /**
     * Returns the stored value to the given key.
     *
     * @param string $key The key to the internal record
     *
     * @return mixed
     */
    public function getValue(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Returns a single value or multiple values always as an array.
     *
     * @param string $key The key to the internal record
     *
     * @return array
     */
    public function getArrayValue(string $key): array
    {
        $value = $this->getValue($key);

        if ($value === null) {
            $value = [];
        }

        if (!is_array($value)) {
            return [$value];
        }

        return $value;
    }

    /**
     * Sets a value to the provided key. Multiple values will be stored as an array.
     *
     * @param string $key   The key used to store the value
     * @param mixed  $value The value to store
     *
     * @return self
     */
    public function setValue(string $key, $value): self
    {
        if (isset($this->data[$key])) {
            if (!is_array($this->data[$key])) {
                $this->data[$key] = [$this->data[$key]];
            }

            $this->data[$key][] = $value;
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]) || array_key_exists($offset, $this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }
}
