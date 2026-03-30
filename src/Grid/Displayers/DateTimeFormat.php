<?php

namespace MenqzAdmin\Admin\Grid\Displayers;

use Carbon\Carbon;
use MenqzAdmin\Admin\Grid;

class DateTimeFormat extends AbstractDisplayer
{
    public function display($format = null)
    {
        if (!$this->getValue()) {
            return '';
        }

        if ($format == null) {
            if (Grid::getAlternativeDateTimeFormat()) {
                $format = Grid::getAlternativeDateTimeFormat();
            } else {
                $format = 'Y-m-d H:i:s';
            }
        }

        return (new Carbon($this->getValue()))->format($format);
    }
}
