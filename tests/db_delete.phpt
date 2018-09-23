<?php 

use function \K\{db};

db()->query("CREATE TEMPORARY TABLE t (id serial, letter varchar)");

function countLetter($letter)
{
    return db()->fetchOne("SELECT COUNT(*) FROM t WHERE letter = $1", [$letter]);
}

test("deleting from table", function ($t) {
    db()->query("INSERT INTO t (letter) VALUES ('a'), ('a')");

    $t->equals(countLetter('a'), 2);
    db()->delete('t', "letter = 'a'");
    $t->equals(countLetter('a'), 0, "all letter a's removed");
});

test("deleting from table with params", function ($t) {
    db()->query("INSERT INTO t (letter) VALUES ('b'), ('b')");

    $t->equals(countLetter('b'), 2);
    db()->delete('t', "letter = $1", ['b']);
    $t->equals(countLetter('b'), 0, "all letter b's removed");
});

test("deleting requires where clause", function ($t) {
    $t->throws(function() {
        db()->delete('t', '');
    }, '/No \$where condition passed/');
});
