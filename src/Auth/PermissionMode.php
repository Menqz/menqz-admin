<?php

namespace MenqzAdmin\Admin\Auth;

use MenqzAdmin\Admin\Auth\Database\AdminSetting;

class PermissionMode
{
    public const MODE_ROUTE = 'route';
    public const MODE_CRUD = 'crud';

    public static function current(): string
    {
        try {
            $mode = AdminSetting::getValue('permissions.mode', config('admin.permissions.mode', self::MODE_ROUTE));
        } catch (\Throwable $e) {
            $mode = config('admin.permissions.mode', self::MODE_ROUTE);
        }
        $mode = strtolower((string) $mode);

        if (!in_array($mode, [self::MODE_ROUTE, self::MODE_CRUD], true)) {
            return self::MODE_ROUTE;
        }

        return $mode;
    }

    public static function isCrud(): bool
    {
        return static::current() === self::MODE_CRUD;
    }

    public static function set(string $mode): void
    {
        $mode = strtolower(trim($mode));

        if (!in_array($mode, [self::MODE_ROUTE, self::MODE_CRUD], true)) {
            $mode = self::MODE_ROUTE;
        }

        AdminSetting::setValue('permissions.mode', $mode);
    }
}
