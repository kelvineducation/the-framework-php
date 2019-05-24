<?php

use function \The\{db};
use The\DbException;

test(function ($t) {
    $result = db()->query('SELECT 1');
    $t->equals(get_class($result), 'The\DbResult', "Query returns a result object");
});

test(function ($t) {
    $result = db()->query('SELECT $1', ['1']);
    $t->equals(get_class($result), 'The\DbResult', "query accepts parameters");
});

test(function ($t) {
    $t->throws(function () {
        @db()->query('SELECT 1 FROM fail');
    }, "/Failed to run query/", "failed query throws DbException");
});
