<?php

namespace K;

class Response implements ResponseWriterInterface
{
    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var Stream
     */
    private $body_stream;

    /**
     * @var int
     */
    private $status_code = 200;

    /**
     * @var array
     */
    private $session = [];

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
     * @throws \RuntimeException
     */
    public function write(string $data): int
    {
        return $this->getBodyStream()->write($data);
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setSessionParam(string $key, string $value)
    {
        $this->session[$key] = $value;
    }

    public function isRedirect(): bool
    {
        return $this->status_code === 302;
    }

    public function hasBody(): bool
    {
        return (bool) $this->body_stream;
    }

    /**
     * @param array $output_methods
     */
    public function send(array $output_methods = [])
    {
        $this->output($output_methods);
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

        // Session headers
        if (!empty($this->session)) {
            foreach ($this->session as $key => $value) {
                $_SESSION[$key] = $value;
            }
        }

        foreach ($this->headers as $name => $value) {
            call_user_func($output_methods['header'], sprintf("%s: %s", $name, $value));
        }

        call_user_func($this->getBodyStream()->output($output_methods['body']));
    }

    /**
     * @return Stream
     */
    private function getBodyStream()
    {
        if ($this->body_stream) {
            return $this->body_stream;
        }
        $this->body_stream = new Stream();
        return $this->body_stream;
    }
}
