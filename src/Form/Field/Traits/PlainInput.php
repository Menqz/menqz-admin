<?php

namespace MenqzAdmin\Admin\Form\Field\Traits;

trait PlainInput
{
    /**
     * @var string
     */
    protected $prepend;

    /**
     * @var string
     */
    protected $append;

    /**
     * @param mixed $string
     *
     * @return $this
     */
    public function prepend($string)
    {
        if (is_null($this->prepend)) {
            $this->prepend = $string;
        }

        return $this;
    }

    /**
     * @param mixed $string
     *
     * @return $this
     */
    public function append($string)
    {
        if (is_null($this->append)) {
            $this->append = $string;
        }

        return $this;
    }

    /**
     * @return void
     */
    protected function initPlainInput()
    {
        if (empty($this->view)) {
            $this->view = 'admin::form.input';
        }
    }

    /**
     * @param string $attribute
     * @param string $value
     *
     * @return $this
     */
    protected function defaultAttribute($attribute, $value)
    {
        if (!array_key_exists($attribute, $this->attributes)) {
            $this->attribute($attribute, $value);
        }

        return $this;
    }
}
