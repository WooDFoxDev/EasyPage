<?php

namespace Easypage\Middleware;

use Easypage\Kernel\Abstractions\Middleware;
use Easypage\Kernel\Authenticator;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;

class Authenticate extends Middleware
{
    protected function input(Request $request): Request|Response
    {
        $is_authenticated = Authenticator::isLoggedIn();

        if (!$is_authenticated && in_array('authenticated', $request->route()['guards'])) {
            return redirectTo('/login');
        }

        return parent::input($request);
    }
}
