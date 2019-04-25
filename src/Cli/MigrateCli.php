<?php

namespace The\Cli;

use The\Migration;
use function The\db;

class MigrateCli extends Cli
{
    public function run(array $args)
    {
        \The\Model::setDb(function () {
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
