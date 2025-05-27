<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Form\Field;

class Display extends Field
{
    public function prepare($value)
    {
        return $this->original();
    }
}
