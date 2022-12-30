<?php

namespace Easypage\Kernel\Interfaces;

interface ViewInterface
{
    public function setCacheEnabled(bool $enabled): void;
    public function setCachePath(string $path): void;
    public function render(string $template_name, array $data = []): string;
    public function setTemplatesPath(string $path): void;
    public static function getInstance(): self;
}
