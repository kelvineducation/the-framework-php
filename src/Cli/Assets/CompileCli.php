<?php

namespace The\Cli\Assets;

use The\Cli\Cli;

class CompileCli extends Cli
{
    public function run(array $args)
    {
        \The\option('asset_buster')->syncManifest();

        return 0;
    }
}
