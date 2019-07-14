<?php

use The\PageUrl;
use The\Tests\Test;

test('url to page', function (Test $t) {
    $cases = [
        '/' => ['\\App\\Pages\\HomePage', []],
        '/test_report/test_id/5/other_id/10' => [
            '\\App\\Pages\\TestReportPage',
            ['test_id' => '5', 'other_id' => '10']
        ],
    ];
    foreach ($cases as $url => $expected) {
        [$expected_page_class, $expected_path_params] = $expected;
        $page_url = PageUrl::fromUrl($url);
        $t->equals($page_url->getPageClass(), $expected_page_class, 'page class matches');
        $t->equals($page_url->getPathParams(), $expected_path_params, 'path params match');
    }
});

test('page to url', function (Test $t) {
    $cases = [
        [['Home', [], [], ''], '/', 'home page'],
        [
            ['\App\Pages\TestReportPage', ['test_id' => '5'], ['q' => 'search'], 'results'],
            '/test_report/test_id/5?q=search#results',
            'full url'
        ],
        [
            ['Test', ['some param' => 'hello world'], ['q' => 'yup yup'], ''],
            '/test/some+param/hello+world?q=yup+yup',
            'url is encoded',
        ]
    ];
    foreach ($cases as $case) {
        [$page_url, $expected_url, $message] = $case;
        $actual = (new PageUrl($page_url[0], $page_url[1], $page_url[2], $page_url[3]))->__toString();
        $t->equals($actual, $expected_url, $message);
    }
});
