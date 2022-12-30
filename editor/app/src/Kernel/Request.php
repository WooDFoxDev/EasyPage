<?php

namespace Easypage\Kernel;

class Request
{
    const HTTP_TYPES = ['get', 'post', 'patch', 'delete'];
    private ?string $method = null;
    private ?array $headers = null;
    private array $accepts = [];
    private ?array $get = null;
    private ?array $post = null;
    private ?array $files = null;
    private ?string $body = null;
    private $route = [];
    private bool $body_can_fetch = false;
    private bool $body_fetched = false;

    public function captureRequest(): Request
    {
        $this->get = $_GET ?? null;
        $this->post = $_POST ?? null;
        $this->files = $_FILES ?? null;

        $this->method = $this->getRequestMethod();
        $this->accepts = explode(',', strtolower($_SERVER['HTTP_ACCEPT'] ?? ''));

        $this->body_can_fetch = true;

        // Since HTTP headers are case insensetive
        $this->headers = (function_exists('getallheaders')) ? array_change_key_case(getallheaders(), CASE_LOWER) : [];

        return $this;
    }

    public function route()
    {
        if (empty($this->route)) {
            $this->route = Route::find($this->get($_ENV['APP_REWRITE']) ?? '/', $this->method());
        }

        return $this->route;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function accepts(): array
    {
        return $this->accepts;
    }

    public function get(?string $key = null): int|string|array|bool|null
    {
        if (!$key) {
            return $this->get;
        } else {
            if (!array_key_exists($key, $this->get)) {
                return null;
            }

            return $this->get[$key];
        }
    }

    public function post(?string $key = null): int|string|array|bool|null
    {
        if (!$key) {
            return $this->post;
        } else {
            if (!array_key_exists($key, $this->post)) {
                return null;
            }

            return $this->post[$key];
        }
    }

    public function files(): array|null
    {
        if (empty($this->files)) {
            return null;
        }

        return $this->files;
    }

    public function header(string $key): int|string|array|bool|null
    {
        // Since HTTP headers are case insensetive
        $key = strtolower($key);

        if (!array_key_exists($key, $this->headers)) {
            return null;
        }

        return $this->headers[$key];
    }

    public function setGet(array $data)
    {
        foreach ($data as $key => $value) {
            $this->get[$key] = $value;
        }
    }

    public function setPost(array $data)
    {
        foreach ($data as $key => $value) {
            $this->post[$key] = $value;
        }
    }

    public function hasFiles(): bool
    {
        return !empty($this->files());
    }

    public function wantsJSON(): bool
    {
        return in_array('application/json', $this->accepts());
    }

    public function setBody(string $content): void
    {
        $this->body = $content;
    }

    public function body(): string
    {
        if ($this->body_can_fetch && !$this->body_fetched) {
            $this->fetchRequestBody();
        }

        return $this->body;
    }

    private function getRequestMethod(): string
    {
        $method = strtolower($_SERVER['REQUEST_METHOD'] ?? 'get');

        $override = $this->post('_method');
        if (!is_null($override)) {
            $override = strtolower($override);

            if (in_array($override, self::HTTP_TYPES)) {
                $method = $override;
            }
        }

        return $method;
    }

    private function fetchRequestBody(): void
    {
        $this->body = file_get_contents('php://input');
    }
}
