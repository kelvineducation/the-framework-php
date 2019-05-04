<?php

namespace The;

class CliContext extends AppContext
{
    /**
     * string $namespace
     */
    private $namespace;

    /**
     * @var array $argv
     */
    private $argv;

    /**
     * @param array $argv
     */
    public static function init(string $namespace, array $argv)
    {
        return new CliContext($namespace, $argv);
    }

    /**
     * @param array $argv
     */
    public function __construct(string $namespace, array $argv)
    {
        $this->namespace = $namespace;
        $this->argv = $argv;
    }

    public function run()
    {
        $argv = $this->argv;
        $script = array_shift($argv);
        $command_name = array_shift($argv);

        if (!$command_name) {
            $this->help($script);
            exit(1);
        }

        $class_name = strtr(ucwords($command_name, ':-'), [':' => '\\', '-' => '']);
        $command_class = sprintf('\%s\Cli\%sCli', $this->namespace, $class_name);
        if (!class_exists($command_class)) {
            echo "Unknown command '{$class_name}'\n\n";
            $this->help($script);
            exit(1);
        }

        $command = new $command_class();
        exit($command->run($argv));
    }

    public function defaultErrorHandler(\Throwable $e)
    {
        $honeybadger = option('honeybadger');
        $honeybadger->notify($e);

        echo "{$e}\n";
    }

    private function help($script)
    {
echo <<<HELP
{$script} {{then read the source}}\n
HELP;
    }
}
