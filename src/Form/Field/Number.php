<?php

namespace MenqzAdmin\Admin\Form\Field;

use  MenqzAdmin\Admin\Form\Field\Traits\HasNumberModifiers;

class Number extends Text
{
    use HasNumberModifiers;

    protected $view = 'admin::form.number';

    public function render()
    {
        $this->defaultAttribute('type', 'number');
        $this->append("<i class='icon-plus plus'></i>");
        $this->prepend("<i class='icon-minus minus'></i>");
        $this->default($this->default);
        $script = '';
        if (
            empty($this->attributes['readonly']) &&
            empty($this->attributes['disabled'])
        ) {
            $this->script = '';
            $varNumber = $this->id . '_number';
            $script = <<<HTML
                <script>
                    waitForElement('{$this->getElementClassSelector()}', function () {
                        var {$varNumber} = new NumberInput(document.querySelector('{$this->getElementClassSelector()}'));
                    });
                </script>
            HTML;
        }

        $this->style('max-width', '120px');

        $render = parent::render();
        return $render . $script;
    }
}
