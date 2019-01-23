<?php

namespace K;

use DirectoryIterator;

class AssetBuster
{
    private $manifest_path = '';
    private $public_path = '';
    private $options = [];
    private $assets = [];

    public function __construct(string $manifest_path, string $public_path, array $options = [])
    {
        $this->manifest_path = $manifest_path;
        $this->public_path = $public_path;
        $this->options = array_merge([
            'sync_manifest' => false,
            'dirnames'      => ['js', 'css'],
            'links_dirname' => 'asset_links',
        ], $options);
    }

    public function initialize()
    {
        $this->assets = $this->getAssetsFromManifest();

        if ($this->options['sync_manifest'] === true) {
            $this->syncManifest();
        }
    }

    public function getAssetUrl(string $url): string
    {
        if (!isset($this->assets[$url])) {
            throw new \Exception(sprintf(
                "Linked asset not found for **%s**. Make sure it exists in " .
                "the manifest file *%s*",
                $url,
                $this->manifest_path
            ));
        }

        return $this->assets[$url]['url'];
    }

    private function getAssetsFromManifest(): array
    {
        if (!file_exists($this->manifest_path)) {
            return [];
        }

        $manifest_json = file_get_contents($this->manifest_path);
        if ($manifest_json === false) {
            throw new \Exception(sprintf(
                "Could not read manifest json '%s'",
                $this->manifest_path
            ));
        }

        return json_decode($manifest_json, true);
    }

    private function syncManifest()
    {
        foreach ($this->options['dirnames'] as $dirname) {
            $files = new DirectoryIterator($this->getPublicPath($dirname));
            foreach ($files as $file) {
                $this->syncAsset($dirname, $file);
            }

            ksort($this->assets);
            $wrote_manifest = file_put_contents(
                $this->manifest_path,
                json_encode($this->assets, JSON_PRETTY_PRINT)
            );
            if (!$wrote_manifest) {
                throw new \Exception(sprintf(
                    "Unable to update the manifest file '%s'",
                    $this->manifest_path
                ));
            }
        }
    }

    private function syncAsset(string $dirname, \SplFileInfo $file)
    {
        // Note: This doesn't work with nested asset directories which is okay
        // because we don't need that complication right now
        if (!$file->isFile()) {
            return;
        }

        // e.g. /js/app.js
        $url = '/' . $dirname . '/' . $file->getFilename();

        if (!$this->assetHasChanged($url, $file)) {
            return;
        }

        $this->removeOldSymlink($url);

        $sha = sha1_file($file->getPathname());
        if ($sha === false) {
            throw new \Exception(sprintf(
                "Could not make sha for %s", $file->getPathname()
            ));
        }
        $link_filename = $sha . '_' . $file->getFilename();
        $link_url = '/asset_links/' . $dirname . '/' . $link_filename;
        $link_pathname = $this->getPublicPath($link_url);
        $relative_target_pathname = '../../' . $dirname . '/' . $file->getFilename();

        $this->assets[$url] = [
            'mtime' => $file->getMTime(),
            'url'   => $link_url,
        ];

        if (file_exists($link_pathname)) {
            return;
        }

        $symlink = @symlink($relative_target_pathname, $link_pathname);
        if (!$symlink) {
            throw new \Exception(sprintf(
                "Could not create asset symlink %s -> %s",
                $relative_target_pathname,
                $link_pathname
            ));
        }
    }

    private function assetHasChanged(string $url, \SplFileInfo $file): bool
    {
        if (isset($this->assets[$url])
            && $this->assets[$url]['mtime'] == $file->getMTime()
        ) {
            return false;
        }

        return true;
    }

    private function removeOldSymlink(string $url)
    {
        if (!isset($this->assets[$url])) {
            return;
        }

        $old_symlink_path = $this->getPublicPath($this->assets[$url]['url']);
        $remove_old_symlink = unlink($old_symlink_path);
        if ($remove_old_symlink === false) {
            throw new \Exception(sprintf(
                "Could not remove old symlink **%s**",
                $old_symlink_path
            ));
        }
    }

    private function getPublicPath(...$dirs)
    {
        $path = $this->public_path;
        foreach ($dirs as $dir) {
            $path .= '/' . $dir;
        }

        return $path;
    }
}
