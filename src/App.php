<?php

namespace The;

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

    public static function run(AppContext $app_context)
    {
        $app = new self();

        self::setErrorReportingLevel();
        set_error_handler([__CLASS__, 'errorException'], E_ALL);
        set_exception_handler([__CLASS__, 'errorDispatcher']);
        register_shutdown_function([__CLASS__, 'handleFatal']);

        $app->registerErrorHandler(SERVER_ERROR, [$app_context, 'defaultErrorHandler']);

        $app_context->configure($app);
        $app_context->run();
    }

    public function registerErrorHandler(int $err_code, callable $handler)
    {
        self::$error_handlers[$err_code] = $handler;
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
        if (!(error_reporting() & $errno)) {
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

    private static function setErrorReportingLevel()
    {
        if (getenv('APP_ENV') === ENV_PRODUCTION) {
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
            return;
        }

        error_reporting(E_ALL);
    }
}
