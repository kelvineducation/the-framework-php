<?php

require_once __DIR__ . '/../bootstrap.php';

use K\{Api, Web};

if (strpos($_SERVER['REQUEST_URI'], '/api') === 0) {
    Api::run();
} else {
    Web::run();
}
