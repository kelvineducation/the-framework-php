<?php

use function \The\{db};

test(function ($t) {
    $t->equals(
        db()->fetchRow("SELECT 1 AS one"),
        ['one' => '1'],
        "selects simple row"
    );
});

test(function ($t) {
    db()->query("CREATE TEMPORARY TABLE data (data_id int, data varchar);");
    db()->query("INSERT INTO data VALUES (1, 'hello'), (2, 'world');");

    $row = db()->fetchRow("SELECT * FROM data WHERE data_id = 2 LIMIT 1");
    $t->equals(
        $row,
        ['data_id' => '2', 'data' => 'world'],
        "can select multiple columns"
    );
});
