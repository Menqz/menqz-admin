<?php

namespace MenqzAdmin\Admin\Grid\Displayers;

use MenqzAdmin\Admin\Helpers\Helper;

class CurrencyFormat extends AbstractDisplayer
{
    public function display($precision = 2)
    {
        if (!$this->getValue()) {
            return '';
        }
        return Helper::formatCurrency($this->getValue(), $precision);
    }
}
