<?php

namespace Easypage\Models;

use Easypage\Kernel\Abstractions\Model;

class ImageModel extends Model
{
    protected static string $repository = 'images';
    protected string $location = 'app/uploads/images';
    protected array $persistent = ['name', 'file_name', 'type'];
    protected array $updateable = ['name', 'file_name', 'type'];

    const IMAGE_TYPES = ['background', 'favicon'];
    const IMAGE_EXTENSIONS = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'ico' => 'image/vnd.microsoft.icon',
    ];

    protected string $name;
    protected string $file_name;
    protected string $type;
    protected ?string $uploaded = null;

    public function save(): bool
    {
        if (!is_null($this->uploaded)) {
            if (!$this->isValid()) {
                return false;
            }

            if (!checkPath(ROOT_PATH . '/' . $this->location)) {
                return false;
            }

            if (!move_uploaded_file($this->uploaded, $this->getImagePath())) {
                return false;
            }
        }

        return parent::save();
    }

    public function remove(): bool
    {
        if (!is_null($this->getId())) {
            unlink($this->getImagePath());

            $preview_path = $this->getImagePath($this->getPreviewPath(false));
            if (file_exists($preview_path)) {
                unlink($preview_path);
            }
        }

        return parent::remove();
    }

    public function getImagePath(?string $file_name = null): string
    {
        return ROOT_PATH . '/' . $this->location . '/' . ($file_name ?? $this->file_name);
    }

    public function getPreviewPath($create_if_missing = true): string
    {
        if (is_null($this->getId())) {
            return $this->file_name;
        }

        $preview_name = 'prev_' . $this->file_name;
        $preview_path = $this->getImagePath($preview_name);

        if ($create_if_missing && !file_exists($preview_path)) {
            $this->createPreview($preview_path);
        }

        return $preview_name;
    }

    private function createPreview(string $preview): void
    {
        $width = 350;
        $height = 200;
        $quality = 90;

        if ($this->type == 'favicon') {
            $width = 150;
            $height = 150;
        }

        $imagick = new \Imagick(realpath($this->getImagePath()));
        $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality($quality);
        $imagick->thumbnailImage($width, $height, false, false);

        if (file_put_contents($preview, $imagick) === false) {
            throw new \Exception("Could not put contents.");
        }
    }

    public function getImageExtension(?string $file_name = null): string
    {
        $file_extension = pathinfo(($file_name ?? $this->file_name), PATHINFO_EXTENSION);

        return $file_extension;
    }

    public function fromUpload(array $files, array $args): bool
    {
        if (empty($files['image']) || $files['image']['error']) {
            return false;
        }

        $this->uploaded = $files['image']['tmp_name'];

        $args['name'] = cleanString($files['image']['name']);
        $args['file_name'] = $this->createFileName($files['image']['name']);

        $this->fromRequest($args);

        return true;
    }

    private function createFileName(string $original_name): string
    {
        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);

        do {
            $file_name = substr(md5(random_bytes(32)), 0, 12) . '.' . $file_extension;
        } while (file_exists($this->getImagePath($file_name)));

        return $file_name;
    }

    protected function validate(): bool
    {
        $this->_is_valid = true;
        $this->_validator_messages = [];

        $this->validateProperty('name', 'hasPresence', invalidMessage: "File name cannot be blank");
        $this->validateProperty('file_name', 'hasPresence', invalidMessage: "File name cannot be blank");
        $this->validateProperty('type', 'hasPresence', invalidMessage: "Image type cannot be blank");

        if (!is_null($this->uploaded)) {
            $this->validateImage();
        }

        return $this->_is_valid;
    }

    private function validateImage(): bool
    {
        if (function_exists('mime_content_type')) {

            $file_extension = pathinfo($this->file_name, PATHINFO_EXTENSION);
            // TODO: Remove error suppressing
            // The mime_content_type function gives a warning, though it works well
            $file_mime = @mime_content_type($this->uploaded);

            if (!in_array($file_mime, static::IMAGE_EXTENSIONS)) {
                return false;
            }

            if (!in_array($file_extension, array_keys(static::IMAGE_EXTENSIONS))) {
                return false;
            }

            if ($file_extension != array_search($file_mime, static::IMAGE_EXTENSIONS)) {
                return false;
            }
        }

        if ($this->type == 'background') {
            return $this->validateImageBackground();
        } else if ($this->type == 'background') {
            return $this->validateImageFavicon();
        }

        return false;
    }

    private function validateImageBackground(): bool
    {
        return $this->validateImageMinDimensions(800, 800);
    }

    private function validateImageFavicon(): bool
    {
        return $this->validateImageMinDimensions(24, 24);
    }

    private function validateImageMinDimensions(int $width, int $height): bool
    {
        $image_dimensions = getimagesize($this->uploaded);

        if (!$image_dimensions) {
            return false;
        }

        if ($image_dimensions[0] < $width) {
            return false;
        }

        if ($image_dimensions[1] < $height) {
            return false;
        }

        return true;
    }
}
