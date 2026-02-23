<?php

namespace MenqzAdmin\Admin\Grid\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use MenqzAdmin\Admin\Actions\Response;
use MenqzAdmin\Admin\Actions\RowAction;

class Delete extends RowAction
{
    public $icon = 'icon-trash';

    public $customClass = 'grid-delete-btn';

    /**
     * @return array|null|string
     */
    public function name()
    {
        return __('admin.delete');
    }

    public function addScript()
    {
        $queryString = $this->parent->getQueryString();
        $dataUrl = sprintf(
            '%s/%s%s',
            $this->getResource(),
            $this->getKey(),
            $queryString ? ('?'.$queryString) : ''
        );

        $this->attributes = [
            'onclick' => 'admin.resource.delete(event,this)',
            'data-url'=> $dataUrl,
        ];
    }

    /*
    // could use dialog as well instead of addScript
    public function dialog()
    {
        $options  = [
            "type" => "warning",
            "showCancelButton"=> true,
            "confirmButtonColor"=> "#DD6B55",
            "confirmButtonText"=> __('confirm'),
            "showLoaderOnConfirm"=> true,
            "cancelButtonText"=>  __('cancel'),
        ];
        $this->confirm('Are you sure delete?', '', $options);
    }
    */

    /**
     * @param Model $model
     *
     * @return Response
     */
    public function handle(Model $model)
    {
        $trans = [
            'failed'    => trans('admin.delete_failed'),
            'succeeded' => trans('admin.delete_succeeded'),
        ];

        try {
            DB::transaction(function () use ($model) {
                $model->delete();
            });
        } catch (\Exception $exception) {
            return $this->response()->error("{$trans['failed']} : {$exception->getMessage()}");
        }

        return $this->response()->success($trans['succeeded'])->refresh();
    }
}
