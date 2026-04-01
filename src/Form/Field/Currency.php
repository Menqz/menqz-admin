<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Helpers\Helper;

class Currency extends Text
{
    /**
     * @var string
     */
    protected $symbol = 'R$';

    /**
     * @see https://github.com/RobinHerbots/Inputmask#options
     *
     * @var array
     */
    protected $options = [
        'alias'              => 'currency',
        'prefix'             => '',
        'groupSeparator'     => '',
        'radixPoint'         => ',',
        'autoGroup'          => false,
        'digits'             => 2,
        'digitsOptional'     => true,
        'rightAlign'         => true,
    ];

    /**
     * Set symbol for currency field.
     *
     * @param string $symbol
     *
     * @return $this
     */
    public function symbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Set digits for input number.
     *
     * @param int $digits
     *
     * @return $this
     */
    public function digits($digits)
    {
        return $this->options(compact('digits'));
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($value)
    {
        $value = parent::prepare($value);

        return (float) Helper::currencyToFloat($value);
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $this->value(Helper::formatCurrency($this->value));
        $this->inputmask($this->options);

        $script = '<script>' . $this->script . '</script>';
        $this->script = '';

        $this->prepend($this->symbol);
        $this->style('max-width', '160px');

        $render = parent::render();
        return $render . $script;
    }
}
