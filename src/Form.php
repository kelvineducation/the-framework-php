<?php

namespace K;

use K\Form\{FormException, Field, RequestInterface};

class Form
{
    const FIELD_SUBMITTED = '__submitted';

    private $request;
    private $name;
    private $action;
    private $method;
    private $fields = [];
    private $hidden_fields = [];
    private $is_valid_fn;
    private $errors = [];

    public function __construct(RequestInterface $request, string $name, string $action, string $method = 'get')
    {
        $this->request = $request;
        $this->name = $name;
        $this->action = $action;
        $this->method = $method;
        $this->addHidden(self::FIELD_SUBMITTED, 't');
    }

    public function addText(string $name, string $value = '', string $label = '')
    {
        $field = new Field($name, 'text', $label);
        $field->setAttribute('class', 'form-control');
        $field->setValue($this->request->getParam($name, $value));
        $this->addField($field);
        return $field;
    }

    public function addSelect(string $name, array $options = [], array $selected = [], string $label = '')
    {
        $field = new Field($name, 'select', $label);
        $field->setAttribute('class', 'form-control');
        $selected = $this->request->getParam($name, $selected);
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        $field->setSelected($selected);
        $field->setOptions($options);
        $this->addField($field);
        return $field;
    }

    public function addEmail(string $name, string $value = '', string $label)
    {
        $field = new Field($name, 'email', $label);
        $field->setAttribute('class', 'form-control');
        $field->setValue($this->request->getParam($name, $value));
        $this->addField($field);
        return $field;
    }

    public function addSubmit(string $name, string $value)
    {
        $field = new Field($name, 'submit');
        $field->setAttribute('class', 'btn');
        $field->setValue($value);
        $this->addField($field);
        return $field;
    }

    public function addHidden(string $name, string $value)
    {
        $field = new Field($name, 'hidden');
        $field->setValue($value);
        $this->addField($field);
        $this->hidden_fields[$field->getName()] = $field;
        return $field;
    }

    public function wasSubmitted(): bool
    {
        return ($this->getData(self::FIELD_SUBMITTED) === 't');
    }

    public function addError(string $error)
    {
        $this->errors[] = $error;
    }

    public function setIsValidCallback(callable $fn)
    {
        $this->is_valid_fn = $fn;
    }

    public function isValid(): bool
    {
        foreach ($this->fields as $field_name => $field) {
            if ($field->required === true
                && empty($this->getData($field_name))
            ) {
                return false;
            }
        }
        if ($this->is_valid_fn) {
            return call_user_func($this->is_valid_fn, $this);
        }
        return true;
    }

    public function getData(string $field_name = '')
    {
        $data = [];
        foreach (array_keys($this->fields) as $name) {
            $data[$name] = $this->request->getParam($name);
        }
        if ($field_name !== '') {
            return $data[$field_name];
        }
        return $data;
    }

    public function field(string $name)
    {
        return $this->fields[$name];
    }

    public function toHtml(string $field_name): string
    {
        $field = $this->field($field_name);
        $attrs = ['class' => 'form-group'];
        if ($field->required === true) {
            $attrs['class'] = $attrs['class'] . ' required';
        }
        $attrs = $this->htmlify($attrs);
        return <<<HTML
<div {$attrs}>
    {$field->toHtmlWithLabel()}
</div>

HTML;
    }

    public function beginHtml()
    {
        return sprintf(
            "<form %s>\n%s\n%s\n",
            $this->htmlify([
                'name'       => $this->name,
                'action'     => $this->action,
                'method'     => $this->method,
                'novalidate' => 'novalidate',
                'class'      => 'needs-validation',
            ]),
            implode("\n", array_map(function ($hidden_field) {
                return $hidden_field->toHtml();
            }, $this->hidden_fields)),
            $this->getErrorHtml()
        );
    }

    public function endHtml()
    {
        return "</form>";
    }

    private function getErrorHtml(): string
    {
        if (empty($this->errors)) {
            return '';
        }

        $errors_html = implode("\n", array_map(function ($err) {
            return "<li>{$this->h($err)}</li>";
        }, $this->errors));
        return <<<HTML
<div class="alert alert-danger" role="alert">
    <ul class="list-unstyled mb-0">
        {$errors_html}
    </ul>
</div>
HTML;
    }

    private function htmlify(array $attributes)
    {
        return implode(" ", array_map(function($attr, $val) {
            return sprintf('%s="%s"', $attr, $this->h($val));
        }, array_keys($attributes), $attributes));
    }

    private function h(string $val): string
    {
        return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
    }

    private function addField(Field $field)
    {
        if (isset($this->fields[$field->getName()])) {
            throw new FormException(sprintf(
                "Field with name '%s' already added to form '%s'",
                $field->getName(),
                $this->name
            ));
        }
        $this->fields[$field->getName()] = $field;
    }
}
