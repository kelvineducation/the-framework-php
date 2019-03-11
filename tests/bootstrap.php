<?php

include __DIR__ . '/../vendor/autoload.php';

use function K\{option, service};
use K\{Db, Model};

option('db', service(function () {
    return new Db(getenv('DATABASE_URL'));
}));

Model::setDb(function() {
    return option('db');
});
