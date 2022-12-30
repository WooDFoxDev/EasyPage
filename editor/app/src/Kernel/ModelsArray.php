<?php

namespace Easypage\Kernel;

use Easypage\Kernel\Abstractions\Model;
use Easypage\Kernel\Abstractions\ObjectsArray;

class ModelsArray extends ObjectsArray
{
    protected function validate($value): void
    {
        if (!$value instanceof Model) {
            throw new \InvalidArgumentException(
                'Not an instance of Entity'
            );
        }
    }
}
