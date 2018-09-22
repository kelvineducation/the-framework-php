<?php

require_once __DIR__ . '/vendor/autoload.php';

use function K\{option, service, url};
use K\{Db, Model};

option('db', service(function () {
    return new Db(getenv('DATABASE_URL'));
}));

option('google_auth_provider', service(function () {
    return new \League\OAuth2\Client\Provider\Google([
        'clientId'     => getenv('GOOGLE_ID'),
        'clientSecret' => getenv('GOOGLE_SECRET'),
        'redirectUri'  => url('/login/auth'),
        'useOidcMode'  => true,
    ]);
}));

Model::setDb(function() {
    return option('db');
});
