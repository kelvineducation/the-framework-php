<?php

require_once __DIR__ . '/../bootstrap.php';

ini_set('session.save_handler', option('session_save_handler'));
ini_set('session.save_path', option('session_save_path'));

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/login', '\K\Pages\LoginPage');
    $r->addRoute('GET', '/login/auth', '\K\Pages\LoginAuthPage');
    $r->addRoute('GET', '/logout', '\K\Pages\LogoutPage');

    $r->addRoute('GET', '/', '\K\Pages\TimelinePage');
    $r->addRoute('GET', '/explore', '\K\Pages\ReportSummaryPage');
    $r->addRoute('GET', '/organizations', '\K\Pages\OrganizationsPage');

    $r->addRoute('GET', '/questions', '\K\Pages\QuestionsPage');
    $r->addRoute('POST', '/questions/add', '\K\Pages\QuestionAddPage');

    $r->addRoute('GET', '/pulses', '\K\Pages\PulsesPage');
    $r->addRoute('GET', '/pulses/remove', '\K\Pages\PulseRemovePage');
    $r->addRoute('POST', '/pulses/add', '\K\Pages\PulseAddPage');

    $r->addRoute('GET', '/api/v1/pulse', '\K\Pages\ApiPulsePage');
    $r->addRoute('POST', '/api/v1/pulse', '\K\Pages\ApiReceivePulsePage');
    $r->addRoute('OPTIONS', '/api/v1/pulse', '\K\Pages\ApiCorsPage');
});

// Fetch method and URI from somewhere
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$route_info = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $uri);

if ($route_info[0] == FastRoute\Dispatcher::NOT_FOUND) {
    $response = new \K\Response();
    $response->withStatus(404);
    $response->output();
    exit(0);
}

if ($route_info[0] == FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
    $response = new \K\Response();
    $response->withStatus(405);
    $response->output();
    exit(0);
}

$page_class = $route_info[1];
$vars = $route_info[2];
$request = new \K\Request();
call_user_func([$page_class, 'handleRequest'], $request, $vars);
