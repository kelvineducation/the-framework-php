<?php

namespace The\Db;

class TransactionAcceptanceOrderException extends \Exception
{
    /**
     * @param string $expected_name
     * @param string $actual_name
     */
    public function __construct($expected_name, $actual_name)
    {
        parent::__construct(
            "Transaction name '{$actual_name}' cannot be accepted before "
            . "transaction name '{$expected_name}'."
        );
    }
}
