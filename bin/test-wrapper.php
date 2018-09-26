<?php

require_once __DIR__ . '/../bootstrap.php';

$script = array_shift($argv);
$test_file = array_shift($argv);

if (!file_exists($test_file)) {
    echo "Test file to wrap not found\n";
    exit(1);
}

$err = new \K\Tests\ErrorHandler();
$err->register();

echo sprintf("# %s\n", $test_file);

$include_tests = function ($test_file) {
    include $test_file;
};
$include_tests($test_file);

echo "\n";

function test()
{
    $args = func_get_args();
    $name = '';
    if (count($args) === 2) {
        list($name, $fn) = $args;
    } elseif (count($args) === 1) {
        list($fn) = $args;
    } else {
        throw new InvalidArgumentException("Invalid arguments");
    }

    if ($name !== '') {
        echo "# {$name}\n";
    }
    $t = new \K\Tests\Test(
        function(bool $passed, string $message = '', string $more = '') use (&$failed) {
            $message = ($message ? "- {$message}" : '');
            if ($passed) {
                echo "ok {$message}\n";
            } else {
                echo "not ok {$message}\n";
            }
            echo $more;
        }
    );
    call_user_func($fn, $t);
}
