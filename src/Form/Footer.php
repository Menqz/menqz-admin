<?php

namespace MenqzAdmin\Admin\Form;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Route;

class Footer implements Renderable
{
    /**
     * Footer view.
     *
     * @var string
     */
    protected $view = 'admin::form.footer';

    /**
     * Form builder instance.
     *
     * @var Builder
     */
    protected $builder;

    /**
     * Available buttons.
     *
     * @var array
     */
    protected $buttons = ['reset', 'submit'];

    /**
     * Available checkboxes.
     *
     * @var array
     */
    protected $checkboxes = ['view', 'continue_editing', 'continue_creating'];

    /**
     * @var string
     */
    protected $defaultCheck;

    /**
     * @var bool
     */
    public $fixedFooter = true;

    /**
     * @var bool
     */
    public $useDestroyInCancel = true;

    /**
     * Footer constructor.
     *
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Disable reset button.
     *
     * @return $this
     */
    public function disableReset(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->buttons, 'reset');
        } elseif (!in_array('reset', $this->buttons)) {
            array_push($this->buttons, 'reset');
        }

        return $this;
    }

    /**
     * Disable submit button.
     *
     * @return $this
     */
    public function disableSubmit(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->buttons, 'submit');
        } elseif (!in_array('submit', $this->buttons)) {
            array_push($this->buttons, 'submit');
        }

        return $this;
    }

    /**
     * Disable View Checkbox.
     *
     * @return $this
     */
    public function disableViewCheck(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->checkboxes, 'view');
        } elseif (!in_array('view', $this->checkboxes)) {
            array_push($this->checkboxes, 'view');
        }

        return $this;
    }

    /**
     * Disable Editing Checkbox.
     *
     * @return $this
     */
    public function disableEditingCheck(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->checkboxes, 'continue_editing');
        } elseif (!in_array('continue_editing', $this->checkboxes)) {
            array_push($this->checkboxes, 'continue_editing');
        }

        return $this;
    }

    /**
     * Disable Creating Checkbox.
     *
     * @return $this
     */
    public function disableCreatingCheck(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->checkboxes, 'continue_creating');
        } elseif (!in_array('continue_creating', $this->checkboxes)) {
            array_push($this->checkboxes, 'continue_creating');
        }

        return $this;
    }

    /**
     * Set `view` as default check.
     *
     * @return $this
     */
    public function checkView()
    {
        $this->defaultCheck = 'view';

        return $this;
    }

    /**
     * Set `continue_creating` as default check.
     *
     * @return $this
     */
    public function checkCreating()
    {
        $this->defaultCheck = 'continue_creating';

        return $this;
    }

    /**
     * Set `continue_editing` as default check.
     *
     * @return $this
     */
    public function checkEditing()
    {
        $this->defaultCheck = 'continue_editing';

        return $this;
    }

    /**
     * Set `continue_editing` as default check.
     *
     * @return $this
     */
    public function fixedFooter($set = true)
    {
        $this->fixedFooter = $set;

        return $this;
    }

    /**
     * Set `continue_editing` as default check.
     *
     * @return $this
     */
    public function useDestroyInCancel($set = true)
    {
        $this->useDestroyInCancel = $set;

        return $this;
    }

    protected function getRoutePrincipal()
    {
        // Quebrando o nome da rota atual em partes (ex: ['parceiro', 'contato', 'edit'])
        $routeParts = explode('.', Route::currentRouteName());

        // Remover a última parte (que geralmente é 'create', 'edit', etc.)
        array_pop($routeParts);

        $routePrincipal = implode('.', $routeParts);

        return $routePrincipal;
    }

    protected function getRouteCancel()
    {
        $routePrincipal = $this->getRoutePrincipal();

        // Construir o nome da rota 'index' (ex: 'parceiro.contato.index')
        $indexRoute = $routePrincipal . '.index';

        // Obter os parâmetros atuais da rota (ex: ['id' => 1])
        $routeParameters = request()->route()->parameters();

        $route = '';
        try {
            $route = route($indexRoute, $routeParameters);
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $route;
    }

    protected function getRouteDestroy()
    {
        $routePrincipal = $this->getRoutePrincipal();

        $routeDestroy = '';
        try {
            $routeDestroy = route($routePrincipal.'.destroy', ['#id#']);
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $routeDestroy;
    }

    /**
     * Render footer.
     *
     * @return string
     */
    public function render()
    {
        $submitRedirects = [
            'continue_editing'  => 'continue_editing',
            'continue_creating' => 'continue_creating',
            'view'              => 'view',
            //'exit' => 'exit', // can be exit as well when doing ajax request
        ];

        $data = [
            'width'            => $this->builder->getWidth(),
            'buttons'          => $this->buttons,
            'checkboxes'       => $this->checkboxes,
            'submit_redirects' => $submitRedirects,
            'default_check'    => $this->defaultCheck,
            'route_cancel'     => $this->getRouteCancel(),
            'route_destroy'    => $this->getRouteDestroy(),
            'fixed_footer'      => $this->fixedFooter,
            'use_destroy_in_cancel' => $this->useDestroyInCancel,
            'is_creating' => $this->builder->isCreating(),
            'is_editing' => $this->builder->isEditing(),
        ];

        return view($this->view, $data)->render();
    }
}
