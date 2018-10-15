<?php

namespace K;

class Request implements RequestInterface, Form\RequestInterface
{
    public function __construct()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['CONTENT_TYPE'] === 'application/json') {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParam(string $key, $default = null)
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
