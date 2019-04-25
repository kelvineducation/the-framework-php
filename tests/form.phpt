<?php

use K\Form;
use K\Tests\Test;

test("form addField does not allow duplicate field names", function (Test $t) {
    $request = new K\Tests\RequestStub();
    $form = new Form($request, 'testing', '');
    $form->addText('testing');

    $t->throws(function() use ($form) {
        $form->addText('testing');
    }, '/FormException/');
});
