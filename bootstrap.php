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

option('session_save_handler', 'redis');
option('session_save_path', service(function () {
    $redis_url = getenv('REDIS_URL');
    $save_path = sprintf(
        "tcp://%s:%d",
        parse_url($redis_url, PHP_URL_HOST),
        parse_url($redis_url, PHP_URL_PORT)
    );
    if ($password = parse_url($redis_url, PHP_URL_PASS)) {
        $save_path .= '?' . http_build_query(['auth' => $password]);
    }

    return $save_path;
}));

option('honeybadger', service(function() {
    return \Honeybadger\Honeybadger::new([
        'api_key'          => getenv('HONEYBADGER_API_KEY'),
        'environment_name' => getenv('APP_ENV') ?: 'unknown',
        'handlers'         => ['exception' => false, 'error' => false],
    ]);
}));

Model::setDb(function() {
    return option('db');
});
