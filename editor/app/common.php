<?php

use Easypage\Kernel\Core;
use Easypage\Kernel\Response;

// REQUEST HELPERS
// View helper
function view(string $template, array $data = []): Response
{
    $response = new Response();
    $response->setBody(Core::getView()->render($template, $data));

    return $response;
}

function json(array $data): Response
{
    $response = new Response();
    $response->setBodyJSON($data);

    return $response;
}

function jsonSuccess(array $data = []): Response
{
    return json(
        array_merge(
            [
                'status' => 'ok',
            ],
            $data
        )
    );
}

function jsonError(?string $message = null, array $data = []): Response
{
    return json(
        array_merge(
            [
                'status' => 'error',
                'message' => $message ?? 'Undefined error',
            ],
            $data
        )
    );
}

// Request abortion shortcut
function abort(?string $template = 'error', ?int $http_code = 404, ?string $path = null, ?string $message = 'Something went wrong'): Response
{
    if ($path) {
        return redirectTo($path, $http_code);
    }

    $request = Core::getInstance()->getRequest();

    if (!$request->wantsJSON()) {
        if ($http_code == 404) {
            $template = '404';
        }

        $response = view($template);
    } else {
        $response = jsonError($message);
    }

    $response->setHttpCode($http_code);

    return $response;
}

// Redirect shortcut
function redirectTo(string $path, ?int $http_code = 302): Response
{
    $response = new Response();
    $response->setHttpCode($http_code);
    $response->addHeader('Location', redirectPath($path));

    return $response;
}

function redirectPath(string $path): string
{
    if (substr($path, 0, 1) == '/') {
        return $_ENV['APP_LOCATION'] . $path;
    }

    return $path;
}


// SYSTEM HELPERS
// Check path and create folders
function checkPath(string $path): bool
{
    if (!file_exists($path)) {
        if (!mkdir($path, recursive: true)) {
            return false;
        }
    }

    return true;
}

// Copy directory with contents
function dirCopy($source_path, $destination_path): bool
{
    if (!checkPath($destination_path)) {
        return false;
    }

    $listing = scandir($source_path);

    foreach ($listing as $file) {
        if ($file == '.' || $file == '..') continue;
        if (!is_readable($source_path . '/' . $file)) continue;

        if (is_dir($source_path . '/' . $file)) {
            checkPath($destination_path . '/' . $file);

            if (!dirCopy($source_path . '/' . $file, $destination_path . '/' . $file)) {
                return false;
            }
        } else {
            copy($source_path . '/' . $file, $destination_path . '/' . $file);
        }
    }

    return true;
}

// Random string
function randomString(int $length = 6): string
{
    return substr(md5(random_bytes(12)), 0, $length);
}

// DATA TRANSFORM
function HexToRGB(string $hex): array
{
    switch (strlen($hex)) {
        case 3:
            return sscanf($hex, "%1x%1x%1x");
        case 6:
            return sscanf($hex, "%2x%2x%2x");
    }

    throw new \InvalidArgumentException('Wrong hex value');
}

function HexColorToRGB(string $hex_color): string
{
    $rgb_array = HexToRGB(str_replace('#', '', $hex_color));

    return implode(', ', $rgb_array);
}


// FORM HELPERS
// CSRF field helper
function csrfField()
{
    $token = csrfToken();

    return '<input type="hidden" name="_token" value="' . $token . '">';
}
function csrfToken()
{
    $session = CORE::getInstance()->getSession();

    if (is_null($session->get('_token'))) {
        $session->set('_token', bin2hex(random_bytes(35)));
    }

    return $session->get('_token');
}

// Form method override
function formMethod(string $method)
{
    return '<input type="hidden" name="_method" value="' . $method . '">';
}

// Data saitize
function cleanString(string $string): string
{
    return trim(addslashes($string));
}

// VALIDATION HELPERS
// Empty string checker
function isBlank(string $value): bool
{
    return !isset($value) || trim($value) === '';
}

// String has content
function hasPresence(string $value): bool
{
    return !isBlank($value);
}

// Equal strings
function isEqual(string $a, string $b): bool
{
    return $a == $b;
}

// Not equal strings
function isNotEqual(string $a, string $b): bool
{
    return !isEqual($a, $b);
}

// String length GREATER
function hasLengthGreaterThan(string $value, int $min): bool
{
    $length = strlen($value);
    return $length > $min;
}

// String length LESS
function hasLengthLessThan(string $value, int $max): bool
{
    $length = strlen($value);
    return $length < $max;
}

// String length EXACTLY
function hasLengthExactly(string $value, int $exact): bool
{
    $length = strlen($value);
    return $length == $exact;
}

// String has length
// hasLength('abcd', ['min' => 3, 'max' => 5])
// hasLength('abcd', ['exact' => 3])
function hasLength(string $value, ?int $min = null, ?int $max = null, ?int $exactly = null): bool
{
    if (!is_null($min) && !hasLengthGreaterThan($value, $min - 1)) {
        return false;
    } elseif (isset($max) && !hasLengthLessThan($value, $max + 1)) {
        return false;
    } elseif (isset($exactly) && !hasLengthExactly($value, $exactly)) {
        return false;
    } else {
        return true;
    }
}

// $value is in array (in_array shortcut)
function hasInclusionOf(int|string|bool $value, array $set): bool
{
    return in_array($value, $set);
}

// $value is not in array
function hasExclusionOf(int|string|bool $value, array $set): bool
{
    return !in_array($value, $set);
}

// String has substring inclusion
function hasString(string $value, string $required_string): bool
{
    return strpos($value, $required_string) !== false;
}

// Array structure
function hasArraysOf(array $array, array $scheme): bool
{
    foreach ($array as $item) {
        if (!is_array($item)) {
            return false;
        }

        foreach (array_keys($item) as $key) {
            if (!in_array($key, $scheme)) {
                return false;
            }
        }
    }

    return true;
}

// Email checker
function hasValidEmailFormat(string $value): bool
{
    $email_regex = '/\A[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\Z/i';
    return preg_match($email_regex, $value) === 1;
}

// Password checker
function hasValidPassword(string $value): bool
{
    return (passwordWeakness($value)) ? false : true;
}

// Password validation
function passwordWeakness(string $value): array|false
{
    $errors = null;

    if (!hasLength($value, min: 12)) {
        $errors[] = "Password must contain 12 or more characters";
    } else if (!preg_match('/[A-Z]/', $value)) {
        $errors[] = "Password must contain at least 1 uppercase letter";
    } else if (!preg_match('/[a-z]/', $value)) {
        $errors[] = "Password must contain at least 1 lowercase letter";
    } else if (!preg_match('/[0-9]/', $value)) {
        $errors[] = "Password must contain at least 1 number";
    } else if (!preg_match('/[^A-Za-z0-9\s]/', $value)) {
        $errors[] = "Password must contain at least 1 symbol";
    }

    return $errors ?? false;
}


// DUMP AND DEBUG HELPERS
// Print well-formatted debug message
function d($message)
{
    if (!isDebug()) {
        return false;
    }

    echo '<pre>';
    var_dump($message);
    echo '</pre>';
}

// Print debug message and die
function dd($message)
{
    if (!isDebug()) {
        return false;
    }

    d($message);
    exit();
}

// Is app in debug mode
function isDebug(): bool
{
    if (!isset($_ENV['APP_ENV']) || !in_array(strtolower($_ENV['APP_ENV']), ['dev', 'development'])) {
        return false;
    }

    return true;
}
