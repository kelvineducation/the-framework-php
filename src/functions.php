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
function viewPath(string $filename)
{
    return __DIR__ . '/../views/' . $filename;
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
    if ($layout !== '') {
        $stream = new Stream();
        render($stream, $view_path, '', $vars);
        render($w, $layout, '', [
            'content' => $stream->output()
        ]);
        return;
    }

    ob_start(function ($data) use ($w) {
        $w->write($data);
        return "";
    });
    extract($vars);
    include $view_path;
    ob_end_clean();
}
