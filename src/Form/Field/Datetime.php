<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Admin;

class Datetime extends Date
{
    protected $format = 'Y-m-d H:i:S';

    public function getAlternativeFormat()
    {
        return Admin::getAlternativeDatetimeFormat() ?? null;
    }

    public function render()
    {
        $this->style('max-width', '160px');

        return parent::render();
    }
}
