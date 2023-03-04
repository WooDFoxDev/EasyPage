<?php

namespace Easypage\Kernel\Abstractions;

use Easypage\Kernel\Interfaces\ControllerInterface;

abstract class Controller implements ControllerInterface
{
    protected $model_class;
}
