<?php

use function \The\{db};

test("quoting postgres column names", function ($t) {
    $t->equals(db()->quoteCol('id'), '"id"', "quote column with double quotes");
    $t->equals(db()->quoteCol('someCol'), '"someCol"', "quote column with capital letters");
});

test("parameterizing sql IN statements", function ($t) {
    $organization_id = 1;
    $params = [$organization_id];

    $emails = ['zach@kelvin.education', 'matt@kelvin.education'];
    [$in_sql, $params] = db()->quoteIn($emails, $params);

    $t->equals($in_sql, '$2, $3', "parameter count increases");
    $t->equals($params, [
        $organization_id,
        'zach@kelvin.education',
        'matt@kelvin.education',
    ], "quotedIn values get added to params");
});

test("quoting sql values", function ($t) {
    $t->equals(db()->quote('value'), "'value'", "quotes a string");
    $t->equals(db()->quote(1), "1", "doesn't quote integer");
    $t->equals(db()->quote("you're"), "'you''re'", "quotes single quotes");
    $t->equals(db()->quote(null), "NULL", "doesn't quote null");
    $t->equals(db()->quote(false), "FALSE", "doesn't quote boolean");
    $t->equals(db()->quote(['a', 'b']), "ARRAY['a', 'b']", "converts array");
    $t->equals(db()->quote(new The\DbExpr('now()')), "now()", "ignores expressions");
});

test("imploding integers", function ($t) {
    $t->equals(db()->implodeInts([1,8,2,3]), '1, 8, 2, 3', 'implodes integers');
    $t->equals(db()->implodeInts(['1','8','2']), '1, 8, 2', 'implodes string integers');
    $t->equals(db()->implodeInts(['php','yo','3','7']), '0, 0, 3, 7', 'casts to integers');
});
