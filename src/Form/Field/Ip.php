<?php

namespace MenqzAdmin\Admin\Form\Field;

class Ip extends Text
{
    protected $rules = 'nullable|ip';

    /**
     * @see https://github.com/RobinHerbots/Inputmask#options
     *
     * @var array
     */
    protected $options = [
        'alias' => 'ip',
    ];

    public function render()
    {
        $this->inputmask($this->options);

        $script = '<script>' . $this->script . '</script>';
        $this->script = '';

        $this->prepend('<i class="icon-laptop fa-fw"></i>');
        $this->style('max-width', '160px');

        $render = parent::render();
        return $render . $script;
    }
}
