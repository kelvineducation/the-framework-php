<?php

namespace The;

class Inflector
{
    const PAGE_SUFFIX = 'Page';
    const PAGE_NAMESPACE = '\\App\\Pages\\';

    public static function pascalToSnakeCase(string $pascal_str): string
    {
        $snake_str = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($pascal_str)));

        return $snake_str;
    }

    public static function snakeToPascalCase(string $snake_str): string
    {
        $pascal_str = str_replace('_', '', ucwords($snake_str, '_'));

        return $pascal_str;
    }

    public static function urlifyPage(string $page_class): string
    {
        // Remove namespace and Page suffix if a fully qualified class name is passed,
        // e.g., App\Pages\HomePage would become Home
        if ($namespace_pos = strrchr($page_class, '\\')) {
            $page_class = substr($namespace_pos, 1);
            $page_class = substr($page_class, 0, strrpos($page_class, self::PAGE_SUFFIX));
        }

        $page_class_for_url = self::pascalToSnakeCase($page_class);

        return $page_class_for_url;
    }

    public static function templateifyPage(string $page_class): string
    {
        return self::urlifyPage($page_class);
    }

    public static function pageify(string $page_name): string
    {
        $page_class = self::PAGE_NAMESPACE . self::snakeToPascalCase($page_name) .
            self::PAGE_SUFFIX;

        return $page_class;
    }
}
