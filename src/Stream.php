<?php

namespace The;

use RuntimeException;

class Stream implements WriterInterface
{
    /**
     * @var resource
     */
    private $stream;

    private $size = 0;

    /**
     * @param string $data
     * @return int Number of bytes written
     * @throws RuntimeException
     */
    public function write(string $data): int
    {
        $bytes = fwrite($this->getStream(), $data);
        if ($bytes === false) {
            throw new RuntimeException("Could not write to stream");
        }
        $this->size += $bytes;
        return $bytes;
    }

    public function writeCsv(array $data, ...$params)
    {
        $bytes = fputcsv($this->getStream(), $data, ...$params);
        if ($bytes === false) {
            throw new RuntimeException('Could not write to stream');
        }
        $this->size += $bytes;
        return $bytes;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param callable|null $fn Output function
     * @return callable
     */
    public function output(callable $fn = null)
    {
        if ($fn === null) {
            $fn = function ($data) {
                echo $data;
            };
        }
        return function () use ($fn) {
            rewind($this->getStream());
            while (!feof($this->getStream())) {
                call_user_func($fn, fread($this->getStream(), 1024));
            }
            fclose($this->getStream());
        };
    }

    /**
     * @return resource
     * @throws RuntimeException
     */
    private function getStream()
    {
        if ($this->stream) {
            return $this->stream;
        }
        $this->stream = fopen('php://temp', 'r+');
        if ($this->stream === false) {
            throw new RuntimeException("Could not open stream php://temp");
        }
        return $this->stream;
    }
}
