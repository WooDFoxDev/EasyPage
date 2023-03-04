<?php


namespace Easypage\Kernel\Abstractions;

use Closure;
use Easypage\Kernel\Interfaces\MiddlewareInterface;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;

abstract class Middleware implements MiddlewareInterface
{
    public function __invoke(Request $request, Closure $next): Request|Response
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
