<?php

use function \K\{db};

$sql = <<<SQL
CREATE TEMPORARY TABLE t AS
SELECT generate_series(1, 5) AS num;
SQL;
db()->query($sql);

test(function ($t) {
    $t->equals(db()->fetchOne("SELECT 1"), '1', "grabs simple select value");
});

test(function ($t) {
    $t->ok(
        db()->fetchOne("SELECT * FROM t WHERE num = 6") === null,
        "returns null if row does not exist"
    );
});

test(function ($t) {
    $t->equals(
        db()->fetchOne("SELECT 'yup', 'nope'"),
        'yup',
        "grab first column"
    );
});

test(function ($t) {
    $t->equals(
        db()->fetchOne("SELECT num, COUNT(*) OVER () FROM t ORDER BY num"),
        '1',
        "grab first column of first row"
    );
});
