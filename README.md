# the-framework-php

## Dev Setup

*composer.json*
```
    "repositories": [
        {
            "type": "path",
            "url": "/app/composer-dev/the-framework-php",
            "options": {"symlink": true}
        }
    ]
```

*Checkout dev version of framework*
```
cd your_project
mkdir composer-dev
cd composer-dev
git clone git@github.com:kelvineducation/the-framework-php.git
cd ..
docker-compose exec php-fpm composer require kelvineducation/the-framework-php:@dev
```

* Make edits to framework
* Test edits
* Release new version of framework (release with tag)
* Update composer.json to match new version of framework
```
"require": {
	"kelvineducation/the-framework-php": "^4.0.0"
}
```
* Test changes in app
* Release new app changes (merge master)


## Required Bootstrap elements
```
require_once __DIR__ . '/vendor/autoload.php';

use function The\option;
use function The\service;
use The\Request;

option('root_dir', __DIR__);
option('views_dir', option('root_dir') . '/views');

option('request', service(function () {
    return new Request;
}));

option('honeybadger', service(function () {
// Technically just a class that implements notify()
// Null class example
return class {
  function notify() {
    // do nothing
  }
}
}));

option('instrumentor', service(function () {
// Must implement The\InstrumentorInterface
// Null class example
return class {
  function startHttpRequest($request) {
    // do nothing
  }
  function endHttpRequest($response) {
    // do nothing
  }
}
}));
```


## Example Bootstrap with honeybadger/prometheus
```
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Honeybadger\Honeybadger;
use function The\option;
use function The\service;
use The\Request;

option('root_dir', __DIR__);
option('views_dir', option('root_dir') . '/views');

option('request', service(function () {
    return new Request;
}));

option('honeybadger', service(function() {
    $honeybadger = Honeybadger::new([
        'api_key'          => getenv('HONEYBADGER_API_KEY') ?: null,
        'environment_name' => getenv('APP_ENV') ?: 'unknown',
        'handlers'         => ['exception' => false, 'error' => false],
    ]);
    return $honeybadger;
}));

// instrumentor
```