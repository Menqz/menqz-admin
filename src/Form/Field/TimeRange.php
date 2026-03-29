<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Form;
use MenqzAdmin\Admin\Form\Field;

class TimeRange extends Field
{
    protected $format = 'H:i:S';

    protected $defaults = [
        'time_24hr'     => true,
        'enableSeconds' => true,
        'enableTime'    => true,
        'allowInput'    => true,
        'noCalendar'    => true,
    ];

    /**
     * Column name.
     *
     * @var array
     */
    protected $column = [];

    protected static $js = [
        '/vendor/menqz-admin/flatpickr/plugins/minMaxTimePlugin.js',
    ];

    public function __construct($column, $arguments)
    {
        $this->column['start'] = $column;
        $this->column['end'] = $arguments[0];

        array_shift($arguments);

        $this->label = $this->formatLabel($arguments);
        $this->id = $this->formatId($this->column['start']);
    }

    public function check_format_options()
    {
        $format = $this->options['format'];
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

    public function getAlternativeFormat()
    {
        return Form::getAlternativeTimeFormat() ?? null;
    }

    public function render()
    {
        if ($this->getAlternativeFormat() !== null) {
            $this->options['altInput'] = true;
            $this->options['altFormat'] = $this->getAlternativeFormat();
        }

        $this->options = array_merge($this->defaults, $this->options);
        $this->options['format'] = $this->format;
        $this->options['locale'] = array_key_exists('locale', $this->options) ? $this->options['locale'] : config('app.locale');
        $this->options['allowInputToggle'] = true;
        $this->options['plugins'] = '__replace_me__';

        $this->check_format_options();

        $options = json_encode($this->options);
        $func = <<<JS
                [
                    new minMaxTimePlugin({
                        minTime: "00:00:00",
                        maxTime: "23:59:59"
                    })
                ],onChange : function(selectedDates, dateStr, instance){
                    let endVal = document.querySelector('{$this->getElementClassSelector()['end']}').value;
                    if (endVal != ''){
                        {$this->column['start']}_fp_inst.setMaxTime(endVal);
                    }
                    let startVal = document.querySelector('{$this->getElementClassSelector()['start']}').value;
                    if (startVal != ''){
                        {$this->column['end']}_fp_inst.setMinTime(startVal);
                    }
                }
            JS;

        $str_options = str_replace('"__replace_me__"', $func, $options);

        $script = <<<JS
            var {$this->column['start']}_fp_inst = flatpickr('{$this->getElementClassSelector()['start']}',{$str_options});
            var {$this->column['end']}_fp_inst = flatpickr('{$this->getElementClassSelector()['end']}',{$str_options});
        JS;

        $render = parent::render();
        return $render.$script;
    }
}
