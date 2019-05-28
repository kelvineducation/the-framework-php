<?php

namespace The\Db;

class DuplicateTransactionNameException extends \Exception
{
    /**
     * @param string $transaction_name
     */
    public function __construct($transaction_name)
    {
        parent::__construct(
            "Transaction name '{$transaction_name}' is already active."
        );
    }
}
