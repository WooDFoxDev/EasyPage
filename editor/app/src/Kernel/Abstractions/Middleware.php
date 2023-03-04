<?php


namespace Easypage\Kernel\Abstractions;

use Closure;
use Easypage\Kernel\Interfaces\MiddlewareInterface;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;

/**
 * Middleware
 */
abstract class Middleware implements MiddlewareInterface
{
    /**
     * Default invocation method
     * Adds the middleware to the request processing queue
     *
     * @param  Request $request
     * @param  Closure $next
     * @return Request|Response
     */
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

    /**
     * Processes Request on input chain
     * Returns Request for further processing
     * May return Response to prevent further processing
     *
     * @param  mixed $request
     * @return Request
     */
    protected function input(Request $request): Request|Response
    {
        return $request;
    }

    /**
     * Processes Response on output chain
     * Returns Response
     *
     * @param  mixed $response
     * @return Response
     */
    protected function output(Response $response): Response
    {
        return $response;
    }
}
