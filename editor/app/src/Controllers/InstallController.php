<?php

namespace Easypage\Controllers;

use Easypage\Kernel\Abstractions\Controller;
use Easypage\Kernel\Authenticator;
use Easypage\Kernel\Core;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;
use Easypage\Kernel\Session;
use Easypage\Models\UserModel;

class InstallController extends Controller
{

    public function index(): Response
    {
        return view('install');
    }

    public function install(Request $request, Session $session): Response
    {
        if (!$request->wantsJSON()) {
            throw new \UnexpectedValueException('This method can only be called through API');
        }

        $model = new UserModel();
        $model->create($request->post());

        if ($model->save()) {
            $session->destroy();

            Authenticator::logIn(
                $request->post('username') ?? '',
                $request->post('password') ?? ''
            );

            $this->installFiles();

            // return jsonSuccess(['next_page' => '/?refresh=' . randomString()]);
            return jsonSuccess(['next_page' => redirectPath('/')]);
        } else {
            return jsonError('Please, fill the red fields correctly', ['validator' => $model->getValidationErrors()]);
        }
    }

    private function installFiles(): void
    {
        // Install default images
        if (!file_exists(ROOT_PATH . '/app/storage/images.json')) {
            copy(ROOT_PATH . '/app/install/storage/images.json', ROOT_PATH . '/app/storage/images.json');
            dirCopy(ROOT_PATH . '/app/install/uploads/images/', ROOT_PATH . '/app/uploads/images/');
        }

        // Install default page
        if (!file_exists(ROOT_PATH . '/app/storage/pages.json')) {
            copy(ROOT_PATH . '/app/install/storage/pages.json', ROOT_PATH . '/app/storage/pages.json');
            Core::getInstance()->call('ExportController@exportPage', ['id' => 1]);
        }
    }
}
