<?php

namespace The\Db;

class UnknownTransactionNameException extends \Exception
{
    /**
     * @param string $transaction_name
     */
    public function __construct($transaction_name)
    {
        parent::__construct(
            "Transaction name '{$transaction_name}' was not started and therefore cannot be accepted."
        );
    }
}
