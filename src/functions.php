<?php

namespace K;

/**
 * @param ResponseWriterInterface $w
 * @param string $url
 * @param int|null $status
 */
function redirect(ResponseWriterInterface $w, string $url, int $status = null)
{
    $w->withStatus($status ?: 302);
    $w->withHeader('Location', $url);
}

/**
 * @param ResponseWriterInterface $w
 * @param mixed $data
 * @param int|null $status
 * @param int $encoding
 * @return ResponseWriterInterface
 */
function json(ResponseWriterInterface $w, $data, int $status = null, $encoding = 0)
{
    if ($status !== null) {
        $w->withStatus($status);
    }
    $w->withHeader('Content-Type', 'application/json;charset=utf-8');
    $w->write($json = json_encode($data, $encoding));
    if ($json === false) {
        throw new \RuntimeException(json_last_error_msg(), json_last_error());
    }
    return $w;
}

/**
 * @param ResponseWriterInterface $w
 * @param string $view_path
 * @param string $layout
 * @param array $vars
 * @param int|null $status
 */
function html(
    ResponseWriterInterface $w,
    string $view_path,
    string $layout = '',
    array $vars = [],
    int $status = null
) {
    if ($status !== null) {
        $w->withStatus($status);
    }
    $w->withHeader('Content-Type', 'text/html; charset=utf-8');
    render($w, $view_path, $layout, $vars);
}

/**
 * @param string $filename
 * @return string
 */
function view_path(string $filename)
{
    return $filename;
}

/**
 * @param WriterInterface $w
 * @param string $view_path
 * @param string $layout
 * @param array $vars
 */
function render(
    WriterInterface $w,
    string $view_path,
    string $layout = '',
    array $vars = []
) {
    try {
        $level = ob_get_level();
        ob_start(function ($data) use ($w) {
            $w->write($data);
            return "";
        }, 4096);
        $v = new View($vars, option('views_dir') ?: '');
        $v->render($view_path, $layout);
        ob_end_clean();
    } catch (\Throwable $e) {
        while (ob_get_level() > $level) {
            ob_end_clean();
        }

        throw $e;
    }
}

/**
 * @param string $view_path
 * @param array $vars
 * @return string
 */
function partial(string $view_path, array $vars = []): string
{
    ob_start();
    extract($vars);
    include option('views_dir') . '/' . $view_path;
    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}

/**
 * @return Db
 */
function db()
{
    return option('db');
}

/**
 * @param ResponseWriterInterface $w
 * @param RequestInterface $request
 * @return bool|User
 * @throws DbException
 */
function require_login(ResponseWriterInterface $w, RequestInterface $request)
{
    $user_id = $request->getSessionParam('user_id');
    if (!$user_id) {
        $q = http_build_query(['prev_uri' => $_SERVER['REQUEST_URI']]);
        redirect($w, '/login?' . $q);
        return false;
    }
    $user = User::find($user_id);
    return $user;
}

/**
 * @param ResponseWriterInterface $w
 * @param RequestInterface $request
 * @return bool|Organization
 * @throws DbException
 */
function require_organization(ResponseWriterInterface $w, RequestInterface $request)
{
    $organization_id = $request->getSessionParam('organization_id');
    if (!$organization_id) {
        redirect($w, '/organizations');
        return false;
    }
    $organization = Organization::find($organization_id);
    return $organization;
}

function load_env()
{
    $env_file = __DIR__ . '/../.env.php';
    if (!file_exists($env_file)) {
        return;
    }
    $env = include $env_file;
    foreach ($env as $key => $value) {
        putenv("{$key}={$value}");
    }
}

/**
 * Get base url for app
 *
 * @param string $path
 * @return string
 */
function url($path = '')
{
    return 'https://' . $_SERVER['HTTP_HOST'] . $path;
}

/**
 * @param string $option
 * @param mixed $value
 */
function option(string $option = null, ...$args)
{
    static $options = [];
    static $frozen = [];

    if ($option === null) {
        return $options;
    }

    if (count($args)) {
        if (isset($frozen[$option])) {
            throw new Exception(sprintf("Cannot set option '%s' again", $option));
        }
        [$value] = $args;
        $options[$option] = $value;
        return $options[$option];
    }

    if (!array_key_exists($option, $options)) {
        return null;
    }

    if ($options[$option] instanceof Factory) {
        return call_user_func($options[$option]);
    }

    if (empty($frozen[$option]) && $options[$option] instanceof Service) {
        $options[$option] = call_user_func($options[$option]);
        $frozen[$option] = true;
        return $options[$option];
    }

    return $options[$option];
}

function service(callable $fn) {
    return new Service($fn);
}
function factory(callable $fn) {
    return new Factory($fn);
}

class NamedCallable
{
    private $fn;
    public function __construct(callable $fn) {
        $this->fn = $fn;
    }
    public function __invoke() {
        return call_user_func($this->fn);
    }
}

class Service extends NamedCallable { }
class Factory extends NamedCallable { }
