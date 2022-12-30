<?php

namespace Easypage\Kernel;

class Response
{
    private int $http_code = 200;
    private array $headers = [];
    private string $body = '';
    private array $body_json;

    public function addHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    public function addHeaders(array $headers): void
    {
        foreach ($headers as $header) {
            $this->addHeader($header[0], $header[1]);
        }
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function setBodyJSON(array $data): void
    {
        $this->body_json = $data;
    }

    public function appendBodyJson(array $data): void
    {
        if (is_null($this->body_json)) {
            $this->body_json = [];
        }

        $this->body_json = array_merge($this->body_json, $data);
    }

    public function setHttpCode(int $http_code): void
    {
        $this->http_code = $http_code;
    }

    public function getHttpCode(): int
    {
        return $this->http_code;
    }

    public function isJson(): bool
    {
        return !is_null($this->body_json);
    }

    public function send(): void
    {
        // Set http response code
        if ($this->http_code !== 200) {
            http_response_code($this->http_code);
        }

        // Prepare body
        if (isset($this->body_json)) {
            $this->body = json_encode($this->body_json);
            $this->addHeader('Content-Type', 'application/json');
        }

        // Send headers
        foreach ($this->headers as $header => $value) {
            header($header . ': ' . $value);
        }

        if (isDebug()) {
            header('Time-Consumed: ' . (microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]));
        }

        // Sebd body
        echo $this->body;
    }
}
