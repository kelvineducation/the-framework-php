<?php

namespace K\Form;

class Field
{
    public $label = '';
    public $required = false;
    public $is_disabled = false;

    private $name;
    private $type;
    private $value;
    private $selected = [];
    private $options = [];
    private $attributes = [];
    private $invalid_feedback = '';
    private $help_text = '';

    private $is_multiple = false;

    public function __construct(string $name, string $type, string $label = '')
    {
        $this->name = $name;
        $this->type = $type;
        $this->label = $label ?: ucwords(str_replace('_', ' ', $name));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->attributes['id'] ?? $this->getName();
    }

    public function setValue(string $value)
    {
        $this->value = $value;
    }

    public function setSelected(array $selected)
    {
        $this->selected = array_combine($selected, $selected);
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function makePrimary()
    {
        $this->addClass("btn-primary");
    }

    public function setMultiple()
    {
        $this->is_multiple = true;
    }

    public function setAttribute(string $attribute, string $value): Field
    {
        $this->attributes[$attribute] = $value;
        return $this;
    }

    public function addClass(string $new_class)
    {
        $class = $this->attributes['class'] ?? '';
        $this->setAttribute('class', "{$class} {$new_class}");
    }

    public function setHelpText(string $help_text)
    {
        $this->help_text = $help_text;
    }

    public function setInvalid(string $invalid_feedback = '')
    {
        $this->addClass('is-invalid');
        if ($invalid_feedback) {
            $this->invalid_feedback = $invalid_feedback;
        }
    }

    public function setRequired(string $invalid_feedback = '')
    {
        $this->required = true;
        $this->invalid_feedback = $invalid_feedback ?: "{$this->label} is required.";
    }

    public function setDisabled()
    {
        $this->is_disabled = true;
    }

    public function toHtml(): string
    {
        if ($this->type === 'text' || $this->type === 'email'
            || $this->type === 'submit' || $this->type === 'hidden'
            || $this->type === 'date' || $this->type === 'number'
            || $this->type === 'checkbox'
        ) {
            $default_attributes = [
                'type'  => $this->type,
                'id'    => $this->getId(),
                'name'  => $this->getName(),
                'value' => $this->value,
            ];
            if ($this->required === true) {
                $default_attributes['required'] = '';
            }
            if ($this->is_disabled === true) {
                $default_attributes['disabled'] = '';
            }
            if (strstr($this->getId(), '[]') !== false) {
                unset($default_attributes['id']);
            }
            $attributes = array_merge($default_attributes, $this->attributes);

            return sprintf("<input %s>", $this->htmlify($attributes));
        } elseif ($this->type === 'select') {
            $default_attributes = [
                'id'    => $this->getId(),
                'name'  => $this->getName() . ($this->is_multiple ? '[]' : ''),
            ];
            if ($this->required === true) {
                $default_attributes['required'] = '';
            }
            if ($this->is_disabled === true) {
                $default_attributes['disabled'] = '';
            }
            if ($this->is_multiple) {
                $default_attributes['multiple'] = '';
            }
            $attributes = array_merge($default_attributes, $this->attributes);

            return sprintf(
                "<select %s>\n%s\n</select>",
                $this->htmlify($attributes),
                implode("\n", array_map(function ($val, $desc) {
                    $attrs = ['value' => $val];
                    if (array_key_exists($val, $this->selected)) {
                        $attrs['selected'] = 'selected';
                    }
                    return sprintf(
                        "<option %s>%s</option>\n",
                        $this->htmlify($attrs),
                        $desc
                    );
                }, array_keys($this->options), $this->options))
            );
        }  elseif ($this->type === 'radios' || $this->type === 'checkboxes') {
            $attributes = $this->attributes;
            $type = ($this->type === 'checkboxes' ? 'checkbox' : 'radio');

            return implode("\n", array_map(function ($val, $desc) use ($type) {
                $container_attrs = ['class' => "custom-control custom-{$type}"];
                $input_attrs = [
                    'name'  => $this->name . ($this->type === 'checkboxes' ? '[]' : ''),
                    'type'  => $type,
                    'value' => $val,
                    'class' => 'custom-control-input',
                    'id'    => "{$this->name}-{$val}"
                ];
                $label_attrs = [
                    'class' => 'custom-control-label',
                    'for'   => "{$this->name}-{$val}"
                ];
                if (array_key_exists($val, $this->selected)) {
                    $input_attrs['checked'] = 'checked';
                }
                return sprintf(
                    "<div %s>\n<input %s>\n<label %s>%s</label></div>\n",
                    $this->htmlify($container_attrs),
                    $this->htmlify($input_attrs),
                    $this->htmlify($label_attrs),
                    $desc
                );
            }, array_keys($this->options), $this->options));
        } elseif ($this->type === 'textarea') {
            $default_attributes = [
                'id'    => $this->getId(),
                'name'  => $this->getName(),
            ];
            if ($this->required === true) {
                $default_attributes['required'] = '';
            }
            if ($this->is_disabled === true) {
                $default_attributes['disabled'] = '';
            }
            $attributes = array_merge($default_attributes, $this->attributes);

            return sprintf("<textarea %s>%s</textarea>",
                $this->htmlify($attributes), $this->value);
        }

        return '';
    }

    public function toHtmlWithLabel(): string
    {
        if ($this->type === 'submit') {
            return $this->toHtml();
        }

        $html = <<<HTML
<label for="{$this->h($this->getId())}">{$this->h($this->label)}</label>
{$this->toHtml()}
HTML;
        if ($this->help_text !== '') {
            $html .= <<<HTML
<small class="form-text text-muted">{$this->h($this->help_text)}</small>
HTML;
        }
        if ($this->required === true) {
            $html .= <<<HTML
<div class="invalid-feedback">{$this->invalid_feedback}</div>
HTML;
        }

        return $html;
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
}
