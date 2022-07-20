<?php

namespace The;

class PageUrl
{
    private $page_class;
    private $path_params;
    private $query_string_params;
    private $hash;

    public static function fromUrl(string $url): self
    {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === '/') {
            $page_class = Inflector::pageify('Home');

            return new self($page_class);
        }

        $path_pieces = explode('/', trim($path, '/'));

        $page_class = Inflector::pageify(array_shift($path_pieces));

        $path_params = [];
        while (count($path_pieces)) {
            $param = urldecode(array_shift($path_pieces) ?? '');
            $param_value = urldecode(array_shift($path_pieces) ?? '');
            $path_params[$param] = $param_value;
        }

        return new self($page_class, $path_params);
    }

    public function __construct(
        string $page_class,
        array $path_params = [],
        array $query_string_params = [],
        string $hash = ''
    ) {
        $this->page_class = $page_class;
        $this->path_params = $path_params;
        $this->query_string_params = $query_string_params;
        $this->hash = $hash;
    }

    public function getPageClass(): string
    {
        return $this->page_class;
    }

    public function getPathParams(): array
    {
        return $this->path_params;
    }

    public function toUrl()
    {
        $path = '/';
        if (($page = Inflector::urlifyPage($this->page_class)) !== 'home') {
            $path .= $page;
        }

        foreach ($this->path_params as $param => $value) {
            $path .= sprintf('/%s/%s', urlencode($param ?? ''), urlencode($value ?? ''));
        }

        $url = $path;
        if ($this->query_string_params) {
            $url .= '?' . http_build_query($this->query_string_params);
        }

        if ($this->hash) {
            $url .= "#{$this->hash}";
        }

        return $url;
    }

    public function __toString()
    {
        return $this->toUrl();
    }
}
