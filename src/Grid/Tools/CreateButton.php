<?php

namespace MenqzAdmin\Admin\Grid\Tools;

use MenqzAdmin\Admin\Grid;
use MenqzAdmin\Admin\Auth\CrudGate;
use MenqzAdmin\Admin\Auth\PermissionMode;
use MenqzAdmin\Admin\Auth\Database\CrudPermission;
use MenqzAdmin\Admin\Facades\Admin;

class CreateButton extends AbstractTool
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * Create a new CreateButton instance.
     *
     * @param Grid $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * Render CreateButton.
     *
     * @return string
     */
    public function render()
    {
        if (!$this->grid->showCreateBtn()) {
            return '';
        }

        if (PermissionMode::isCrud()) {
            $resource = CrudGate::resourceFromUrl($this->grid->resource());
            if ($resource && !Admin::user()->crudCan($resource, CrudPermission::ACTION_CREATE)) {
                return '';
            }
        }

        $new = trans('admin.new');

        return <<<HTML
        <a href="{$this->grid->getCreateUrl()}" class="btn btn-sm btn-success me-1 grid-create-btn" title="{$new}">
            <i class="icon-plus"></i><span class="hidden-xs">{$new}</span>
        </a>
        HTML;
    }
}
