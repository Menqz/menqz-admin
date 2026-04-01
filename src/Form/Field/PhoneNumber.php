<?php

namespace MenqzAdmin\Admin\Form\Field;

class PhoneNumber extends Text
{
    /**
     * @see https://github.com/RobinHerbots/Inputmask#options
     *
     * @var array
     */
    protected $options = [
        'mask' => '99999999999',
    ];

    public function render()
    {
        $this->inputmask($this->options);

        $script = '<script>' . $this->script . '</script>';
        $this->script = '';

        $this->prepend('<i class="icon-phone fa-fw"></i>');
        $this->style('max-width', '160px');

        $render = parent::render();
        return $render . $script;
    }
}
