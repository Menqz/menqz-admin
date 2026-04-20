<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Admin;

class Time extends Date
{
    protected $format = 'H:i:S';

    public function getAlternativeFormat()
    {
        return Admin::getAlternativeTimeFormat() ?? null;
    }

    public function render()
    {
        $this->prepend('<i class="icon-clock"></i>');
        $this->style('max-width', '160px');
        $this->options['noCalendar'] = true;

        return parent::render();
    }
}
