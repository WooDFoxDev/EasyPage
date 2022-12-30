<?php

namespace Easypage\Kernel;

/**
 * DotEnv
 */
class DotEnv
{
    static public function load($path): void
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException('File not found');
        }

        if (!is_readable($path)) {
            throw new \InvalidArgumentException('Cannot read file');
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (substr($line, 0, 1) == '#') {
                // Skip comments
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = self::cleanQuotes(trim($value));

            if (!array_key_exists($name, $_ENV) && !array_key_exists($name, $_SERVER)) {
                self::convertTypes($value);

                $_ENV[$name] = $value;
            }
        }
    }

    static private function convertTypes(&$value): void
    {
        switch ($value) {
            case 'true':
                $value = (bool) true;
                break;
            case 'false':
                $value = (bool) false;
                break;
            case 'null':
                $value = null;
                break;
        }
    }

    static private function cleanQuotes(string $str): string
    {
        if (mb_strlen($str) > 1) {
            switch (substr($str, 0, 1)) {
                case "'":
                case '"':
                    $str = substr($str, 1, -1);
                    break;
                default:
                    break;
            }
        }

        return $str;
    }
}
