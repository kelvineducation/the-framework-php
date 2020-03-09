<?php

namespace The\Tests;

use The\Form\RequestInterface as FormRequestInterface;
use The\RequestInterface;

class RequestStub implements RequestInterface, FormRequestInterface
{
    public $params = [];
    public $session_params = [];
    public $method;
    public $headers;
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

    public function getHeader(string $key): string
    {
        return $this->headers[$key] ?? '';
    }
}
