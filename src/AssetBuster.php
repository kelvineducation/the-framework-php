<?php

namespace The;

use App\Libs\Debug;
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

    public function syncManifest()
    {
        foreach ($this->options['dirnames'] as $dirname) {
            $files = new DirectoryIterator($this->public_path . '/' . $dirname);
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

    private function syncAsset(string $dirname, DirectoryIterator $file, int $recursion_level = 0)
    {
        // Note: This doesn't work with nested asset directories which is okay
        // because we don't need that complication right now

        Debug::echo(sprintf('%s/%s', $dirname, $file->getFilename()));

        // update method to support nested asset directories
        if ($file->isDir() && !$file->isDot()) {
            Debug::echo('recursing');
            $sub_dirname = sprintf('%s/%s', $dirname, $file->getFilename());
            $nested_files = new DirectoryIterator($file->getPathname());
            foreach ($nested_files as $nested_file) {
                $this->syncAsset($sub_dirname, $nested_file, $recursion_level + 1);
            }
            return;
        }

        if (!$file->isFile() || $file->getFilename() === '.keep') {
            Debug::echo('skipping');
            return;
        }

        // e.g. /js/app.js
        $url = '/' . $dirname . '/' . $file->getFilename();

        if (!$this->assetHasChanged($url, $file)) {
            Debug::echo('skipping unchanged');
            return;
        }

        $sha = sha1_file($file->getPathname());
        if ($sha === false) {
            throw new \Exception(sprintf(
                "Could not make sha for %s", $file->getPathname()
            ));
        }
        $link_filename = sprintf('%s_%s', $sha, $file->getFilename());
        if ($file->getExtension() === 'map') {
            $link_filename = $file->getFilename();
        }
        $link_url = '/asset_links/' . $dirname . '/' . $link_filename;
        $link_pathname = $this->public_path . $link_url;
        $relative_target_pathname = '../../' . str_repeat('../', $recursion_level) . $dirname . '/' . $file->getFilename();

        @unlink($link_pathname);

        $this->assets[$url] = [
            'sha'   => $sha,
            'url'   => $link_url,
        ];

        Debug::echo(sprintf('linking %s -> %s', $link_pathname, $relative_target_pathname));
        $symlink = @symlink($relative_target_pathname, $link_pathname);

        if (!$symlink) {
            throw new \Exception(sprintf(
                "Could not create asset symlink %s -> %s",
                $link_pathname,
                $relative_target_pathname
            ));
        }
    }

    private function assetHasChanged(string $url, \SplFileInfo $file): bool
    {
        $sha = sha1_file($file->getPathname());
        if (isset($this->assets[$url])
            && $this->assets[$url]['sha'] == $sha
        ) {
            return false;
        }

        return true;
    }
}
