<?php

use function The\{html};

test(function ($t) {
    $writer = new \The\Tests\ResponseWriterStub();
    html(
        $writer,
        __DIR__ . '/html_example.phtml',
        __DIR__ . '/html_example_layout.phtml',
        ['name' => 'Kelvin'],
        200
    );

    $t->equals($writer->status, 200, "status code is set");
    $t->equals($writer->headers, [
        'Content-Type' => 'text/html; charset=utf-8',
    ], "content type header is set");
    $t->equals(
        $writer->data,
        "Header\nBody Kelvin\nFooter\n",
        "parameters are passed and set"
    );
});
