<?php

namespace MenqzAdmin\Admin\Auth\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MenqzAdmin\Admin\Traits\DefaultDatetimeFormat;

class CrudPermission extends Model
{
    use DefaultDatetimeFormat;

    public const ACTION_LIST = 'list';
    public const ACTION_VIEW = 'view';
    public const ACTION_CREATE = 'create';
    public const ACTION_EDIT = 'edit';
    public const ACTION_DELETE = 'delete';

    public const ACTIONS = [
        self::ACTION_LIST,
        self::ACTION_VIEW,
        self::ACTION_CREATE,
        self::ACTION_EDIT,
        self::ACTION_DELETE,
    ];

    protected $fillable = ['name', 'resource', 'action', 'slug'];

    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.crud_permissions_table', 'admin_crud_permissions'));

        parent::__construct($attributes);
    }

    public static function normalizeResource(string $resource): string
    {
        $resource = trim($resource);
        $resource = trim($resource, ". \t\n\r\0\x0B");
        $resource = str_replace('/', '.', $resource);
        $resource = preg_replace('/\.+/', '.', $resource);

        return $resource ?: '*';
    }

    public static function normalizeAction(string $action): string
    {
        return strtolower(trim($action));
    }

    public static function slugFor(string $resource, string $action): string
    {
        return static::normalizeResource($resource).':'.static::normalizeAction($action);
    }

    public static function ensure(string $resource, string $action): self
    {
        $resource = static::normalizeResource($resource);
        $action = static::normalizeAction($action);

        $slug = static::slugFor($resource, $action);

        return static::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => "{$resource} - {$action}",
                'resource' => $resource,
                'action' => $action,
                'slug' => $slug,
            ]
        );
    }

    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_crud_permissions_table', 'admin_role_crud_permissions');
        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'crud_permission_id', 'role_id');
    }
}

