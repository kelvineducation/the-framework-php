<?php

namespace The\Cli;

use The\Cli\Cli;

class AssetsCompileCli extends Cli
{
    public function run(array $args)
    {
        \The\option('asset_buster')->syncManifest();

        return 0;
    }
}
