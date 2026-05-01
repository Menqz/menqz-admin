<?php

namespace MenqzAdmin\Admin\Form\Field;

use MenqzAdmin\Admin\Admin;

class DatetimeRange extends DateRange
{
    protected $format = 'Y-m-d H:i:s';
    protected $view = 'admin::form.daterange';

    public function getAlternativeFormat()
    {
        return Admin::getAlternativeDatetimeFormat() ?? null;
    }
}
