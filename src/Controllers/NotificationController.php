<?php

namespace MenqzAdmin\Admin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MenqzAdmin\Admin\Facades\Admin;
use MenqzAdmin\Admin\Layout\Content;
use MenqzAdmin\Admin\Notifications\Database\Notification;
use MenqzAdmin\Admin\Grid;
use MenqzAdmin\Admin\Grid\Actions\OpenNotification;
use MenqzAdmin\Admin\Grid\Actions\Show;

class NotificationController extends AdminController
{
    protected $usePersistent = false;

    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.notifications');
    }

    /**
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Notification());

        $grid->column('created_at', trans('admin.created_at'))->dateTimeFormat();
        $grid->column('title', trans('admin.title'));
        $grid->column('description', trans('admin.description'));
        $grid->column('viewed', trans('admin.viewed'))->display(function ($viewed) {
            if ($this->viewed_at) {
                return '<span class="badge rounded-pill bg-success">'.trans('admin.viewed').'</span>';
            }
            return '';
        });
        $grid->column('viewed_at', trans('admin.viewed_at'))->dateTimeFormat();

        $this->hook("alterGridCustom", function ($scope, $grid) {
            // alter the grid here
            $grid->actions(function ($actions) use ($scope) {
                $actions->disableShow();
                $actions->disableDelete();
                $actions->disableEdit();

                $actions->add(new OpenNotification());
                $actions->add(new Show());
            });
            return $grid;
        });

        $user = Admin::user();
        $grid->model()->visibleTo($user)->orderBy('created_at', 'DESC');

        return $grid;
    }

    public function unread(Request $request)
    {
        $user = Admin::user();
        $limit = (int) config('admin.notifications.dropdown_limit', 10);

        $query = Notification::query()
            ->visibleTo($user)
            ->whereNull('viewed_at')
            ->orderByDesc('id');

        $count = (clone $query)->count();

        $items = $query
            ->limit($limit)
            ->get(['id', 'title', 'description', 'created_at']);

        return response()->json([
            'count' => $count,
            'notifications' => $items->map(function (Notification $n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'description' => $n->description,
                    'created_at' => optional($n->created_at)->toISOString(),
                ];
            })->values(),
        ]);
    }

    public function read(int $id)
    {
        $user = Admin::user();

        $notification = Notification::query()
            ->visibleTo($user)
            ->whereKey($id)
            ->firstOrFail();

        $notification->markViewed();

        $count = Notification::query()
            ->visibleTo($user)
            ->whereNull('viewed_at')
            ->count();

        return response()->json([
            'ok' => true,
            'count' => $count,
        ]);
    }

    public function readAll()
    {
        $user = Admin::user();

        Notification::query()
            ->visibleTo($user)
            ->whereNull('viewed_at')
            ->update(['viewed_at' => now()]);

        return response()->json([
            'ok' => true,
            'count' => 0,
        ]);
    }
}

