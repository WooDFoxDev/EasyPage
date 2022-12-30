<?php

namespace Easypage\Controllers;

use Easypage\Kernel\Abstractions\Controller;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;
use Easypage\Models\UserModel;

class PageController extends Controller
{
    public function index(?array $args = []): Response
    {
        return view('users', ['user' => $this->model_class::findAll()]);
    }

    public function show(int $id): Response
    {
        $model = $this->model_class::findById($id);

        if ($model !== false) {
            return view('user', ['user' => $model]);
        } else {
            return view('404', ['404']);
        }
    }

    public function delete(int $id): Response
    {
        $model = $this->model_class::findById($id);

        if ($model !== false) {
            $model->remove();
            return view('user_deleted', ['message' => 'User has been deleted']);
        } else {
            return view('404', ['404']);
        }
    }

    public function add(Request $request): Response
    {
        if (!$request->wantsJSON()) {
            throw new \UnexpectedValueException('This method can only be called through API');
        }
        dd($request);

        $args = [];

        $model = new UserModel();
        $model->create($args);

        if ($model->save()) {
            return view('user', ['user' => $model]);
        } else {
            return view('page_add', ['message' => 'Page cannot be added']);
        }

        // TODO: redirect 404;
    }
}
