<?php

namespace K;

use Exception;

class DbException extends Exception
{
    private $db;

    public function setDb(Db $db)
    {
        $this->db = $db;
    }

    public function getLastError(): string
    {
        return $this->db->getLastError();
    }
}
