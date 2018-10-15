<?php

namespace K;

use function K\{option};

define('E_FATAL', E_ERROR | E_PARSE | E_CORE_ERROR
        | E_COMPILE_ERROR | E_USER_ERROR);
define('NOT_FOUND', 404);
define('SERVER_ERROR', 500);
define('ENV_PRODUCTION', 'production');
define('ENV_STAGING', 'staging');
define('ENV_DEVELOPMENT', 'development');

class App
{
    /**
     * @param array $error_handlers [error code] => function (\Throwable $e) {}
     */
    private static $error_handlers = [];

    /**
     * @param array $routes
     */
    private $routes = [];

    public static function run()
    {
        option('env', ENV_PRODUCTION);
        option('root_dir', dirname(__DIR__));
        option('views_dir', option('root_dir') . '/views');

        set_error_handler([__CLASS__, 'errorException'], E_ALL);
        set_exception_handler([__CLASS__, 'errorDispatcher']);
        register_shutdown_function([__CLASS__, 'handleFatal']);

        $app = new static();
        $app->registerErrorHandler(SERVER_ERROR, [$app, 'defaultErrorHandler']);
        $app->configure();

        if ($session = option('session')) {
            $app->startSession($session);
        }

        $app->initialize();

        $dispatcher = \FastRoute\simpleDispatcher(
            function (\FastRoute\RouteCollector $r) use ($app) {
                foreach ($app->routes as $route) {
                    $r->addRoute(
                        $route['method'],
                        $route['path'],
                        $route
                    );
                }
            }
        );

        $uri = $_SERVER['REQUEST_URI'];
        if (false !== ($pos = strpos($uri, '?'))) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $route_info = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $uri);

        $w = new \K\Response();
        if ($route_info[0] == \FastRoute\Dispatcher::NOT_FOUND) {
            $app->handleNotFound($w);
        } elseif ($route_info[0] == \FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            $app->handleNotAllowed($w);
        } else {
            $request = new \K\Request();
            $route = $route_info[1];
            $app->before($route, $w, $request);
            call_user_func(
                [$route_info[1]['page'], 'handleRequest'],
                $w,
                $request,
                $route_info[2]
            );
        }
        $w->send();
    }

    protected function before($route, $response, $request) { }
    protected function configure() { }
    protected function initialize() {  }
    protected function handleServerError(\K\ResponseWriterInterface $w, \Throwable $e) { }
    protected function handleNotFound(\K\ResponseWriterInterface $w) { }
    protected function handleNotAllowed(\K\ResponseWriterInterface $w) { }

    protected function dispatch(string $path, string $page, array $options = [])
    {
        $this->route('GET', $path, $page, $options);
    }

    protected function dispatchPost(string $path, string $page, array $options = [])
    {
        $this->route('POST', $path, $page, $options);
    }

    protected function dispatchOptions(string $path, string $page, array $options = [])
    {
        $this->route('OPTIONS', $path, $page, $options);
    }

    protected function registerErrorHandler(int $err_code, callable $handler)
    {
        self::$error_handlers[$err_code] = $handler;
    }

    private function route(string $method, string $path, string $page, array $options = [])
    {
        $this->routes[] = [
            'method'  => $method,
            'path'    => $path,
            'page'    => $page,
            'options' => $options,
        ];
    }

    private function defaultErrorHandler(\Throwable $e)
    {
        $w = new \K\Response();
        $w->withStatus(500);

        $this->handleServerError($w, $e);
        $w->send();
    }

    private function startSession(array $session)
    {
        if (isset($_COOKIE[$session['name']])) {
            setcookie(
                $session['name'],
                $_COOKIE[$session['name']],
                time() + $session['lifetime'],
                $session['path'],
                $session['domain'],
                $session['secure'],
                $session['httponly']
            );
        }
        session_start([
            'name'            => $session['name'],
            'cookie_lifetime' => $session['lifetime'],
            'cookie_path'     => $session['path'],
            'cookie_domain'   => $session['domain'],
            'cookie_secure'   => $session['secure'],
            'cookie_httponly' => $session['httponly'],
            'cache_limiter'   => '',
            'gc_maxlifetime'  => $session['lifetime'] * 3,
        ]);
    }

    /**
     * @throws \ErrorException
     */
    public static function errorException(
        int $errno,
        string $errstr,
        string $errfile,
        string $errline
    ) {
        if (error_reporting() === 0) {
            return;
        }
        throw new \ErrorException($errstr, $errno, $errno, $errfile, $errline);
    }

    public static function errorDispatcher(\Throwable $e)
    {
        $error_handler = self::$error_handlers[$e->getCode()]
            ?? self::$error_handlers[SERVER_ERROR];
        call_user_func($error_handler, $e);
        exit(1);
    }

    public static function handleFatal()
    {
        $err = error_get_last();

        if (!$err) {
            return;
        }

        if (!($err['type'] & E_FATAL)) {
            return;
        }

        $e = new \ErrorException(
            $err['message'],
            $err['type'],
            $err['type'],
            $err['file'],
            $err['line']
        );
        self::errorDispatcher($e);
    }
}
