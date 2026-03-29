<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Form\Field;

class DateRange extends Field
{
    protected $format = 'Y-m-d';

    protected $defaults = [
        'weekNumbers'   => true,
        'time_24hr'     => true,
        'enableSeconds' => true,
        'enableTime'    => false,
        'allowInput'    => true,
        'noCalendar'    => false,
    ];

    /**
     * Column name.
     *
     * @var array
     */
    protected $column = [];

    protected static $js = [
        '/vendor/menqz-admin/flatpickr/plugins/rangePlugin.js',
    ];

    public function __construct($column, $arguments)
    {
        $this->column['start'] = $column;
        $this->column['end'] = $arguments[0];

        array_shift($arguments);

        $this->label = $this->formatLabel($arguments);
        $this->id = $this->formatId($this->column);

        $this->options(['format' => $this->format]);
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

    /**
     * {@inheritdoc}
     */
    public function prepare($value)
    {
        $value = parent::prepare($value);
        if ($value === '') {
            $value = null;
        }

        return $value;
    }

    public function render()
    {
        $this->options = array_merge($this->defaults, $this->options);
        $this->options['dateFormat'] = $this->format;
        $this->options['locale'] = array_key_exists('locale', $this->options) ? $this->options['locale'] : config('app.locale');
        $this->options['allowInputToggle'] = true;
        $this->options['plugins'] = '__replace_me__';
        $this->options['clickOpens'] = isset($this->attributes['readonly']) ? !$this->attributes['readonly'] : true;

        $this->check_format_options();

        $options_start = json_encode($this->options);
        $options_start = str_replace('"__replace_me__"', '[new rangePlugin({ input: "'.$this->getElementClassSelector()['end'].'"})]', $options_start);

        //$options_end = json_encode($this->options);
        $varPickr = $this->id . '_pickr';
        $this->script = '';
        $script = <<<HTML
            <script>
                var {$varPickr} = flatpickr('{$this->getElementClassSelector()['start']}',{$options_start});
            </script>
        HTML;

        $render = parent::render();
        return $render.$script;
    }
}
