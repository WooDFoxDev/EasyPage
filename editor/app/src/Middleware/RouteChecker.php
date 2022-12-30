<?php

namespace Easypage\Middleware;

use Easypage\Kernel\Abstractions\Middleware;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;

class RouteChecker extends Middleware
{
    protected function input(Request $request): Request|Response
    {
        if ($request->route() === false) {
            return abort();
        }

        return parent::input($request);
    }
}
