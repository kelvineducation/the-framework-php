<?php

namespace The;

class CliContext extends AppContext
{
    /**
     * @var array $argv
     */
    private $argv;

    /**
     * @param array $argv
     */
    public static function init(array $argv)
    {
        return new CliContext($argv);
    }

    /**
     * @param array $argv
     */
    public function __construct(array $argv)
    {
        $this->argv = $argv;
    }

    public function run()
    {
        $argv = $this->argv;
        $script = array_shift($argv);
        $command_name = array_shift($argv);

        if (!$command_name) {
            $this->help();
            exit(1);
        }

        $class_name = strtr(ucwords($command_name, ':-'), [':' => '\\', '-' => '']);
        $command_class = sprintf('\K\Cli\%sCli', $class_name);
        if (!class_exists($command_class)) {
            echo "Unknown command '{$class_name}'\n\n";
            $this->help();
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

    private function help()
    {
echo <<<HELP
bin/The {{then read the source}}\n
HELP;
    }
}
