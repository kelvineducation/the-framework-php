<?php

namespace The\Db;

use Exception;

class UnknownTransactionNameException extends Exception
{
    public function __construct(string $transaction_name)
    {
        parent::__construct(
            "Transaction name '{$transaction_name}' was not started and therefore cannot be accepted."
        );
    }
}
