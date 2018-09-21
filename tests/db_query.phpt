--TEST--
db query() runs a query
--FILE--
<?php

use function K\db;
use K\DbException;

require_once __DIR__ . '/../bootstrap.php';

$result = db()->query('SELECT 1');
var_dump(get_class($result));
$result = db()->query('SELECT $1', ['1']);
var_dump(get_class($result));

try {
    @db()->query('SELECT 1 FROM fail');
} catch (DbException $e) {
    var_dump($e->getMessage());
}

?>
--EXPECT--
string(10) "K\DbResult"
string(10) "K\DbResult"
string(19) "Failed to run query"
