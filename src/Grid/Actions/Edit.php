<?php

namespace MenqzAdmin\Admin\Grid\Actions;

use MenqzAdmin\Admin\Actions\RowAction;

class Edit extends RowAction
{
    public $icon = 'icon-pen';

    public $customClass = 'grid-edit-btn';

    /**
     * @return array|null|string
     */
    public function name()
    {
        return __('admin.edit');
    }

    /**
     * @return string
     */
    public function href()
    {
        $queryString = $this->parent->getQueryString();
        return sprintf(
            '%s/%s/edit%s',
            $this->getResource(),
            $this->getKey(),
            $queryString ? ('?'.$queryString) : ''
        );
        // return "{$this->getResource()}/{$this->getKey()}/edit";
    }
}
