<?php

namespace MenqzAdmin\Admin\Grid\Displayers;

use Carbon\Carbon;
use MenqzAdmin\Admin\Grid;

class TimeFormat extends AbstractDisplayer
{
    public function display($format = null)
    {
        if (!$this->getValue()) {
            return '';
        }

        if ($format == null) {
            if (Grid::getAlternativeTimeFormat()) {
                $format = Grid::getAlternativeTimeFormat();
            } else {
                $format = 'H:i:s';
            }
        }

        return (new Carbon($this->getValue()))->format($format);
    }
}
