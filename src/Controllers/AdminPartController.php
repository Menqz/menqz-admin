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

    use HasCustomHooks;
    /**
     * The parent model instance.
     *
     * @var Model
     */
    protected $parentModel;

    /**
     * @var string
     */
    protected $title;

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
            return $grid;
        });

        $this->hook("alterDetail", function ($scope, $detail) {
            // alter the detail here
            return $detail;
        });

        $this->hook("alterForm", function ($scope, $form) {
            // alter the form here
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

        return $grid->render();
    }

    public function create()
    {
        $form = $this->form();
        if ($this->hasHooks('alterForm')) {
            $form = $this->callHooks('alterForm', $form);
        }
        return $form->render();
    }

    public function edit()
    {
        $form = $this->form();
        if ($this->hasHooks('alterForm')) {
            $form = $this->callHooks('alterForm', $form);
        }
        return $form->render();
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
    public function handle(Request $request)
    {
        if ($request->get('_mode') === 'create') {
            return $this->create();
        } else if ($request->get('_mode') === 'edit') {
            return $this->edit();
        }

        return $this->index();
    }
}
