<?php

namespace Easypage\Controllers;

use Easypage\Kernel\Abstractions\Controller;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;
use Easypage\Models\ImageModel;

class ImageController extends Controller
{
    protected $model_class = ImageModel::class;

    public function index(): Response
    {
        $favicons = $this->model_class::findByValue(['type' => 'favicon']);
        $backgrounds = $this->model_class::findByValue(['type' => 'background']);

        $favicons = array_map(function ($favicon) {
            $image_data = $favicon->export();
            $image_data['preview_name'] = $favicon->getPreviewPath();

            return $image_data;
        }, (array) $favicons);
        $backgrounds = array_map(function ($background) {
            $image_data = $background->export();
            $image_data['preview_name'] = $background->getPreviewPath();

            return $image_data;
        }, (array) $backgrounds);

        return view('images', ['favicons' => $favicons, 'backgrounds' => $backgrounds]);
    }

    public function upload(Request $request): Response
    {
        if (!$request->wantsJSON()) {
            throw new \UnexpectedValueException('This method can only be called through API');
        }

        $model = new $this->model_class();
        $model->fromUpload($request->files(), $request->post());

        if ($model->save()) {
            return jsonSuccess(['next_page' => redirectPath('/media')]);
        } else {
            return jsonError('Please, fill the red fields correctly', ['validator' => $model->getValidationErrors()]);
        }
    }

    public function delete(Request $request, int $id): Response
    {
        if (!$request->wantsJSON()) {
            throw new \UnexpectedValueException('This method can only be called through API');
        }

        $model = $this->model_class::findById($id);

        if ($model !== false) {
            $model->remove();

            return jsonSuccess(['next_page' => redirectPath('/media')]);
        } else {
            return jsonError('Error deleting page, please try again later');
        }
    }
}
