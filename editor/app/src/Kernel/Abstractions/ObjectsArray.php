<?php

namespace Easypage\Kernel\Abstractions;

abstract class ObjectsArray extends \ArrayObject
{
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->validate($item);
        }
    }

    public function append($value): void
    {
        $this->validate($value);
        parent::append($value);
    }

    public function offsetSet($key, $value): void
    {
        $this->validate($value);
        parent::offsetSet($key, $value);
    }

    protected function validate($value): void
    {
        /* Check instance for being a right class
        *  Like in example below
        *
        *  if (!$value instanceof Model) {
        *      throw new \InvalidArgumentException(
        *          'Not an instance of Entity'
        *      );
        *  }
        */
    }
}
