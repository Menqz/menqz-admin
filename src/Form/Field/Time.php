<?php

namespace MenqzAdmin\Admin\Form\Field;

class Time extends Date
{
    protected $format = 'H:i:S';

    public function render()
    {
        $this->prepend('<i class="icon-clock"></i>');
        $this->style('max-width', '160px');
        $this->options['noCalendar'] = true;

        return parent::render();
    }
}
