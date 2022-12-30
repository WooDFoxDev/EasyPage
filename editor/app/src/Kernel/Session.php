<?php

namespace Easypage\Kernel;

/**
 * Session
 * Session handling
 */
class Session
{
    private static ?Session $instance = null;

    public function __construct()
    {
        session_start();
    }

    public function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function getAll(): array
    {
        return $_SESSION;
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function destroy(): void
    {
        session_unset();
        session_regenerate_id();
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
