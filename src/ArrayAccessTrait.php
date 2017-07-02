<?php
namespace Idealogica\InDI;

trait ArrayAccessTrait
{
    /**
     * Rewinds values array to the beginning and returns first value.
     *
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->values);
    }

    /**
     * Returns current value.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->values);
    }

    /**
     * Returns current value id.
     *
     * @return string
     */
    public function key()
    {
        return key($this->values);
    }

    /**
     * Checks if current value valid.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->key() !== null;
    }

    /**
     * Advances values array pointer and returns next value.
     *
     * @return mixed
     */
    public function next()
    {
        return next($this->values);
    }

    /**
     * Gets value.
     *
     * @param string $id
     * @return mixed
     */
    public function offsetGet($id)
    {
        return $this->get($id);
    }

    /**
     * Adds value.
     *
     * @param string $id
     * @param mixed $value
     * @return $this
     */
    public function offsetSet($id, $value)
    {
        return $this->add($id, $value);
    }

    /**
     * Checks if value exists.
     *
     * @param string $id
     * @return bool
     */
    public function offsetExists($id)
    {
        return $this->has($id);
    }

    /**
     * Removes previously defined value.
     *
     * @param string $id
     * @return $this
     */
    public function offsetUnset($id)
    {
        return $this->remove($id);
    }

    /**
     * Counts values.
     *
     * @return int
     */
    public function count()
    {
        return count($this->values);
    }
}
