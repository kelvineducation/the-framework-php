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

        try {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            [$page_class, $vars] = page($path, 'Home', '\K\Pages\\');

            $response = new \K\Response();
            $request = new \K\Request();
            if (!class_exists($page_class)) {
                $app->handleNotFound($response);
                $response->send();
                return;
            }

            $page = call_user_func(["$page_class", 'factory']);
            $page->__invoke($response, $request, $vars);
            $response->send();
        } catch (\Throwable $e) {
            $app->defaultErrorHandler($e);
        }
    }

    protected function configure() { }
    protected function initialize() {  }
    protected function handleServerError(\K\ResponseWriterInterface $w, \Throwable $e) { }
    protected function handleNotFound(\K\ResponseWriterInterface $w) { }
    protected function handleNotAllowed(\K\ResponseWriterInterface $w) { }

    protected function registerErrorHandler(int $err_code, callable $handler)
    {
        self::$error_handlers[$err_code] = $handler;
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
