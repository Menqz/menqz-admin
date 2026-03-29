<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Form;

class Datetime extends Date
{
    protected $format = 'Y-m-d H:i:S';

    public function getAlternativeFormat()
    {
        return Form::getAlternativeDatetimeFormat() ?? null;
    }

    public function render()
    {
        $this->style('max-width', '160px');

        return parent::render();
    }
}
