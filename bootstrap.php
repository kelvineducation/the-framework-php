<?php

require_once __DIR__ . '/vendor/autoload.php';

use function K\{option, service, url};
use K\{Db, Model, GoogleAuth};

option('db', service(function () {
    return new Db(getenv('DATABASE_URL'));
}));

option('google_auth_redirect_uri', service(function () {
    return getenv('LOGIN_PROXY') ? getenv('LOGIN_PROXY') . '/auth'
        : url('/login/auth');
}));

option('google_auth_provider', service(function () {
    return new \League\OAuth2\Client\Provider\Google([
        'clientId'     => getenv('GOOGLE_ID'),
        'clientSecret' => getenv('GOOGLE_SECRET'),
        'redirectUri'  => option('google_auth_redirect_uri'),
        'useOidcMode'  => true,
    ]);
}));

option('google_auth', service(function () {
    return new GoogleAuth(option('google_auth_provider'), getenv('LOGIN_PROXY'));
}));

Model::setDb(function() {
    return option('db');
});
