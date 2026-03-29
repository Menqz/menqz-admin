<?php

namespace MenqzAdmin\Admin\Form\Field;

class Datetime extends Date
{
    protected $format = 'Y-m-d H:i:S';

    public function render()
    {
        $this->style('max-width', '160px');

        return parent::render();
    }
}
