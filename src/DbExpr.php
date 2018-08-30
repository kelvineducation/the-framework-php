<?php

namespace K;

class DbExpr
{
    private $expr;

    public function __construct(string $expr)
    {
        $this->expr = $expr;
    }

    public function __toString()
    {
        return $this->expr;
    }
}
