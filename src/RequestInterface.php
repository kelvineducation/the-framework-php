<?php

namespace K;

interface RequestInterface
{
    /**
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function getParam(string $key, string $default = null);

    /**
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function getSessionParam(string $key, string $default = null);
}
