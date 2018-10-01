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

//    /**
//     * DataObject constructor.
//     *
//     * @param array $data
//     */
//    public function __construct(array $data = [])
//    {
//        $this->data = $data;
//    }

    /**
     * @param string $key
     *
     * @return null|mixed
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
                $this->data[$key] = [$this->data[$key]];
            }

            $this->data[$key][] = $value;
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]) || array_key_exists($offset, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }
}
