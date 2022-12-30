<?php


namespace Easypage\Kernel\Abstractions;

use Closure;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;

abstract class Middleware
{
    public function __invoke(Request $request, Closure $next)
    {
        $input = $this->input($request);

        if (is_a($input, Request::class)) {
            $output = $next($input);
        } else {
            $output = $input;
        }

        return $this->output($output);
    }

    protected function input(Request $request): Request|Response
    {
        return $request;
    }

    protected function output(Response $response): Response
    {
        return $response;
    }
}
