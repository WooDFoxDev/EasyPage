<?php

namespace Easypage\Kernel;

use Easypage\Kernel\Abstractions\ObjectsArray;
use Easypage\Kernel\Entity;

class EntitiesArray extends ObjectsArray
{
    protected function validate($value): void
    {
        if (!$value instanceof Entity) {
            throw new \InvalidArgumentException(
                'Not an instance of Entity'
            );
        }
    }
}
