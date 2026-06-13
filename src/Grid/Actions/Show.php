<?php

namespace MenqzAdmin\Admin\Grid\Actions;

use MenqzAdmin\Admin\Auth\CrudGate;
use MenqzAdmin\Admin\Auth\Database\CrudPermission;
use MenqzAdmin\Admin\Auth\PermissionMode;
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

    public function authorize($user, $model = null): bool
    {
        if (!PermissionMode::isCrud()) {
            return true;
        }

        $resource = CrudGate::resourceFromUrl($this->getResource());

        return $resource ? $user->crudCan($resource, CrudPermission::ACTION_VIEW) : true;
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
