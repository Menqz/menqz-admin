<?php

namespace MenqzAdmin\Admin\Grid\Concerns;

use MenqzAdmin\Admin\Admin;

trait CanDoubleClick
{
    /**
     * Double-click grid row to jump to the edit page.
     *
     * @return $this
     */
    public function enableDblClick()
    {
        $script = <<<SCRIPT
        document.body.addEventListener('dblclick', function (e) {
            tr = e.target.closest("tr");
            if (tr && tr.dataset.key){
                var url = "{$this->resource()}/"+tr.dataset.key+"/edit";
                admin.ajax.navigate(url);
            }
        });
SCRIPT;
        Admin::script($script);

        return $this;
    }
}
