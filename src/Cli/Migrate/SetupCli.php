<?php

namespace The\Cli\Migrate;

use The\Cli\Cli;
use The\Migration;

class SetupCli extends Cli
{
    public function run(array $args)
    {
        $result = Migration::createMigrationsTable();
        if ($result) {
            echo "Created migrations table\n";
        } else {
            echo "Failed\n";
            return 1;
        }

        return 0;
    }
}
