<?php

use The\Inflector;
use The\Tests\Test;

test('pascal to snake case', function (Test $t) {
    $cases = [
        'SomeTest' => 'some_test',
        'ThisXML'  => 'this_x_m_l',
        'AB'       => 'a_b',
    ];
    foreach ($cases as $actual => $expected) {
        $actual = Inflector::pascalToSnakeCase($actual);
        $t->equals($actual, $expected);
    }
});

test('snake to pascal case', function (Test $t) {
    $cases = [
        'some_test'  => 'SomeTest',
        'this_x_m_l' => 'ThisXML',
        'a_b'        => 'AB',
    ];
    foreach ($cases as $actual => $expected) {
        $actual = Inflector::snakeToPascalCase($actual);
        $t->equals($actual, $expected);
    }
});

test('urlify page class', function (Test $t) {
    $cases = [
        'Home'                       => 'home',
        'PulseEdit'                  => 'pulse_edit',
        'App\Pages\PulseShowPage'    => 'pulse_show',
        '\App\Pages\PulseDeletePage' => 'pulse_delete',
        'ApiV1Extension'             => 'api_v1_extension',
    ];
    foreach ($cases as $actual => $expected) {
        $actual = Inflector::urlifyPage($actual);
        $t->equals($actual, $expected);
    }
});

test('pageify', function (Test $t) {
    $cases = [
        'home'             => '\App\Pages\HomePage',
        'pulse_edit'       => '\App\Pages\PulseEditPage',
        'api_v1_extension' => '\App\Pages\ApiV1ExtensionPage',
    ];
    foreach ($cases as $actual => $expected) {
        $actual = Inflector::pageify($actual);
        $t->equals($actual, $expected);
    }
});

test('page to template', function (Test $t) {
    $cases = [
        'Home'                       => 'home',
        'PulseEdit'                  => 'pulse_edit',
        'App\Pages\PulseShowPage'    => 'pulse_show',
        '\App\Pages\PulseDeletePage' => 'pulse_delete',
        'ApiV1Extension'             => 'api_v1_extension',
    ];
    foreach ($cases as $actual => $expected) {
        $actual = Inflector::templateifyPage($actual);
        $t->equals($actual, $expected);
    }
});
