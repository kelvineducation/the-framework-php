<?php

namespace K;

class Request implements RequestInterface, Form\RequestInterface
{
    private $headers;
    private $params;

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParam(string $key, $default = null)
    {
        $params = $this->getParams();

        return $params['JSON'][$key] ?? $params['POST'][$key]
            ?? $params['GET'][$key] ?? $default;
    }

    public function getJson()
    {
        return $this->getParams()['JSON'] ?? null;
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function getSessionParam(string $key, string $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function getHeader(string $key): string
    {
        $headers = $this->getHeaders();
        return $headers[$key] ?? '';
    }

    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    private function getParams()
    {
        if ($this->params) {
            return $this->params;
        }

        $params = [];
        $params['GET'] =& $_GET;
        $params['POST'] =& $_POST;
        if ($this->getMethod() === 'POST'
            && $this->getHeader('CONTENT_TYPE') === 'application/json'
        ) {
            $params['JSON'] = json_decode(file_get_contents('php://input'), true);
        }

        $this->params = $params;

        return $this->params;
    }

    private function getHeaders(): array
    {
        if ($this->headers) {
            return $this->headers;
        }
        $headers = [];
        $headers['CONTENT_TYPE'] = $_SERVER['CONTENT_TYPE'] ?? '';
        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            }
        }
        $this->headers = $headers;
        return $this->headers;
    }
}
