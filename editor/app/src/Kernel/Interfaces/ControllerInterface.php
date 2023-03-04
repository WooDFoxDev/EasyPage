<?php

namespace Easypage\Kernel\Interfaces;

use Easypage\Kernel\Response;

interface ControllerInterface
{
    /**
     * Default method for every app controller
     *
     * @return Response
     */
    public function index(): Response;
}
