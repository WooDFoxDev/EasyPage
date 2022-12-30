<?php

namespace Easypage\Kernel\Abstractions;

use Easypage\Kernel\Abstractions\Model;
use Easypage\Kernel\Interfaces\ControllerInterface;
use Easypage\Kernel\ModelsArray;
use Easypage\Kernel\Storage;
use Easypage\Kernel\Storage\EntitiesArray;
use Easypage\Kernel\Storage\Entity;
use Easypage\Models\PageModel;

abstract class Controller implements ControllerInterface
{
    protected $model_class;
}
