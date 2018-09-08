<?php

namespace K;

class Request implements RequestInterface
{
    /**
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function getParam(string $key, string $default = null)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        return $default;
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function getSessionParam(string $key, string $default = null)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start(['read_and_close' => true]);
        }
        return $_SESSION[$key] ?? $default;
    }
}
