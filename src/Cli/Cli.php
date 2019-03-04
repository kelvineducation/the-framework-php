<?php

namespace K\Cli;

abstract class Cli
{
    public static function init(array $args = null)
    {
        return new static($args);
    }

    public function run() {return 0;}
}
