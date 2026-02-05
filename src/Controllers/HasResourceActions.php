<?php

namespace MenqzAdmin\Admin\Controllers;

trait HasResourceActions
{
    /**
     * Returns the form with possible callback hooks.
     *
     * @return \MenqzAdmin\Admin\Form;
     */
    public function getForm($id = 0)
    {
        $form = $this->form($id);
        if (method_exists($this, 'hasHooks') && $this->hasHooks('alterForm')) {
            $form = $this->callHooks('alterForm', $form);
        }

        return $form;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $form = $this->getForm($id);
        return $form->update($form->model()->id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store()
    {
        $id = null;
        if ($id == null) {
            $id = request('id_object', null);
        }
        return $this->getForm($id)->store();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $form = $this->getForm($id);
        return $form->destroy($form->model()->id);
    }
}
