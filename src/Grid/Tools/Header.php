<?php

namespace MenqzAdmin\Admin\Grid\Tools;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Query\Builder;
use MenqzAdmin\Admin\Grid;

class Header extends AbstractTool
{
    /**
     * @var Builder
     */
    protected $queryBuilder;

    /**
     * Header constructor.
     *
     * @param Grid $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * Get model query builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function queryBuilder()
    {
        if (!$this->queryBuilder) {
            $this->queryBuilder = $this->grid->model()->getQueryBuilder();
        }

        return $this->queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $content = call_user_func($this->grid->header(), $this->queryBuilder());

        if (empty($content)) {
            return '';
        }

        if ($content instanceof Renderable) {
            $content = $content->render();
        }

        if ($content instanceof Htmlable) {
            $content = $content->toHtml();
        }

        return <<<HTML
    <div class="with-border clearfix">
        {$content}
    </div>
HTML;
    }
}
