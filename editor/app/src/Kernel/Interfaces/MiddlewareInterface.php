<?php

namespace Easypage\Kernel\Interfaces;

use Closure;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;

interface MiddlewareInterface
{
    public function __invoke(Request $request, Closure $next): Request|Response;
}
