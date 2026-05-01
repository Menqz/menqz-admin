<?php

namespace MenqzAdmin\Admin\Grid\Displayers;

use Carbon\Carbon;
use MenqzAdmin\Admin\Admin;

class DateTimeFormat extends AbstractDisplayer
{
    public function getAlternativeFormat()
    {
        return Admin::getAlternativeDatetimeFormat() ?? null;
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
                $format = 'Y-m-d H:i:s';
            }
        }

        return (new Carbon($this->getValue()))->format($format);
    }
}
