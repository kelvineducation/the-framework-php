<?php

namespace K\Cli\Migrate;

use K\Cli\Cli;
use K\Migration;

class RollbackCli extends Cli
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
