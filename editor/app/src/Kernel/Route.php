<?php

namespace Easypage\Kernel;

use Closure;

abstract class Route
{
    private static array $routes_get = [];
    private static array $routes_post = [];
    private static array $routes_patch = [];
    private static array $routes_delete = [];

    public static function get(string $route, string|array|Closure $callback, array $guards = []): void
    {
        static::addRoute('get', $route, $callback, $guards);
    }

    public static function post(string $route, string|array|Closure $callback, array $guards = []): void
    {
        static::addRoute('post', $route, $callback, $guards);
    }

    public static function patch(string $route, string|array|Closure $callback, array $guards = []): void
    {
        static::addRoute('patch', $route, $callback, $guards);
    }

    public static function delete(string $route, string|array|Closure $callback, array $guards = []): void
    {
        static::addRoute('delete', $route, $callback, $guards);
    }

    public static function crud(string $route, string $callback, array $guards = []): void
    {
        // Allow only 'something/{id}' or 'something_{id}' or 'something/{id}/subthing/{id}' patches
        // In short - path should have ending with ID variable
        if (!preg_match('/^.*\{.*\}$/u', $route)) {
            throw new \InvalidArgumentException('Wrong pattern for CRUD route autopopulation');
        }

        // Allow only callbacks without method selection
        // Methods are supposed to be
        // @show for get request
        // @add for post
        // @update for patch
        // @delete for delete
        if (strpos($route, '@') !== false) {
            throw new \InvalidArgumentException('Pass only class name callback for CRUD route autopopulation');
        }

        static::addRoute('get', $route, $callback . '@show', $guards);
        static::addRoute('patch', $route, $callback . '@update', $guards);
        static::addRoute('delete', $route, $callback . '@delete', $guards);

        // Replace last {id} with 'add'
        $route = substr($route, 0, -4) . 'add';
        static::addRoute('post', $route, $callback . '@add', $guards);
    }

    private static function addRoute(string $httpType, string $route, string|array|Closure $callback, $guards): void
    {
        if (!in_array($httpType, Request::HTTP_TYPES)) {
            throw new \InvalidArgumentException('Invalid HTTP request type');
        }

        static::${'routes_' . $httpType}[] = [
            'path' => $route,
            'path_segments' => self::preparePath($route),
            'callback' => $callback,
            'guards' => $guards
        ];
    }

    public static function find(string $path, string $httpType): array|false
    {
        $request_segments = self::preparePath($path);
        $request_segments_count = count($request_segments);

        // Variables found in path
        $request_variables = [];

        foreach (static::${'routes_' . $httpType} as $route) {
            // Check quantity of segments, skip on mismath
            // TODO: return false if count is MORE (need to order paths with their lengths)
            if ($request_segments_count != count($route['path_segments'])) {
                continue;
            }

            foreach ($route['path_segments'] as $segment_pos => $segment_mask) {
                if (strpos($segment_mask, '{') === false) {
                    // Precise matching
                    if ($request_segments[$segment_pos] != $segment_mask) {
                        continue (2);
                    }
                } else {
                    // Variable matching
                    preg_match('/^.*(\{.*\}).*$/u', $segment_mask, $pattern_source);
                    $pattern = str_replace($pattern_source[1], '(.{1,})', addslashes($pattern_source[0]));
                    $variable_name = str_replace(['{', '}'], '', $pattern_source[1]);

                    if (!preg_match('/^' . $pattern . '$/u', $request_segments[$segment_pos], $variable_value)) {
                        continue (2);
                    }

                    // Put found variable into array
                    $request_variables[$variable_name] = $variable_value[1];
                }
            }

            return [
                'callback' => $route['callback'],
                'guards' => $route['guards'],
                'properties' => $request_variables
            ];
        }

        return false;
    }

    public static function print(string $httpType = ''): array
    {
        if (!empty($httpType)) {
            if (!in_array($httpType, Request::HTTP_TYPES)) {
                throw new \InvalidArgumentException('Invalid HTTP request type');
            }

            return static::${'routes_' . $httpType};
        } else {
            return [
                'get' => static::$routes_get,
                'post' => static::$routes_post,
                'patch' => static::$routes_patch,
                'delete' => static::$routes_delete,
            ];
        }
    }

    private static function preparePath(string $path): array
    {
        $path = ltrim($path, '/');
        $path = rtrim($path, '/');
        $path_segments = explode('/', $path);

        return $path_segments;
    }
}
