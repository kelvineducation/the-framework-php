<?php

namespace K\Tests;

use K\ResponseWriterInterface;

class ResponseWriterStub implements ResponseWriterInterface
{
    public $data;
    public function withStatus(int $code)
    {
        echo "{$code}\n";
    }
    public function withHeader(string $name, string $value)
    {
        echo "{$name}: {$value}\n";
    }
    public function write(string $data): int
    {
        $this->data .= $data;
        return strlen($data);
    }
    public function setSessionParam(string $key, string $value)
    {
        echo "S:{$key}:{$value}\n";
    }
}
