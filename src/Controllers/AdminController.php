<?php

namespace MenqzAdmin\Admin\Controllers;

use Illuminate\Routing\Controller;
use MenqzAdmin\Admin\Layout\Content;
use MenqzAdmin\Admin\Traits\HasCustomHooks;

class AdminController extends Controller
{
    use HasResourceActions;
    use HasCustomHooks;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Title';

    /**
     * Set description for following 4 action pages.
     *
     * @var array
     */
    protected $description = [
        //        'index'  => 'Index',
        //        'show'   => 'Show',
        //        'edit'   => 'Edit',
        //        'create' => 'Create',
    ];

    /**
    * id for current resource.
    *
    * @var string
    */
    protected $controll_id = 'controll';

    public function __construct()
    {
        $this->hook("alterGrid", function ($scope, $grid) {
            // alter the grid here
            $grid->model()->where('persistent', true);
            return $grid;
        });

        $this->hook("alterDetail", function ($scope, $detail) {
            // alter the detail here
            return $detail;
        });

        $this->hook("alterForm", function ($scope, $form) {
            // alter the form here
            $form->hidden('persistent');
            $form->hidden('id_object', 'id_object')->default($form->model->id);
            $form->ignore('id_object');

            if ($scope->controll_id != null) {
                $previous_controll = $scope->controll_id.'_previous';
                $previousSess = session($previous_controll, null);
                $previous = url()->previous();

                if (($previous != $previousSess) && (!strpos($previous, 'edit') && !strpos($previous, 'create'))) {
                    $previousSess = $previous;
                }
                session([$previous_controll => $previousSess]);
                $form->hidden('_custom_previous_')->value($previousSess);
                $form->ignore('_custom_previous_');
            }
            return $form;
        });
    }

    /**
     * Get content title.
     *
     * @return string
     */
    protected function title()
    {
        return $this->title;
    }

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        $grid = $this->grid();
        if ($this->hasHooks('alterGrid')) {
            $grid = $this->callHooks('alterGrid', $grid);
        }

        return $content
            ->title($this->title())
            ->description($this->description['index'] ?? trans('admin.list'))
            ->body($grid);
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        $detail = $this->detail($id);
        if ($this->hasHooks('alterDetail')) {
            $detail = $this->callHooks('alterDetail', $detail);
        }

        return $content
            ->title($this->title())
            ->description($this->description['show'] ?? trans('admin.show'))
            ->body($detail);
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        $form = $this->form($id);
        if ($this->hasHooks('alterForm')) {
            $form = $this->callHooks('alterForm', $form);
        }

        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($form->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content)
    {
        $id = old('id_object', null);
        $form = $this->form($id);

        if ($this->hasHooks('alterForm')) {
            $form = $this->callHooks('alterForm', $form);
        }

        return $content
            ->title($this->title())
            ->description($this->description['create'] ?? trans('admin.create'))
            ->body($form);
    }

    public function getControllIDCreate()
    {
        return $this->controll_id.'_create';
    }

    public function getControllIDEdit()
    {
        return $this->controll_id.'_edit';
    }
}
