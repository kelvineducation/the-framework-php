<?php

namespace The\Db;

use Exception;

class TransactionAcceptanceOrderException extends Exception
{
    public function __construct(string $expected_name, string $actual_name)
    {
        parent::__construct(
            "Transaction name '{$actual_name}' cannot be accepted before "
            . "transaction name '{$expected_name}'."
        );
    }
}
