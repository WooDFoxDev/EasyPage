<?php

namespace Easypage\Kernel\Interfaces;

use Closure;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;

interface MiddlewareInterface
{
    /**
     * Middleware should contain invocation method
     * that should return Request or Responce object
     *
     * @param  Request $request
     * @param  Closure $next
     * @return Request|Response
     */
    public function __invoke(Request $request, Closure $next): Request|Response;
}
