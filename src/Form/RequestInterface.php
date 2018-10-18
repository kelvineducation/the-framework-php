<?php

namespace K\Form;

interface RequestInterface
{
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParam(string $key, $default = null);
}
