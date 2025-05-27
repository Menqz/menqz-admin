<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Form\Field;

class Button extends Field
{
    protected $class = 'btn-primary';

    public function info()
    {
        $this->class = 'btn-info';

        return $this;
    }

    public function on($event, $callback)
    {
        $this->script = <<<JS
        document.querySelector('{$this->getElementClassSelector()}').addEventListener('$event', function() {
            $callback
        });
JS;
    }
}
