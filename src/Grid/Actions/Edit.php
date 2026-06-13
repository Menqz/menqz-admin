<?php

namespace MenqzAdmin\Admin\Grid\Actions;

use MenqzAdmin\Admin\Auth\CrudGate;
use MenqzAdmin\Admin\Auth\Database\CrudPermission;
use MenqzAdmin\Admin\Auth\PermissionMode;
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

    public function authorize($user, $model = null): bool
    {
        if (!PermissionMode::isCrud()) {
            return true;
        }

        $resource = CrudGate::resourceFromUrl($this->getResource());

        return $resource ? $user->crudCan($resource, CrudPermission::ACTION_EDIT) : true;
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
