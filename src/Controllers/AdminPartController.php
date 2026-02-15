<?php

namespace MenqzAdmin\Admin\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Database\Eloquent\Model;
use MenqzAdmin\Admin\Grid;
use MenqzAdmin\Admin\Form;
use Illuminate\Http\Request;
use MenqzAdmin\Admin\Traits\HasCustomHooks;

abstract class AdminPartController extends Controller
{
    use HasResourceActions;
    use HasCustomHooks;
    /**
     * The parent model instance.
     *
     * @var Model
     */
    protected $parentModel;

     /**
     * Query string for grid.
     *
     * @var string
     */
    protected $hasManyString = '';

    /**
     * @var string
     */
    protected $title;

     /**
     * Query string for grid.
     *
     * @var string
     */
    protected $queryString = '';

    /**
     * Part constructor.
     *
     * @param Model|null $parentModel
     */
    public function __construct(?Model $parentModel = null)
    {
        $this->parentModel = $parentModel;

        $this->hook("alterGrid", function ($scope, $grid) {
            // alter the grid here
            $grid->disableFilter(true);
            $grid->disableExport(true);
            $grid->disableRowSelector(true);
            $grid->disableBatchActions(true);
            $grid->disablePagination(true);
            $grid->disableColumnSelector(true);

            if ($this->parentModel) {
                $grid->model()->where($this->hasManyString . '_type', get_class($this->parentModel))
                              ->where($this->hasManyString . '_id', $this->parentModel->id);
            }
            return $grid;
        });

        $this->hook("alterDetail", function ($scope, $detail) {
            // alter the detail here
            $detail->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableList();
                    $tools->disableDelete();
                });
            return $detail;
        });

        $this->hook("alterForm", function ($scope, $form) {
            // alter the form here
            $form->hasFooter(false);
            $form->setTitle($this->title());

            if ($this->parentModel) {
                $form->hidden($this->hasManyString . '_type')->value(get_class($this->parentModel));
                $form->hidden($this->hasManyString . '_id')->value($this->parentModel->id);
            }
            return $form;
        });
    }

    /**
     * Get or set the title.
     *
     * @param string|null $title
     * @return $this|string
     */
    public function title($title = null)
    {
        if ($title !== null) {
            $this->title = $title;
            return $this;
        }

        return $this->title ?: class_basename(static::class);
    }

    public function index()
    {
        $grid = $this->grid();
        if ($this->hasHooks('alterGrid')) {
            $grid = $this->callHooks('alterGrid', $grid);
        }

        if ($this->hasHooks('alterGridCustom')) {
            $grid = $this->callHooks('alterGridCustom', $grid);
        }

        $grid->queryString($this->queryString);

        return $grid->render();
    }

     /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id)
    {
        $detail = $this->detail($id);
        if ($this->hasHooks('alterDetail')) {
            $detail = $this->callHooks('alterDetail', $detail);
        }

        if ($this->hasHooks('alterDetailCustom')) {
            $detail = $this->callHooks('alterDetailCustom', $detail);
        }

        return $detail;
    }


    public function create()
    {
        $form = $this->form();
        if ($this->hasHooks('alterForm')) {
            $form = $this->callHooks('alterForm', $form);
        }

        if ($this->hasHooks('alterFormCustom')) {
            $form = $this->callHooks('alterFormCustom', $form);
        }

        return $form->render();
    }

    public function edit($id)
    {
        $form = $this->form();
        if ($this->hasHooks('alterForm')) {
            $form = $this->callHooks('alterForm', $form);
        }

        if ($this->hasHooks('alterFormCustom')) {
            $form = $this->callHooks('alterFormCustom', $form);
        }

        return $form->edit($id)->render();
    }

    /**
     * Build the grid.
     *
     * @return Grid
     */
    abstract public function grid();

    /**
     * Build the form.
     *
     * @return Form
     */
    abstract public function form();

    /**
     * Handle the request and return the response.
     *
     * @param Request $request
     * @return mixed
     */
    public function handle(Request $request, ?string $id = null, ?string $modo = null)
    {
        $this->queryString = $this->formatQueryString($request->query());
        if ($modo === 'create') {
            return $this->create();
        } else if ($modo === 'edit') {
            return $this->edit($id);
        } else if ($modo === 'show') {
            return $this->show($id);
        }

        return $this->index();
    }

    /**
     * Format query string.
     *
     * @param array $query
     * @return string
     */
    protected function formatQueryString(array $query)
    {
        return http_build_query($query);
    }
}
