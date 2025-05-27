<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Form\Field;

class Nullable extends Field
{
    public function __construct()
    {
    }

    public function __call($method, $parameters)
    {
        return $this;
    }
}
