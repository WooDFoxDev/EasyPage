<?php

namespace Easypage\Controllers;

use Easypage\Kernel\Abstractions\Controller;
use Easypage\Kernel\Authenticator;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;

class LoginController extends Controller
{
    public function index(): Response
    {
        if (Authenticator::isLoggedIn()) {
            return redirectTo('/');
        }

        return view('login');
    }

    public function login(Request $request): Response
    {
        if (!$request->wantsJSON()) {
            throw new \UnexpectedValueException('This method can only be called through API');
        }

        $is_authenticated = Authenticator::logIn(
            $request->post('username') ?? '',
            $request->post('password') ?? ''
        );

        if ($is_authenticated) {
            return jsonSuccess(['next_page' => redirectPath('/')]);
        } else {
            return jsonError('Username or password is not in accepted list');
        }
    }

    public function logout(Request $request): Response
    {
        if (!$request->wantsJSON()) {
            throw new \UnexpectedValueException('This method can only be called through API');
        }

        Authenticator::logOut();

        return jsonSuccess(['next_page' => redirectPath('/')]);
    }
}
