<?php

namespace K\Cli;

use function K\db;
use K\Migration;

class MigrateCli extends Cli
{
    public function run(array $args)
    {
        \K\Model::setDb(function () {
            return db();
        });
        Migration::runAll(
            function ($mig_id) {
                echo "* Running {$mig_id}...";
            },
            function ($mig_id, $skipped) {
                if ($skipped) {
                    echo "* Skipping {$mig_id}\n";
                } else {
                    echo "Done\n";
                }
            }
        );

        return 0;
    }
}
