<?php

namespace MenqzAdmin\Admin\Grid\Filter;

class EndsWith extends Like
{
    protected $exprFormat = '%{value}';
}
