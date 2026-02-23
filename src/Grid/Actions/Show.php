<?php

namespace MenqzAdmin\Admin\Grid\Actions;

use MenqzAdmin\Admin\Actions\RowAction;

class Show extends RowAction
{
    public $icon = 'icon-eye';

    public $customClass = 'grid-show-btn';

    /**
     * @return array|null|string
     */
    public function name()
    {
        return __('admin.show');
    }

    /**
     * @return string
     */
    public function href()
    {
        $queryString = $this->parent->getQueryString();
        return sprintf(
            '%s/%s%s',
            $this->getResource(),
            $this->getKey(),
            $queryString ? ('?'.$queryString) : ''
        );
        // return "{$this->getResource()}/{$this->getKey()}";
    }
}
