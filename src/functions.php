<?php

namespace K;

/**
 * Checks if the request uri matches a route regex pattern
 * Example: if (route('#/user/(\d+)#, $matches))
 * This strips trailing slashes
 *
 * @param string $pattern
 * @param array|null $matches
 * @return bool
 */
function route(string $pattern, array &$matches = null)
{
    $req_uri = $_SERVER['REQUEST_URI'];
    if (strpos($req_uri, '?') !== false) {
        $req_uri = substr($req_uri, 0, strpos($req_uri, '?'));
    }
    $req_uri = rtrim($req_uri, '/') ?: '/';

    if (preg_match($pattern, $req_uri, $matches)) {
        return true;
    }

    return false;
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
