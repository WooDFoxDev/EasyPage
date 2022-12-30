<?php

namespace Easypage\Middleware;

use Easypage\Kernel\Abstractions\Middleware;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;
use Easypage\Models\UserModel;

class Install extends Middleware
{

    protected function input(Request $request): Request|Response
    {
        // Assume that it is cold start, if there is no users configured
        $is_installed = UserModel::countAll() > 0;

        if ($is_installed && in_array('install', $request->route()['guards'])) {
            return redirectTo('/');
        }

        if (!$is_installed && !in_array('install', $request->route()['guards'])) {
            return redirectTo('/install');
        }

        return parent::input($request);
    }
}
