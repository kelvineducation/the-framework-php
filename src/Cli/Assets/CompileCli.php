<?php

namespace K\Cli\Assets;

use K\Cli\Cli;

class CompileCli extends Cli
{
    public function run(array $args)
    {
        \K\option('asset_buster')->syncManifest();

        return 0;
    }
}
