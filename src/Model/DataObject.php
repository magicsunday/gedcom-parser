<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use ArrayAccess;

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
     * @var array
     */
    private $data;

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getValue(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return self
     */
    public function setValue(string $key, $value): self
    {
        if (isset($this->data[$key])) {
            if (!\is_array($this->data[$key])) {
                $this->data[$key] = [ $this->data[$key] ];
            }

            $this->data[$key][] = $value;
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]) || array_key_exists($offset, $this->data);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }
}
