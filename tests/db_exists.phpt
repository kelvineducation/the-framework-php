<?php

use function \The\{db};

$sql = <<<SQL
CREATE TEMPORARY TABLE t AS
SELECT generate_series(1, 5) AS num;
SQL;
db()->query($sql);

test("rows are returned", function ($t) {
    $t->ok(db()->exists("SELECT 1"), "Selecting one exists");
    $t->ok(db()->exists("SELECT NULL"), "Selecting null exists");
    $t->ok(db()->exists("SELECT false"), "Selecting false exists");
    $t->ok(db()->exists("SELECT 1 FROM t WHERE num = 1"), "Select from table");
});

test("rows are not returned", function ($t) {
    $t->notOk(db()->exists("SELECT 1 FROM t WHERE num = 6"), "six should not exist");
    $t->notOk(db()->exists("SELECT * FROM t WHERE num = 7"), "7 should not exist");
});
