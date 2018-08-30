--TEST--
db fetchRow()
--FILE--
<?php

use function K\db;

require_once __DIR__ . '/../vendor/autoload.php';

var_dump(db()->fetchRow('SELECT 1 AS t'));

db()->query("CREATE TEMPORARY TABLE data (data_id int, data varchar);");
db()->query("INSERT INTO data VALUES (1, 'hello'), (2, 'world');");

var_dump(db()->fetchRow("SELECT * FROM data WHERE data_id = 2 LIMIT 1"));

?>
--EXPECT--
array(1) {
  ["t"]=>
  string(1) "1"
}
array(2) {
  ["data_id"]=>
  string(1) "2"
  ["data"]=>
  string(5) "world"
}
