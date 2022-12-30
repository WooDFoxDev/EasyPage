<?php

namespace Easypage\Middleware;

use Easypage\Kernel\Abstractions\Middleware;
use Easypage\Kernel\Core;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;

class CSRFControl extends Middleware
{
    static ?string $new_token = null;

    protected function input(Request $request): Request|Response
    {
        if (in_array($request->method(), ['post', 'patch', 'delete'])) {

            $session = Core::getInstance()->getSession();

            $token = $request->post('_token');
            $token = $request->header('X-Csrf-Token') ?? $token;

            if (is_null($session->get('_token')) || is_null($token) || $token != $session->get('_token')) {
                return abort(http_code: 405, message: 'Wrong CSRF token, please, reload page');
            } else {
                $session->set('_token', null);
                self::$new_token = csrfToken();
            }
        }

        return parent::input($request);
    }

    protected function output(Response $response): Response
    {
        if (!is_null(self::$new_token) && $response->getHttpCode() == 200 && $response->isJson()) {
            $response->appendBodyJson(['_token_refresh' => csrfToken()]);
        }

        return parent::output($response);
    }
}
