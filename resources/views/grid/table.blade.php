@include("admin::grid.table-header")

        @php
            $visibleColumns = $grid->visibleColumns();
            $mobileCardTitleColumn = $visibleColumns->first(function ($column) {
                return !in_array($column->getName(), [
                    \MenqzAdmin\Admin\Grid\Column::SELECT_COLUMN_NAME,
                    \MenqzAdmin\Admin\Grid\Column::ACTION_COLUMN_NAME,
                ]);
            });
            $mobileCardTitleColumnName = $mobileCardTitleColumn ? $mobileCardTitleColumn->getName() : null;
        @endphp

        <div class="table-responsive @if($grid->showMobileCards()) grid-mobile-cards-container @endif" autocomplete="off">
            <table class="table grid-table table-sm table-hover select-table @if($grid->showMobileCards()) grid-mobile-cards @endif" id="{{ $grid->tableID }}">

                <thead>
                    <tr>
                        @foreach($visibleColumns as $column)
                        <th {!! $column->formatHtmlAttributes() !!}>{!! $column->getLabel() !!}{!! $column->renderHeader() !!}</th>
                        @endforeach
                    </tr>
                </thead>

                @if ($grid->hasQuickCreate())
                    {!! $grid->renderQuickCreate() !!}
                @endif

                <tbody>

                    @if($grid->rows()->isEmpty() && $grid->showDefineEmptyPage())
                        @include('admin::grid.empty-grid')
                    @endif

                    @foreach($grid->rows() as $row)
                    <tr {!! $row->getRowAttributes() !!}>
                        @foreach($visibleColumns as $column)
                        @php
                            $name = $column->getName();
                            $mobileCardLabel = trim(strip_tags((string) $column->getLabel()));
                        @endphp
                        <td {!! $row->getColumnAttributes($name) !!} data-label="{{ $mobileCardLabel }}" @if($name === $mobileCardTitleColumnName) data-card-title="true" @endif>
                            {!! $row->column($name) !!}
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>

                {!! $grid->renderTotalRow() !!}

            </table>

        </div>

        {!! $grid->renderFooter() !!}

        {!! $grid->paginator() !!}

    </div>
        <!-- /.box-body -->
</div>
