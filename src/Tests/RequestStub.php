<?php

namespace K\Tests;

class RequestStub implements \K\RequestInterface
{
    public $params = [];
    public $session_params = [];
    public $method;
    public function getParam(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }
    public function getSessionParam(string $key, string $default = null)
    {
        return $this->session_params[$key] ?? $default;
    }
    public function getMethod()
    {
        return $this->method;
    }
}
