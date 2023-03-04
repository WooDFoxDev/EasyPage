<?php

namespace Easypage\Kernel\Interfaces;

interface ViewInterface
{
    /**
     * Enables or disable views cache
     *
     * @param  bool $enabled
     * @return void
     */
    public function setCacheEnabled(bool $enabled): void;

    /**
     * Sets templates cache location 
     *
     * @param  string $path
     * @return void
     */
    public function setCachePath(string $path): void;

    /**
     * Renders template 
     *
     * @param  string $template_name
     * @param  array $data
     * @return string
     */
    public function render(string $template_name, array $data = []): string;

    /**
     * Sets templates location
     *
     * @param  string $path
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
