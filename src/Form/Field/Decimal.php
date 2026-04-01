<?php

namespace MenqzAdmin\Admin\Form\Field;

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
    ];

    public function render()
    {
        $this->inputmask($this->options);

        $script = '<script>' . $this->script . '</script>';
        $this->script = '';

        $this->prepend('<i class="'.$this->icon.'"></i>');
        $this->style('max-width', '160px');

        $render = parent::render();
        return $render . $script;
    }
}
