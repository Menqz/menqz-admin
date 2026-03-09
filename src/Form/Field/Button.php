<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Form\Field;

class Button extends Field
{
    protected $class = 'btn-primary';

    protected $positionFooter = false;

    protected $scriptUnlockButton = '';

    protected $scriptOnAction = '';

    /**
     * Field constructor.
     *
     * @param       $column
     * @param array $arguments
     */
    public function __construct($column = '', $arguments = [])
    {
        parent::__construct($column, $arguments);
        $this->addVariables(['positionFooter' => false]);
        $this->addVariables(['useUnlockButton' => false]);
    }

    public function positionFooter($positionFooter = null)
    {
        if ($positionFooter === null) {
            return $this->positionFooter;
        }

        $this->positionFooter = $positionFooter;

        $this->addVariables(['positionFooter' => $positionFooter]);

        return $this;
    }

    public function info()
    {
        $this->class = 'btn-info';

        return $this;
    }

    public function unlockButton($message = '')
    {
        $this->addVariables(['useUnlockButton' => true]);

        $this->scriptUnlockButton = <<<JS
            document.querySelectorAll('.unlock-{$this->id}').forEach(unlockBbutton=> {unlockBbutton.addEventListener('click', function(e) {
                e.preventDefault();
                let icon = this.getElementsByTagName('i')[0];
                let button = this;
                Swal.fire({
                    title: '$message',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                }).then(
                    function (result) {
                        if (result.value == true) {
                            document.querySelectorAll('.{$this->id}').forEach(button=>{button.disabled = false});
                            icon.classList.remove('icon-lock');
                            icon.classList.add('icon-lock-open');
                            button.disabled = true;
                        }
                    }
                );
            })});
        JS;
        return $this;
    }

    public function on($event, $callback)
    {
        $this->scriptOnAction = <<<JS
            document.querySelectorAll('{$this->getElementClassSelector()}').forEach(button=>{
                button.addEventListener('$event', function() {
                    $callback
                });
            });
        JS;
    }

    public function getAllScripts()
    {
        return ';(function () {' . $this->scriptUnlockButton . $this->scriptOnAction . '})();';
    }
}
