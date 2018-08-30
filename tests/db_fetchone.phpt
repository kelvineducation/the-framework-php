--TEST--
db fetchOne()
--FILE--
<?php

use function K\db;

require_once __DIR__ . '/../vendor/autoload.php';

var_dump(db()->fetchOne("SELECT 1"));

db()->query("CREATE TEMPORARY TABLE t (id int)");

var_dump(db()->fetchOne("SELECT * FROM t LIMIT 1"));

?>
--EXPECT--
string(1) "1"
NULL
