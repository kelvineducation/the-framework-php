--TEST--
db insert()
--FILE--
<?php

use function K\db;

require_once __DIR__ . '/../bootstrap.php';

db()->query("CREATE TEMPORARY TABLE t (id serial, a varchar, b int, c bool)");
var_dump(db()->insert('t', [
    'a' => 'hello',
    'b' => 2,
    'c' => true,
]));
var_dump(db()->insert('t', [
    'b' => 999,
]));

?>
--EXPECT--
array(4) {
  ["id"]=>
  string(1) "1"
  ["a"]=>
  string(5) "hello"
  ["b"]=>
  string(1) "2"
  ["c"]=>
  string(1) "t"
}
array(4) {
  ["id"]=>
  string(1) "2"
  ["a"]=>
  NULL
  ["b"]=>
  string(3) "999"
  ["c"]=>
  NULL
}
