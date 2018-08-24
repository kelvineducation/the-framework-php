<?php

namespace K;

interface ResponseWriterInterface extends WriterInterface
{
    /**
     * @param int $code
     */
    public function withStatus(int $code);

    /**
     * @param string $name
     * @param string $value
     */
    public function withHeader(string $name, string $value);
}
