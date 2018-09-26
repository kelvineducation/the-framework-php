<?php

use function \K\{db};
use K\DbException;

test(function ($t) {
    $result = db()->query('SELECT 1');
    $t->equals(get_class($result), 'K\DbResult', "Query returns a result object");
});

test(function ($t) {
    $result = db()->query('SELECT $1', ['1']);
    $t->equals(get_class($result), 'K\DbResult', "query accepts parameters");
});

test(function ($t) {
    $t->throws(function () {
        @db()->query('SELECT 1 FROM fail');
    }, "/Failed to run query/", "failed query throws DbException");
});
