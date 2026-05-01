<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Admin;

class Date extends Text
{
    protected $format = 'Y-m-d';

    protected $defaults = [
        'weekNumbers'   => false,
        'time_24hr'     => true,
        'enableSeconds' => true,
        'enableTime'    => false,
        'allowInput'    => true,
        'noCalendar'    => false,
    ];

    public function format($format)
    {
        $this->format = $format;

        return $this;
    }

    public function getAlternativeFormat()
    {
        return Admin::getAlternativeDateFormat() ?? null;
    }

    public function prepare($value)
    {
        $value = parent::prepare($value);

        // allows the value to be empty
        if (empty($value)) {
            $value = null;
        }

        // if the field is not present in the request it should not be processed
        if (empty($value) && !request()->has($this->column)) {
            $value = false;
        }

        return $value;
    }

    public function check_format_options()
    {
        $format = $this->options['dateFormat'];
        if (substr($format, -1) != 'S') {
            $this->options['enableSeconds'] = false;
        }
        if (strpos($format, 'H') !== false) {
            $this->options['enableTime'] = true;
        }
    }

    public function render()
    {
        if ($this->getAlternativeFormat() !== null) {
            $this->options['altInput'] = true;
            $this->options['altFormat'] = $this->getAlternativeFormat();
        }

        $this->options = array_merge($this->defaults, $this->options);
        $this->options['dateFormat'] = $this->format;
        $this->options['locale'] = array_key_exists('locale', $this->options) ? $this->options['locale'] : config('app.locale');
        $this->options['allowInputToggle'] = true;
        $this->options['clickOpens'] = isset($this->attributes['readonly']) ? !$this->attributes['readonly'] : true;
        $this->options['allowInput'] = isset($this->attributes['readonly']) ? !$this->attributes['readonly'] : true;

        $this->check_format_options();

        $varPickr = $this->id . '_pickr';

        // dd($this->options);
        $configs = json_encode($this->options);
        $this->script = '';
        $script = <<<HTML
            <script>
                var {$varPickr} = flatpickr('{$this->getElementClassSelector()}',{$configs});
            </script>
        HTML;

        $this->prepend('<i class="icon-calendar fa-fw"></i>');
        $this->style('max-width', '160px');

        $render = parent::render();
        return $render.$script;
    }
}
