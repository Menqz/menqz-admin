<?php

namespace MenqzAdmin\Admin\Grid\Filter;

class NotIn extends In
{
    /**
     * {@inheritdoc}
     */
    protected $query = 'whereNotIn';
}
