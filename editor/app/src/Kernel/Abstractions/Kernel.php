<?php

namespace Easypage\Kernel\Abstractions;

use Closure;
use Easypage\Kernel\Abstractions\Storage;
use Easypage\Kernel\Request;
use Easypage\Kernel\Response;
use Easypage\Kernel\Session;
use Easypage\Kernel\Storage\FileStorage;
use Easypage\Kernel\Storage\MemoryStorage;
use Easypage\Kernel\Abstractions\View;
use Easypage\Kernel\View\EPView;

/**
 * Kernel
 */
abstract class Kernel
{
    protected static ?Kernel $instance = null;
    protected Session $session;
    protected Storage $storage;
    protected View $view;
    protected Request $request;

    protected array $middleware = [];
    // method separator of a class. when pass class and method as string
    const METHOD_SEPARATOR = '@';
    // default method name
    const METHOD_DEFAULT = 'index';
    // namespace  for  class. when pass class and method as string
    const NAMESPACE = "Easypage\\";

    protected array $middleware_api = [];
    protected array $middleware_web = [];
    protected array $middleware_global = [];

    private function __construct(?Session $session = null, ?Storage $storage = null, ?View $view = null, ?Request $request = null)
    {
        if (!isset($_ENV['APP_ENV']) || empty($_ENV['APP_ENV'])) {
            throw new \RuntimeException('You should load environment variables before instantiating Core controller');
        }

        // Setup PHP
        self::setupPHP($_ENV['APP_ENV'] ?? 'production');

        // Setup global dependencies
        // Session
        $this->session = $session ?? Session::getInstance();

        // Storage
        if (!is_null($storage)) {
            $this->storage = $storage;
        } else {
            if ($_ENV['APP_ENV'] != 'testing') {
                $this->storage = new FileStorage;
                $this->storage->setStorage($_ENV['STORAGE_PATH']);
            } else {
                $this->storage = new MemoryStorage;
            }
        }

        // View
        $this->view = $view ?? EPView::getInstance();
        if (!is_null($view)) {
            $this->view = $view;
        } else {
            $this->view = new EPView;
            $this->view->setTemplatesPath($_ENV['VIEW_PATH']);
            $this->view->setCachePath($_ENV['CACHE_PATH']);
            $this->view->setCacheEnabled($_ENV['VIEW_CACHE']);
        }

        // Request
        $this->request = $request ?? (new Request)->captureRequest();

        // Load routes and configure app
        // relatively to request type
        if (!$this->request->wantsJSON()) {
            require(ROOT_PATH . '/app/routes/web.php');
            array_push($this->middleware, ...array_reverse($this->middleware_web));
        } else {
            require(ROOT_PATH . '/app/routes/api.php');
            array_push($this->middleware, ...array_reverse($this->middleware_api));
        }
        array_push($this->middleware, ...array_reverse($this->middleware_global));
    }

    static public function createInstance(?Session $session = null, ?Storage $storage = null, ?View $view = null, ?Request $request = null): Kernel
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($session, $storage, $view, $request);
        }

        return static::getInstance();
    }

    static public function getInstance(): Kernel
    {
        return static::$instance;
    }

    public static function getStorage(): ?Storage
    {
        return static::$instance->storage ?? null;
    }

    public static function getSession(): ?Session
    {
        return static::$instance->session ?? null;
    }

    public static function getView(): ?View
    {
        return static::$instance->view ?? null;
    }

    public static function getRequest(): ?Request
    {
        return static::$instance->request ?? null;
    }

    static private function setupPHP(string $env = 'production'): void
    {
        $env = strtolower($env);

        if ('dev' != $env) {
            // Production mode
            ini_set('display_errors', 0);
        } else {
            // Development mode
            ini_set('display_errors', 1);
        }
    }

    public function process(): Response
    {
        $process = function ($request) {
            return $this->call($request->route()['callback'], $request->route()['properties']);
        };

        foreach ($this->middleware as $middleware) {
            $callable = new $middleware;

            $process = fn ($request) => $callable($request, $process);
        }

        return $process($this->request);
    }

    public function make(string $class, array $parameters = []): object
    {

        list($callbackClass) = $this->resolveCallback($class);

        $classReflection = new \ReflectionClass($callbackClass);
        $constructorParams = $classReflection->getConstructor() ? $classReflection->getConstructor()->getParameters() : [];

        $dependencies = $this->resolveDependencies($constructorParams);

        return $classReflection->newInstance(...$dependencies);
    }

    public function call(string|array|Closure $callable, array $parameters = [])
    {
        if (is_a($callable, 'Closure')) {
            $functionReflection = new \ReflectionFunction($callable);
            $functionParams = $functionReflection->getParameters();

            $dependencies = $this->resolveDependencies($functionParams, $parameters);

            return $callable(...$dependencies);
        } else {
            list($callbackClass, $callbackMethod) = $this->resolveCallback($callable);

            if (!is_object($callbackClass)) {
                $initClass = $this->make($callbackClass, $parameters);
            } else {
                $initClass = $callbackClass;
            }

            $methodReflection = new \ReflectionMethod($callbackClass, $callbackMethod);
            $methodParams = $methodReflection->getParameters();

            $dependencies = $this->resolveDependencies($methodParams, $parameters);

            return $methodReflection->invoke($initClass, ...$dependencies);
        }
    }

    private function resolveCallback(string|array $callable): array
    {
        if (is_string($callable)) {
            $segments = explode(self::METHOD_SEPARATOR, $callable);

            $callbackClass = $this->addNamespace($segments[0]);
            $callbackMethod = !empty($segments[1]) ? $segments[1] : self::METHOD_DEFAULT;
        } else if (is_array($callable)) {
            if (is_object($callable[0])) {
                $callbackClass = $callable[0];
            } else if (is_string($callable[0])) {
                $callbackClass = $this->addNamespace($callable[0]);
            }

            $callbackMethod = !empty($callable[1]) ? $callable[1] : self::METHOD_DEFAULT;
        }

        return [$callbackClass, $callbackMethod];
    }

    private function addNamespace(string $callable): string
    {
        if (strpos($callable, self::NAMESPACE) !== 0) {
            if (strpos($callable, 'Controller') !== false) {
                $callable = 'Controllers\\' . $callable;
            } else if (strpos($callable, 'Model') !== false) {
                $callable = 'Models\\' . $callable;
            }

            $callable = self::NAMESPACE . $callable;
        }

        return $callable;
    }

    public function resolveDependencies(array $params, array $parameters = []): array
    {
        $dependencies = [];

        foreach ($params as $param) {

            $type = $param->getType();

            if ($type && $type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $dependency_class = new \ReflectionClass($type->getName());

                if (
                    isset($this->{$param->getName()})
                    && $this->{$param->getName()} instanceof ((string) $dependency_class->getName())
                ) {
                    // Include existing dependency
                    array_push($dependencies, $this->{$param->getName()});
                } else {
                    // Instanciate new dependency
                    array_push($dependencies, $dependency_class->newInstance());
                }
            } else {
                $name = $param->getName();

                if (!empty($parameters) && array_key_exists($name, $parameters)) {

                    array_push($dependencies, $parameters[$name]);
                } else {

                    if (!$param->isOptional()) {
                        throw new \Exception("Cannot resolve parameters");
                    }
                }
            }
        }

        return $dependencies;
    }
}
