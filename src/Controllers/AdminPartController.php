<?php

namespace MenqzAdmin\Admin\Controllers;

use Illuminate\Database\Eloquent\Model;
use MenqzAdmin\Admin\Grid;
use MenqzAdmin\Admin\Form;
use Illuminate\Http\Request;

abstract class AdminPartController
{
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
        if ($request->get('_mode') === 'form') {
            return $this->form()->render();
        }

        return $this->grid()->render();
    }
}
