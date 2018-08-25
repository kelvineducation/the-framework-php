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
        return $_GET[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function getSessionParam(string $key, string $default = null)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION[$key] ?? $default;
    }
}
