<?php

namespace Easypage\Kernel\Abstractions;

use Easypage\Kernel\Interfaces\ControllerInterface;

/**
 * Controller
 */
abstract class Controller implements ControllerInterface
{
    /**
     * The name of a Model class for the Controller 
     *
     * @var mixed
     */
    protected $model_class;
}
