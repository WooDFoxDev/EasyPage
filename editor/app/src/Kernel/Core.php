<?php

namespace Easypage\Kernel;

use Easypage\Kernel\Abstractions\Kernel;
use Easypage\Middleware\Authenticate;
use Easypage\Middleware\CSRFControl;
use Easypage\Middleware\Install;
use Easypage\Middleware\RouteChecker;

/**
 * Core
 */
class Core extends Kernel
{
    protected array $middleware_global = [
        RouteChecker::class,
        Install::class,
        Authenticate::class,
        CSRFControl::class,
    ];

    protected array $middleware_api = [];

    protected array $middleware_web = [];
}
