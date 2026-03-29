<?php

namespace MenqzAdmin\Admin\Form\Field;

class DatetimeRange extends DateRange
{
    protected $format = 'Y-m-d H:i:s';
    protected $view = 'admin::form.daterange';
}
