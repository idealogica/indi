<?php
namespace Idealogica\InDI;

trait PropertyAccessTrait
{
    /**
     * Gets value.
     *
     * @param string $id
     * @return mixed
     */
    public function __get(string $id)
    {
        return $this->get($id);
    }

    /**
     * Adds value.
     *
     * @param string $id
     * @param mixed $value
     */
    public function __set(string $id, $value)
    {
        $this->add($id, $value);
    }

    /**
     * Checks if value exists.
     *
     * @param string $id
     * @return bool
     */
    public function __isset(string $id)
    {
        return $this->has($id);
    }

    /**
     * Removes previously defined value.
     *
     * @param string $id
     */
    public function __unset(string $id)
    {
        $this->remove($id);
    }
}
