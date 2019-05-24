<?php

namespace The\Cli;

use The\Cli\Cli;
use The\Migration;

class MigrateRollbackCli extends Cli
{
    public function run(array $args)
    {
        Migration::rollback(
            function ($mig_id) {
                echo "* Rolling back {$mig_id}...";
            },
            function () {
                echo "Done\n";
            }
        );

        return 0;
    }
}
