<?php

use The\Form;
use The\Form\Field;
use The\Tests\Test;

test("form addField does not allow duplicate field names", function (Test $t) {
    $request = new The\Tests\RequestStub();
    $form = new Form($request, 'testing', '');
    $form->addText('testing');

    $t->throws(function() use ($form) {
        $form->addText('testing');
    }, '/FormException/');
});

test("form select html", function (Test $t) {
  $field = new Field('pizza', 'select', 'Pizza');
  $html = <<<'HTML'
    <select id="pizza" name="pizza">

    </select>
    HTML;
  $t->equals($field->toHtml(), $html);
});

test("form select html with options", function (Test $t) {
  $field = new Field('pizza', 'select', 'Pizza');
  $field->setOptions(['cheese' => 'Cheese', 'pepperoni' => 'Pepperoni']);
  $html = <<<'HTML'
    <select id="pizza" name="pizza">
    <option value="cheese">Cheese</option>

    <option value="pepperoni">Pepperoni</option>

    </select>
    HTML;
  $t->equals($field->toHtml(), $html);
});

test("form select html with selected options", function (Test $t) {
  $field = new Field('pizza', 'select', 'Pizza');
  $field->setOptions(['cheese' => 'Cheese', 'pepperoni' => 'Pepperoni']);
  $field->setSelected(['pepperoni']);
  $html = <<<'HTML'
    <select id="pizza" name="pizza">
    <option value="cheese">Cheese</option>

    <option value="pepperoni" selected="selected">Pepperoni</option>

    </select>
    HTML;
  $t->equals($field->toHtml(), $html);
});

//create test that tests for opt groups
test("form select html with selected options", function (Test $t) {
  $field = new Field('pizza', 'select', 'Pizza');
  $field->setOptions([
    'cheese' => 'Cheese',
    'pepperoni' => 'Pepperoni',
    'Sausages' => [
      'sausage1' => 'Sausage 1',
      'sausage2' => 'Sausage 2'
    ],
  ]);
  $field->setSelected(['pepperoni']);

  $html = <<<'HTML'
    <select id="pizza" name="pizza">
    <option value="cheese">Cheese</option>

    <option value="pepperoni" selected="selected">Pepperoni</option>

    <optgroup label="Sausages">
    <option value="sausage1">Sausage 1</option>
    <option value="sausage2">Sausage 2</option>
    </optgroup>

    </select>
    HTML;
  $t->equals($field->toHtml(), $html);
});
