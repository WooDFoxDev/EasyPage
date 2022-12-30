<?php

namespace Easypage\Kernel\Interfaces;

use Easypage\Kernel\Response;

interface ControllerInterface
{
    public function index(): Response;
}
