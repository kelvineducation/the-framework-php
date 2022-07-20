<?php

namespace The;

class View implements \ArrayAccess
{
    private $vars = [];
    private $content_for = '';
    private $views_dir = '';

    public function __construct(array $vars = [], string $views_dir = '')
    {
        $this->vars = $vars;
        $this->views_dir = $views_dir;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->vars[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->vars[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->vars[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->vars[$offset] = $value;
    }

    public function h($key)
    {
        return esc($this->vars[$key] ?? '');
    }

    public function start($name)
    {
        $this->content_for = $name;
        ob_start();
    }

    public function end()
    {
        $this->vars[$this->content_for] = ob_get_clean();
        $this->content_for = '';
    }

    public function render(string $view_file, string $layout_file = '')
    {
        if ($layout_file) {
            include $this->path($layout_file);
        } else {
            include $this->path($view_file);
        }
    }

    public function partial(string $view_file, array $vars = [])
    {
        $d = new self($vars, $this->views_dir);
        $d->render($view_file);
    }

    private function path(string $file): string
    {
        if ($this->views_dir === '') {
            return $file;
        }

        return $this->views_dir . '/' . $file;
    }
}
