<?php

namespace MenqzAdmin\Admin\Form\Field\Traits;

use MenqzAdmin\Admin\Admin;
use MenqzAdmin\Admin\Form\Field;
use MenqzAdmin\Admin\Form\Field\ValuePicker;

/**
 * @mixin Field
 */
trait HasValuePicker
{
    /**
     * @var ValuePicker
     */
    protected $picker;

    /**
     * @param string $picker
     * @param string $column
     *
     * @return $this
     */
    public function pick($picker, $column = '')
    {
        $this->picker = new ValuePicker($picker, $column);

        return $this;
    }

    /**
     * @param string $picker
     * @param string $column
     * @param string $separator
     */
    public function pickMany($picker, $column = '', $separator = ',')
    {
        $this->picker = new ValuePicker($picker, $column, true, $separator);

        return $this;
    }

    /**
     * @param \Closure|null $callback
     *
     * @return $this
     */
    protected function mountPicker(\Closure $callback = null)
    {
        $this->picker && $this->picker->mount($this, $callback);

        return $this;
    }

    /**
     * @return string
     */
    public function getRules()
    {
        $rules = parent::getRules();
        if (!empty($rules)) {
            array_delete($rules, 'image');
        }

        return $rules;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    protected function renderFilePicker()
    {
        $this->mountPicker()
            ->setView('admin::form.filepicker')
            ->attribute('type', 'text')
            ->attribute('id', $this->id)
            ->attribute('name', $this->elementName ?: $this->formatName($this->column))
            ->attribute('value', old($this->elementName ?: $this->column, $this->value()))
            ->attribute('class', 'form-control '.$this->getElementClassString())
            ->attribute('placeholder', $this->getPlaceholder())
            ->addVariables([
                'preview' => $this->picker->getPreview(get_called_class()),
            ]);

        return Admin::component('admin::form.filepicker', $this->variables());
    }
}
