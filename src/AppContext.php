<?php

namespace The;

class AppContext
{
    public function __construct() { }
    public function configure(App $app) { }
    public function run() { }

    public function defaultErrorHandler(\Throwable $e)
    {
        http_response_code(500);
        echo "An error occurred. That is all. Please try again later.\n";
        exit(1);
    }
}
