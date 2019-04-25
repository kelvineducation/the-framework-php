<?php

test("writing json response", function ($t) {
    $writer = new \The\Tests\ResponseWriterStub();

    \The\json($writer, ['success' => true]);
    
    $t->equals(
        $writer->headers['Content-Type'],
        'application/json;charset=utf-8',
        "response header content-type is json"
    );
    $t->equals($writer->data, '{"success":true}', "response body is json");
});

test("writing json error response", function ($t) {
    $writer = new \The\Tests\ResponseWriterStub();
    
    $status = 500;
    \The\json($writer, [], $status);
    
    $t->equals($writer->status, $status, sprintf("status code is set to %s", $status));
});
