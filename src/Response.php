<?php

namespace K;

class Response implements ResponseWriterInterface
{
    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var resource
     */
    private $body_stream;

    /**
     * @var int
     */
    private $status_code;

    /**
     * @param int $code
     * @return Response
     */
    public function withStatus(int $code)
    {
        $this->status_code = $code;
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return Response
     */
    public function withHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * @param string $data
     * @return int Number of bytes written
     */
    public function write(string $data): int
    {
        $bytes = fwrite($this->bodyStream(), $data);
        if ($bytes === false) {
            throw new \RuntimeException("Could not write to stream");
        }
        return $bytes;
    }

    /**
     * @param array $output_methods
     */
    public function output(array $output_methods = [])
    {
        $output_methods = array_merge([
            'code'   => function (int $status_code) {
                http_response_code($status_code);
            },
            'header' => function (string $header) {
                header($header);
            },
            'body'   => function (string $data) {
                echo $data;
            }
        ], $output_methods);
        call_user_func($output_methods['code'], $this->status_code);
        foreach ($this->headers as $name => $value) {
            call_user_func($output_methods['header'], sprintf("%s: %s", $name, $value));
        }

        rewind($this->bodyStream());
        while (!feof($this->bodyStream())) {
            call_user_func($output_methods['body'], fread($this->bodyStream(), 1024));
        }
        fclose($this->bodyStream());
    }

    /**
     * @return resource
     */
    private function bodyStream()
    {
        if ($this->body_stream) {
            return $this->body_stream;
        }
        $this->body_stream = fopen('php://temp', 'r+');
        if ($this->body_stream === false) {
            throw new \RuntimeException("Could not open php://temp");
        }

        return $this->body_stream;
    }
}
