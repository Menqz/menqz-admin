<?php

namespace MenqzAdmin\Admin\Grid\Tools;

use MenqzAdmin\Admin\Admin;
use MenqzAdmin\Admin\Grid;

class ExportButton extends AbstractTool
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * Create a new Export button instance.
     *
     * @param Grid $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * Render Export button.
     *
     * @return string
     */
    public function render()
    {
        if (!$this->grid->showExportBtn()) {
            return '';
        }
        $page = request('page', 1);

        return Admin::component('admin::components.export-btn', [
            'page'   => $page,
            'grid'   => $this->grid,
        ]);
    }
}
