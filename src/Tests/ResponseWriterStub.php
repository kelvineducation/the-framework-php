<?php

namespace The\Tests;

use The\ResponseWriterInterface;

class ResponseWriterStub implements ResponseWriterInterface
{
    public $data = '';
    public $status;
    public $session;
    public $headers = [];
    public function withStatus(int $code)
    {
        $this->status = $code;
    }
    public function withHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;
    }
    public function write(string $data): int
    {
        $this->data .= $data;
        return strlen($data);
    }
    public function setSessionParam(string $key, string $value)
    {
        $this->session[$key] = $value;
    }

    public function isRedirect(): bool
    {
        return $this->status === 302;
    }

    public function hasBody(): bool
    {
        return $this->data !== '';
    }
}
