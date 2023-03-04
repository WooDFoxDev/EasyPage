<?php

namespace Easypage\Controllers;

use Easypage\Kernel\Abstractions\Controller;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;
use Easypage\Models\ImageModel;
use Easypage\Models\PageModel;

class PageController extends Controller
{
    protected $model_class = PageModel::class;

    public function index(?array $args = []): Response
    {
        $pages = $this->model_class::findAll();

        $pages_array = array_map(fn ($page) => $page->export(), (array) $pages);
        usort($pages_array, fn ($a, $b) => $a['name'] <=> $b['name']);

        return view('pages', ['pages' => $pages_array]);
    }

    public function show(int|string $id): Response
    {
        // TODO: Support for string IDs
        // TODO: Changeable ID field
        if (is_string($id)) {
            $temp = intval($id);

            if ($temp != $id) {
                return view('404', ['404']);
            }

            $id = $temp;
        }

        $model = $this->model_class::findById($id);

        if ($model !== false) {
            $page = $model->export();

            $fonts_list = PageModel::FONTS_LIST;
            usort($fonts_list, fn ($a, $b) => $a <=> $b);

            // For selects (currently using datalists, so they can have just)
            // $fonts_list = array_map(fn ($var) => ['name' => $var, 'value' => str_replace(' ', '+', $var)], $fonts_list);

            $favicons = ImageModel::findByValue(['type' => 'favicon']);
            $favicons_list = array_map(function ($favicon) {
                $image_data = $favicon->export();
                $image_data['preview_name'] = $favicon->getPreviewPath();

                return $image_data;
            }, (array) $favicons);

            $backgrounds = ImageModel::findByValue(['type' => 'background']);
            $backgrounds_list = array_map(function ($background) {
                $image_data = $background->export();
                $image_data['preview_name'] = $background->getPreviewPath();

                return $image_data;
            }, (array) $backgrounds);

            return view('page', ['page' => $page, 'fonts_list' => $fonts_list, 'favicons_list' => $favicons_list, 'backgrounds_list' => $backgrounds_list]);
        } else {
            return view('404', ['404']);
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

            return jsonSuccess(['next_page' => redirectPath('/')]);
        } else {
            return jsonError('Error deleting page, please try again later');
        }
    }

    public function new(): Response
    {
        $fonts_list = PageModel::FONTS_LIST;
        $fonts_list = array_map(fn ($var) => ['name' => $var, 'value' => str_replace(' ', '+', $var)], $fonts_list);

        $favicons = ImageModel::findByValue(['type' => 'favicon']);
        $favicons_list = array_map(function ($favicon) {
            $image_data = $favicon->export();
            $image_data['preview_name'] = $favicon->getPreviewPath();

            return $image_data;
        }, (array) $favicons);

        $backgrounds = ImageModel::findByValue(['type' => 'background']);
        $backgrounds_list = array_map(function ($background) {
            $image_data = $background->export();
            $image_data['preview_name'] = $background->getPreviewPath();

            return $image_data;
        }, (array) $backgrounds);

        return view('page', ['fonts_list' => $fonts_list, 'favicons_list' => $favicons_list, 'backgrounds_list' => $backgrounds_list]);
    }

    public function add(Request $request): Response
    {
        if (!$request->wantsJSON()) {
            throw new \UnexpectedValueException('This method can only be called through API');
        }

        $model = new $this->model_class();
        $model->fromRequest($request->post());

        if ($model->save()) {
            return jsonSuccess(['next_page' => redirectPath('/page/' . $model->getId())]);
        } else {
            return jsonError('Please, fill the red fields correctly', ['validator' => $model->getValidationErrors()]);
        }
    }

    public function update(Request $request, int $id): Response
    {
        if (!$request->wantsJSON()) {
            throw new \UnexpectedValueException('This method can only be called through API');
        }

        $model = $this->model_class::findById($id);

        if ($model !== false) {
            $model->fromRequest($request->post());

            if ($model->save()) {
                if (!$request->post('export_on_save')) {
                    return jsonSuccess(['message' => 'Page saved successfully']);
                } else {
                    $exportController = new ExportController();

                    if (!$exportController->makeExport($model->export())) {
                        return jsonError('Page saved, but export failed');
                    }

                    return jsonSuccess(['message' => 'Page saved and exported successfully']);
                }
            } else {
                return jsonError('Please, fill the red fields correctly', ['validator' => $model->getValidationErrors()]);
            }
        } else {
            return jsonError('Cannot find selected page');
        }
    }
}
