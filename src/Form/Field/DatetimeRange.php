<?php

namespace MenqzAdmin\Admin\Form\Field;

class DatetimeRange extends DateRange
{
    protected $format = 'YYYY-MM-DD HH:mm:ss';
    protected $view = 'admin::form.daterange';
}
