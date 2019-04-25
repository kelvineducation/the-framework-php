<?php

namespace The;

interface WriterInterface
{
    /**
     * @param string $data
     * @return int Number of bytes written
     */
    public function write(string $data): int;
}
