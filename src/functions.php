<?php

namespace The;

define('URL_PARAM_PREFIX', '_');

function esc(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function url_for(
    string $page_class,
    array $path_params = [],
    array $query_string_params = [],
    string $hash = ''
): string {
    return (new PageUrl($page_class, $path_params, $query_string_params, $hash))->__toString();
}

/**
 * @param ResponseWriterInterface $w
 * @param string $url
 * @param int|null $status
 */
function redirect($response, $request, string $url, int $status = null)
{
    $response->withStatus($status ?: 302);
    $response->withHeader('Location', $url);
    if ($request->getHeader('TURBOLINKS_REFERRER')) {
        $response->setSessionParam('_turbolinks_location', $url);
    }
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

function html(
    $response,
    string $view_path,
    string $layout = '',
    array $vars = [],
    int $status = null
) {
    if ($status !== null) {
        $response->withStatus($status);
    }
    $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    render($response, $view_path, $layout, $vars);
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
    if (getenv('BASE_URL')) {
        return getenv('BASE_URL') . $path;
    }

    return 'https://' . getenv('HEROKU_APP_NAME') . '.herokuapp.com' . $path;
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

function asset_url(string $asset_url)
{
    return option('asset_buster')->getAssetUrl($asset_url);
}
