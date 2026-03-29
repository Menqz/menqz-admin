<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Form;

class DateMultiple extends Text
{
    protected $format = 'Y-m-d';

    public function format($format)
    {
        $this->format = $format;

        return $this;
    }

    public function prepare($value)
    {
        $value = parent::prepare($value);
        if ($value === '') {
            $value = null;
        }

        return $value;
    }

    public function render()
    {
        if (Form::getAlternativeDateFormat() !== null) {
            $this->options['altInput'] = true;
            $this->options['altFormat'] = Form::getAlternativeDateFormat();
        }

        $this->options['dateFormat'] = $this->format;
        $this->options['locale'] = array_key_exists('locale', $this->options) ? $this->options['locale'] : config('app.locale');
        $this->options['allowInputToggle'] = true;
        $this->options['mode'] = 'multiple';
        $this->options['plugins'] = "[
            ShortcutButtonsPlugin({
              button: {
                label: 'Clear',
              },
              onClick: (index, fp) => {
                fp.clear();
                fp.close();
              }
            })
          ]";

        $script = "flatpickr('{$this->getElementClassSelector()}',".json_encode($this->options).');';

        $this->prepend('<i class="icon-calendar"></i>')
            ->defaultAttribute('style', 'width: 100%');

        $render = parent::render();
        return $render.$script;
    }
}
