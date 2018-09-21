<?php

require_once __DIR__ . '/vendor/autoload.php';

K\option('db', \K\service(function () {
    return new \K\Db(getenv('DATABASE_URL'));
}));
