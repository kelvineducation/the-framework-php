<?php

namespace The\Cli;

use The\Cli\Cli;
use The\Migration;

class MigrateSetupCli extends Cli
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
