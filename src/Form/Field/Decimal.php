<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Helpers\Helper;

class Decimal extends Text
{
    /**
     * @see https://github.com/RobinHerbots/Inputmask#options
     *
     * @var array
     */
    protected $options = [
        'alias'      => 'decimal',
        'rightAlign' => true,
        'prefix'             => '',
        'groupSeparator'     => '',
        'radixPoint'         => ',',
    ];

    /**
     * {@inheritdoc}
     */
    public function prepare($value)
    {
        $value = parent::prepare($value);

        return (float) Helper::currencyToFloat($value);
    }

    public function render()
    {
        $precision = 2;
        if (isset($this->options['digits'])) {
            $precision = $this->options['digits'];
        }
        $this->value(Helper::formatCurrency($this->value, $precision));
        $this->inputmask($this->options);

        $script = '<script>' . $this->script . '</script>';
        $this->script = '';

        $this->prepend('<i class="'.$this->icon.'"></i>');
        $this->style('max-width', '160px');

        $render = parent::render();
        return $render . $script;
    }
}
