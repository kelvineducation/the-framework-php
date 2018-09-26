<?php

namespace K\Tests;

use Throwable;

class Test
{
    /**
     * @param callable $tested_fn
     */
    private $tested_fn;

    /**
     * @param callable $tested_fn
     */
    public function __construct(callable $tested_fn)
    {
        $this->tested_fn = $tested_fn;
    }

    /**
     * @param string $message
     */
    public function pass(string $message = '')
    {
        call_user_func($this->tested_fn, true, $message);
    }

    /**
     * @param string $message
     * @param array $options
     */
    public function fail(string $message = '', array $options = [])
    {
        $operator = $expected = $actual = '';
        $at = ['file' => '', 'line' => ''];
        extract($options, EXTR_IF_EXISTS);
        
        $more = '';
        if ($options) {
            $more = <<<MORE
  ---
    operator: {$operator}
    expected: {$this->formatValue($expected)}
    actual:   {$this->formatValue($actual)}
    at: {$at['file']}:{$at['line']}
  ...

MORE;
        }
        call_user_func($this->tested_fn, false, $message, $more);
    }

    /**
     * @param bool $value
     * @param string $message
     */
    public function ok(bool $value, string $message = '')
    {
        $expected = true;
        if ($value === $expected) {
            $this->pass($message);
        } else {
            $this->fail($message, [
                'operator' => 'ok',
                'expected' => $expected,
                'actual'   => $value,
                'at'       => debug_backtrace()[0],
            ]);
        }
    }

    /**
     * @param bool $value
     * @param string $message
     */
    public function notOk(bool $value, string $message = '')
    {
        $expected = false;
        if ($value === $expected) {
            $this->pass($message);
        } else {
            $this->fail($message, [
                'operator' => 'notOk',
                'expected' => $expected,
                'actual'   => $value,
                'at'       => debug_backtrace()[0],
            ]);
        }
    }

    /**
     * @param mixed $actual
     * @param mixed $expected
     * @param string $message
     */
    public function equals($actual, $expected, string $message = '')
    {
        if ($actual == $expected) {
            $this->pass($message);
        } else {
            $this->fail($message, [
                'operator' => 'equals',
                'expected' => $expected,
                'actual'   => $actual,
                'at'       => debug_backtrace()[0],
            ]);
        }
    }

    /**
     * @param mixed $actual
     * @param mixed $expected
     * @param string $message
     */
    public function notEquals($actual, $expected, string $message = '')
    {
        if ($actual != $expected) {
            $this->pass($message);
        } else {
            $this->fail($message, [
                'operator' => 'notEquals',
                'expected' => null,
                'actual'   => $actual,
                'at'       => debug_backtrace()[0],
            ]);
        }
    }

    /**
     * @param callable $func
     * @param string $expected regex matching exception class and message
     * @param string $message
     */
    public function throws(callable $func, string $expected, string $message = '')
    {
        $actual = '';
        try {
            call_user_func($func);
        } catch (Throwable $e) {
            $actual = sprintf(
                "exception '%s' with message '%s'",
                get_class($e),
                $e->getMessage()
            );
        }

        if ($actual && preg_match($expected, $actual)) {
            $this->pass($message);

            return;
        }

        $this->fail($message, [
            'operator' => 'throws',
            'expected' => $expected,
            'actual'   => $actual,
            'at'       => debug_backtrace()[0],
        ]);
    }

    /**
     * @param callable $func
     * @param string $message
     */
    public function doesNotThrow(callable $func, string $message = '')
    {
        $actual = '';
        try {
            call_user_func($func);
        } catch (Throwable $e) {
            $actual = sprintf(
                "exception '%s' with message '%s'",
                get_class($e),
                $e->getMessage()
            );
        }

        if (!$actual) {
            $this->pass($message);
            return;
        }

        $this->fail($message, [
            'operator' => 'doesNotThrow',
            'actual'   => $actual,
            'at'       => debug_backtrace()[0],
        ]);
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function formatValue($value)
    {
        if (is_bool($value)) {
            return ($value ? 'true' : 'false');
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }
}
