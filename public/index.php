<?php

require_once __DIR__ . '/../bootstrap.php';

use K\{App, ApiContext, WebContext};

if (strpos($_SERVER['REQUEST_URI'], '/api') === 0) {
    $context = ApiContext::init();
} else {
    $context = WebContext::init();
}

App::run($context);
