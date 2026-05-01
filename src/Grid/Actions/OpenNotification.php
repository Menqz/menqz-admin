<?php

namespace MenqzAdmin\Admin\Grid\Actions;

use MenqzAdmin\Admin\Actions\RowAction;
use MenqzAdmin\Admin\Notifications\Database\Notification;

class OpenNotification extends RowAction
{
    public $icon = 'icon-envelope-open';

    public $name = 'Visualizar';

    public function handle(Notification $model)
    {
        // $model ...
        $model->markViewed();
        return $this->response()->success(trans('admin.viewed'))->refresh();
    }

    public function render() {
        if ($this->row->viewed_at) {
            return '';
        }
        return parent::render();
    }
}
