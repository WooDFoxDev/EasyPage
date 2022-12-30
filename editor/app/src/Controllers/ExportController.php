<?php

namespace Easypage\Controllers;

use Easypage\Kernel\Abstractions\Controller;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;
use Easypage\Kernel\View\EPView;
use Easypage\Models\ImageModel;
use Easypage\Models\PageModel;

class ExportController extends Controller
{
    public function index(): Response
    {
        return abort(message: 'Method unavailable');
    }

    public function exportPage(int $id, Request $request): Response
    {
        if (!$request->wantsJSON()) {
            throw new \UnexpectedValueException('This method can only be called through API');
        }

        $model = PageModel::findById($id);

        if ($model == false || !$page = $model->export()) {
            return jsonError('Error exporting page, please try again later');
        }

        if (!$this->makeExport($page)) {
            return jsonError('Error exporting page, please try again later');
        }

        return jsonSuccess(['message' => 'Page exported successfully']);
    }

    public function exportArchive(int $id, Request $request): Response
    {
        if (!$request->wantsJSON()) {
            throw new \UnexpectedValueException('This method can only be called through API');
        }

        $model = PageModel::findById($id);

        if ($model == false || !$page = $model->export()) {
            return jsonError('Error exporting page, please try again later');
        }

        if (!$download_path = $this->makeExportArchive($page)) {
            return jsonError('Error exporting page, please try again later');
        }

        return jsonSuccess([
            'message' => 'Page exported successfully',
            'download' => redirectPath($download_path)
        ]);
    }

    public function makeExport(array $page): bool
    {
        $bundle_path = $this->buildBundle($page);

        $this->moveBundle($bundle_path, ROOT_PATH . '/..');

        return true;
    }

    private function makeExportArchive(array $page): string|bool
    {
        $link_path = '/app/uploads/exports';
        $export_path = ROOT_PATH . $link_path;
        checkPath($export_path);

        $bundle_path = $this->buildBundle($page);

        $archive_name = 'export_' . $page['id'] . '.zip';
        $archive_path = $export_path . '/' . $archive_name;

        if (!$this->archiveBundle($bundle_path, $archive_path)) {
            return false;
        }

        return $link_path . '/' . $archive_name;
    }

    private function moveBundle(string $bundle_path, string $target_path): bool
    {
        dirCopy($bundle_path, $target_path);

        return true;
    }

    private function archiveBundle(string $bundle_path, string $target_name): bool|string
    {

        if (file_exists($target_name)) {
            unlink($target_name);
        }

        $zip = new \ZipArchive();

        if ($zip->open($target_name, \ZipArchive::CREATE) !== true) {
            return false;
        }

        if (!$this->recursiveAddToArchive($zip, $bundle_path)) {
            return false;
        }

        $zip->close();

        return true;
    }

    private function recursiveAddToArchive(\ZipArchive $zip, string $path, string $relative = ''): bool
    {
        $listing = scandir($path);

        foreach ($listing as $file) {
            if ($file == '.' || $file == '..') continue;
            if (!is_readable($path . '/' . $file)) continue;

            if (is_dir($path . '/' . $file)) {
                if (!$this->recursiveAddToArchive($zip, $path . '/' . $file, $relative . $file . '/')) {
                    return false;
                }
            } else {
                $zip->addFile($path . '/' . $file, $relative . $file);
            }
        }

        return true;
    }

    private function buildBundle(array $page): string
    {
        $bundle_path = ROOT_PATH . $_ENV['CACHE_PATH'] . '/bundles/' . $page['id'];

        if (!file_exists($bundle_path)) {
            mkdir($bundle_path, recursive: true);
        }

        $this->buildPage($page, $bundle_path);

        return $bundle_path;
    }

    private function buildPage(array $page, string $path): void
    {
        $this->prepareFolders($path);

        $random = randomString();

        if (!empty($page['image_favicon']) && $image = ImageModel::findById($page['image_favicon'])) {
            $page['favicon_image'] = 'favicon.' . $image->getImageExtension();
            copy($image->getImagePath(), $path . '/assets/img/' . $page['favicon_image']);
        } else {
            $page['favicon_image'] = 'favicon.png';
            copy(ROOT_PATH . '/assets/img/favicon.png', $path . '/assets/img/favicon.png');
        }

        if (!empty($page['image_background']) && $image = ImageModel::findById($page['image_background'])) {
            $page['background_image'] = 'bg.' . $image->getImageExtension();
            copy($image->getImagePath(), $path . '/assets/img/' . $page['background_image']);
        }

        $view = new EPView();
        $view->setCacheEnabled(false);
        $view->setTemplatesPath('app/exports');
        $view->setCachePath($_ENV['CACHE_PATH'] . '/export');

        $html = $view->render('stub_default', ['page' => $page, 'random' => $random]);

        $view->setTemplatesExtension('css');
        $style = $view->render('stub_default', ['page' => $page, 'random' => $random]);

        $this->saveFile($path . '/index.html', $html);
        $this->saveFile($path . '/assets/css/style.css', $style);
    }

    private function prepareFolders(string $root_path = ROOT_PATH . '/..'): void
    {
        $folders = [
            '/assets/css',
            '/assets/img',
        ];

        foreach ($folders as $folder) {
            $path = $root_path . $folder;

            if (!file_exists($path)) {
                if (!checkPath($path)) {
                    throw new \RuntimeException('Cannot create folder ' . $path);
                }
            }
        }
    }

    private function saveFile(string $path, string &$content): void
    {
        if (!file_put_contents($path, $content)) {
            throw new \RuntimeException('Cannot create file ' . $path);
        }
    }
}
