--TEST--
db delete()
--FILE--
<?php

use function K\db;
use K\DbException;

require_once __DIR__ . '/../bootstrap.php';

db()->query("CREATE TEMPORARY TABLE t (id serial, letter varchar)");
db()->query("INSERT INTO t (letter) VALUES ('a'), ('a'), ('b'), ('c'), ('d')");

$countLetter = function ($letter) {
    return db()->fetchOne("SELECT COUNT(*) FROM t WHERE letter = $1", [$letter]);
};

// test without parameters
var_dump($countLetter('a'));
db()->delete('t', "letter = 'a'");
var_dump($countLetter('a'));

// test with parameters
var_dump($countLetter('b'));
db()->delete('t', "letter = $1", ['b']);
var_dump($countLetter('b'));

try {
    @db()->delete('t', ' ');
} catch (DbException $e) {
    var_dump($e->getMessage());
}

?>
--EXPECT--
string(1) "2"
string(1) "0"
string(1) "1"
string(1) "0"
string(57) "No $where condition passed when trying to delete from "t""
