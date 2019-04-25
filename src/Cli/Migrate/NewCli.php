<?php

namespace The\Cli\Migrate;

use The\Cli\Cli;
use The\Migration;

class NewCli extends Cli
{
    public function run(array $args)
    {
        $migration_name = array_shift($args) ?: '';
        $filename = Migration::create($migration_name);
        if ($filename === '') {
            printf("Could not write new migration '%s'.\n", $migration_name);
            return 1;
        } else {
            echo "Created migration {$filename}\n";
        }

        return 0;
    }
}
