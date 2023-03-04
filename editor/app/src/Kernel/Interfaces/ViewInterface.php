<?php

namespace Easypage\Kernel\Interfaces;

interface ViewInterface
{
    /**
     * Enables or disable views cache
     *
     * @param  mixed $enabled
     * @return void
     */
    public function setCacheEnabled(bool $enabled): void;

    /**
     * Sets templates cache location 
     *
     * @param  mixed $path
     * @return void
     */
    public function setCachePath(string $path): void;

    /**
     * Renders template 
     *
     * @param  mixed $template_name
     * @param  mixed $data
     * @return string
     */
    public function render(string $template_name, array $data = []): string;

    /**
     * Sets templates location
     *
     * @param  mixed $path
     * @return void
     */
    public function setTemplatesPath(string $path): void;

    /**
     * Returns an instance of a View class 
     *
     * @return self
     */
    public static function getInstance(): self;
}
