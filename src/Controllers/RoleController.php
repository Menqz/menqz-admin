<?php

namespace MenqzAdmin\Admin\Controllers;

use MenqzAdmin\Admin\Auth\Database\CrudPermission;
use MenqzAdmin\Admin\Auth\PermissionMode;
use MenqzAdmin\Admin\Form;
use MenqzAdmin\Admin\Grid;
use MenqzAdmin\Admin\Show;

class RoleController extends AdminController
{
    protected $usePersistent = false;

    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.roles');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $roleModel = config('admin.database.roles_model');

        $grid = new Grid(new $roleModel());

        $grid->column('id', 'ID')->sortable();
        $grid->column('slug', trans('admin.slug'));
        $grid->column('name', trans('admin.name'));

        if (PermissionMode::isCrud()) {
            // $grid->column('crudPermissions', 'Permissões CRUD')->pluck('slug')->label();
        } else {
            $grid->column('permissions', trans('admin.permission'))->pluck('name')->label();
        }

        $grid->column('created_at', trans('admin.created_at'))->dateTimeFormat();
        $grid->column('updated_at', trans('admin.updated_at'))->dateTimeFormat();

        $grid->actions(function (Grid\Displayers\Actions\Actions $actions) {
            if ($actions->row->slug == 'administrator') {
                $actions->disableDelete();
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $roleModel = config('admin.database.roles_model');

        $show = new Show($roleModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('slug', trans('admin.slug'));
        $show->field('name', trans('admin.name'));
        if (PermissionMode::isCrud()) {
            $show->field('crudPermissions', 'Permissões CRUD')->as(function ($permission) {
                return $permission->pluck('slug');
            })->label();
        } else {
            $show->field('permissions', trans('admin.permissions'))->as(function ($permission) {
                return $permission->pluck('name');
            })->label();
        }
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $roleModel());

        $form->display('id', 'ID');

        $form->text('slug', trans('admin.slug'))->rules('required');
        $form->text('name', trans('admin.name'))->rules('required');
        if (PermissionMode::isCrud()) {
            $matrix = $this->crudPermissionMatrix($permissionModel);

            $crudPermissionIds = [];

            $form->ignore('crud_permissions');

            $form->saving(function (Form $form) use (&$crudPermissionIds) {
                if (!PermissionMode::isCrud()) {
                    return;
                }

                $input = (array) request()->input('crud_permissions', []);

                $crudPermissionIds = array_values(array_unique(array_filter($input, static function ($v) {
                    return is_numeric($v);
                })));
            });

            $form->saved(function (Form $form) use (&$crudPermissionIds) {
                if (!PermissionMode::isCrud()) {
                    return;
                }

                $ids = array_map('intval', $crudPermissionIds);

                $form->model()->crudPermissions()->sync($ids);
            });

            $form->html(function (Form $form) use ($matrix) {
                $selected = old('crud_permissions');

                if (!is_array($selected)) {
                    if ($form->model()->exists) {
                        $selected = $form->model()->crudPermissions()->pluck('id')->all();
                    } else {
                        $selected = [];
                    }
                }

                $selected = array_map('intval', array_filter($selected, static function ($v) {
                    return is_numeric($v);
                }));

                $actions = [
                    CrudPermission::ACTION_LIST => 'Listar',
                    CrudPermission::ACTION_VIEW => 'Ver',
                    CrudPermission::ACTION_CREATE => 'Criar',
                    CrudPermission::ACTION_EDIT => 'Editar',
                    CrudPermission::ACTION_DELETE => 'Excluir',
                ];

                $html = '<div class="table-responsive">';
                $html .= '<table class="table table-sm table-bordered align-middle">';
                $html .= '<thead><tr>';
                $html .= '<th style="width: 40%;">Permissão</th>';

                foreach ($actions as $label) {
                    $html .= '<th class="text-center">'.$label.'</th>';
                }

                $html .= '</tr></thead><tbody>';

                foreach ($matrix as $resource => $byAction) {
                    $displayResource = str_replace('.', '/', (string) $resource);
                    $html .= '<tr>';
                    $html .= '<td><span class="text-nowrap">'.e($displayResource).'</span></td>';

                    foreach (array_keys($actions) as $action) {
                        $perm = $byAction[$action] ?? null;
                        $disabled = $perm === null ? 'disabled' : '';
                        $checked = $perm !== null && in_array((int) $perm['id'], $selected, true) ? 'checked' : '';
                        $value = $perm !== null ? (int) $perm['id'] : 0;

                        $html .= '<td class="text-center">';
                        $html .= '<div class="form-check d-inline-flex justify-content-center m-0">';
                        if (!$disabled) {
                            $html .= '<input class="form-check-input" type="checkbox" name="crud_permissions[]" value="'.$value.'" '.$checked.' '.$disabled.'>';
                        }
                        $html .= '</div>';
                        $html .= '</td>';
                    }

                    $html .= '</tr>';
                }

                $html .= '</tbody></table></div>';

                return $html;
            }, 'Permissões CRUD');
        } else {
            $form->listbox('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'))->height(300);
        }

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        return $form;
    }

    protected function crudPermissionMatrix(string $permissionModel): array
    {
        $resources = collect($permissionModel::query()->pluck('slug')->all())
            ->filter(function ($slug) {
                return is_string($slug) && $slug !== '' && $slug !== '*';
            })
            ->values();

        $resources = $resources->merge([
            'auth/users',
            'auth/roles',
            'auth/permissions',
            'auth/menu',
            'auth/logs',
            'auth/setting',
        ])->unique()->values();

        foreach ($resources as $resource) {
            $resource = CrudPermission::normalizeResource((string) $resource);
            foreach (CrudPermission::ACTIONS as $action) {
                CrudPermission::ensure($resource, $action);
            }
        }

        /** @var CrudPermission $crudModel */
        $crudModel = config('admin.database.crud_permissions_model', CrudPermission::class);

        $permissions = $crudModel::query()
            ->where('resource', '!=', '*')
            ->orderBy('resource')
            ->orderBy('action')
            ->get();

        $matrix = [];

        foreach ($permissions->groupBy('resource') as $resource => $items) {
            $matrix[$resource] = [];
            foreach ($items as $perm) {
                $matrix[$resource][$perm->action] = [
                    'id' => $perm->id,
                    'action' => $perm->action,
                    'resource' => $perm->resource,
                ];
            }
        }

        return $matrix;
    }

    protected function crudPermissionOptions(string $permissionModel): array
    {
        $resources = collect($permissionModel::query()->pluck('slug')->all())
            ->filter(function ($slug) {
                return is_string($slug) && $slug !== '' && $slug !== '*';
            })
            ->values();

        $resources = $resources->merge([
            'auth/users',
            'auth/roles',
            'auth/permissions',
            'auth/menu',
            'auth/logs',
        ])->unique()->values();

        foreach ($resources as $resource) {
            $resource = CrudPermission::normalizeResource((string) $resource);
            foreach (CrudPermission::ACTIONS as $action) {
                CrudPermission::ensure($resource, $action);
            }
        }

        /** @var CrudPermission $crudModel */
        $crudModel = config('admin.database.crud_permissions_model', CrudPermission::class);

        return $crudModel::query()
            ->orderBy('resource')
            ->orderBy('action')
            ->get()
            ->mapWithKeys(function ($perm) {
                $label = "{$perm->resource} - {$perm->action}";
                return [$perm->id => $label];
            })
            ->all();
    }
}
