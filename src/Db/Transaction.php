<?php

namespace The\Db;

use The\Db;

class Transaction
{
    /**
     * @var Db
     */
    private $db;

    /**
     * @var array
     */
    private $names = [];

    /**
     * @var string
     */
    private $current_name = null;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function begin(string $name)
    {
        if (isset($this->names[$name])) {
            throw new DuplicateTransactionNameException($name);
        }

        if (null === $this->current_name) {
            $this->db->query('BEGIN');
        }

        $this->names[$name] = true;
        $this->current_name = $name;
    }

    public function accept(string $name)
    {
        if (!isset($this->names[$name])) {
            throw new UnknownTransactionNameException($name);
        }

        if ($this->current_name !== $name) {
            throw new TransactionAcceptanceOrderException($this->current_name, $name);
        }

        unset($this->names[$name]);
        end($this->names);
        $this->current_name = key($this->names);

        if (null === $this->current_name) {
            $this->db->query('COMMIT');
        }
    }

    public function rollbackAll()
    {
        if (null === $this->current_name) {
            return;
        }

        $this->names = [];
        $this->current_name = null;

        $this->db->query('ROLLBACK');
    }
}
