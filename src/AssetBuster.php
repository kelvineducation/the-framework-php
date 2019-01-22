<?php

namespace K;

use DirectoryIterator;

class AssetBuster
{
    public static function track($asset_url)
    {
        $buster = new self();

        $assets = [];
        $manifest = __DIR__ . '/../asset_manifest.json';
        if (file_exists($manifest)) {
            $assets = json_decode(file_get_contents($manifest), true);
        }

        if (getenv('APP_ENV') === 'development') {
            $assets = $buster->buildManifest($manifest, $assets);
        }

        return $assets[$asset_url]['url'];
    }

    public function buildManifest($manifest, $assets)
    {
        $dirs = [
            'css',
            'js',
        ];
        foreach ($dirs as $dir) {
            $public_path = realpath(__DIR__ . '/../public');
            $dir_path = $public_path . '/' . $dir;
            foreach (new DirectoryIterator($dir) as $info) {
                if (!$info->isFile()) {
                    continue;
                }

                // e.g. /js/app.js
                $url = '/' . $dir . '/' . $info->getFilename();
                if (isset($assets[$url]) && $assets[$url]['mtime'] == $info->getMTime()) {
                    $asset = $assets[$url];
                } else {
                    if (isset($assets[$url])) {
                        unlink($public_path . '/' . $assets[$url]['url']);
                    }
                    $sha = sha1_file($info->getPathname());
                    if ($sha === false) {
                        throw new \Exception(sprintf(
                            "Could not make sha for %s", $info->getPathname()
                        ));
                    }
                    $link_filename = $sha . '_' . $info->getFilename();
                    $link_pathname = $public_path . '/asset_links/' . $dir . '/' . $link_filename;
                    $relative_target_pathname = '../../' . $dir . '/' . $info->getFilename();

                    if (!file_exists($link_pathname)) {
                        $symlink = @symlink($relative_target_pathname, $link_pathname);
                        if (!$symlink) {
                            throw new \Exception(sprintf(
                                "Could not create asset symlink %s -> %s",
                                $relative_target_pathname,
                                $link_pathname
                            ));
                        }
                    }

                    $asset = [
                        'mtime' => $info->getMTime(),
                        'url'   => '/asset_links/' . $dir . '/' . $link_filename,
                    ];
                }
                $assets[$url] = $asset;
            }

            file_put_contents($manifest, json_encode($assets));
        }

        return $assets;
    }
}
