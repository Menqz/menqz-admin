<?php

namespace MenqzAdmin\Admin\Grid\Displayers;

use Carbon\Carbon;
use MenqzAdmin\Admin\Admin;

class DateFormat extends AbstractDisplayer
{
    public function getAlternativeFormat()
    {
        return Admin::getAlternativeDateFormat() ?? null;
    }

    public function display($format = null)
    {
        if (!$this->getValue()) {
            return '';
        }

        if ($format == null) {
            if ($this->getAlternativeFormat() !== null) {
                $format = $this->getAlternativeFormat();
            } else {
                $format = 'Y-m-d';
            }
        }

        return (new Carbon($this->getValue()))->format($format);
    }
}
