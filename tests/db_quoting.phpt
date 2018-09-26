<?php

use function \K\{db};

test(function ($t) {
    $t->equals(db()->quoteCol('id'), '"id"', "quote column with double quotes");
    $t->equals(db()->quoteCol('someCol'), '"someCol"', "quote column with capital letters");
});

test(function ($t) {
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
