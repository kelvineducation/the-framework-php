<?php

include __DIR__ . '/../vendor/autoload.php';

use function The\{option, service};
use The\{Db, Model};

option('db', service(function () {
    return new Db(getenv('DATABASE_URL'));
}));

Model::setDb(function() {
    return option('db');
});
