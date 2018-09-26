<?php

use function \K\{db};

test(function ($t) {
    db()->query("CREATE TEMPORARY TABLE t (id serial, a varchar, b int, c bool)");
    $row = db()->insert('t', [
        'a' => 'hello',
        'b' => 2,
        'c' => true,
    ]);
    $t->equals(
        $row,
        ['id' => '1', 'a' => 'hello', 'b' => '2', 'c' => 't'],
        "create record and return default values"
    );

    $row2 = db()->insert('t', ['b' => 999]);
    $t->equals(
        $row2,
        ['id' => '2', 'a' => null, 'b' => '999', 'c' => null],
        "only insert fields that are passed"
    );

    db()->query("DROP TABLE t");
});
