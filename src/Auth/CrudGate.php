<?php

namespace MenqzAdmin\Admin\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MenqzAdmin\Admin\Auth\Database\CrudPermission;

class CrudGate
{
    public static function resourceFromUrl(string $urlOrPath): ?string
    {
        $path = parse_url($urlOrPath, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            $path = $urlOrPath;
        }

        return static::resourceFromPath($path);
    }

    public static function resourceFromPath(string $path): ?string
    {
        $path = trim($path);

        if ($path === '') {
            return null;
        }

        $path = trim($path, '/');

        $prefix = trim((string) config('admin.route.prefix', 'admin'), '/');

        if ($prefix !== '' && Str::startsWith($path, $prefix.'/')) {
            $path = substr($path, strlen($prefix) + 1);
        } elseif ($prefix !== '' && $path === $prefix) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $path), static function ($s) {
            return $s !== '';
        }));

        if (count($segments) === 0) {
            return null;
        }

        $last = end($segments);

        if (in_array($last, ['create', 'edit'], true)) {
            array_pop($segments);
        }

        while (count($segments) > 0 && is_numeric((string) end($segments))) {
            array_pop($segments);
        }

        $segments = array_values(array_filter($segments, static function ($segment) {
            return !is_numeric((string) $segment);
        }));

        if (count($segments) === 0) {
            return null;
        }

        return CrudPermission::normalizeResource(implode('.', $segments));
    }

    public static function resourceFromRouteName(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        $prefix = (string) config('admin.route.prefix', 'admin');
        $prefixDot = $prefix !== '' ? $prefix.'.' : '';

        if ($prefixDot !== '' && Str::startsWith($routeName, $prefixDot)) {
            $routeName = substr($routeName, strlen($prefixDot));
        }

        $parts = array_values(array_filter(explode('.', $routeName), static function ($p) {
            return $p !== '';
        }));

        if (count($parts) === 0) {
            return null;
        }

        $last = end($parts);
        $restful = ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'];

        if (in_array($last, $restful, true)) {
            array_pop($parts);
        }

        if (count($parts) === 0) {
            return null;
        }

        return CrudPermission::normalizeResource(implode('.', $parts));
    }

    public static function actionFromRequest(Request $request): string
    {
        $routeName = optional($request->route())->getName();
        $method = strtoupper($request->method());

        $routeAction = null;
        if (is_string($routeName) && $routeName !== '') {
            $parts = explode('.', $routeName);
            $routeAction = end($parts) ?: null;
        }

        $map = [
            'index' => CrudPermission::ACTION_LIST,
            'show' => CrudPermission::ACTION_VIEW,
            'create' => CrudPermission::ACTION_CREATE,
            'store' => CrudPermission::ACTION_CREATE,
            'edit' => CrudPermission::ACTION_EDIT,
            'update' => CrudPermission::ACTION_EDIT,
            'destroy' => CrudPermission::ACTION_DELETE,
        ];

        if ($routeAction && isset($map[$routeAction])) {
            return $map[$routeAction];
        }

        if ($method === 'GET' || $method === 'HEAD') {
            $path = trim($request->path(), '/');
            $prefix = trim((string) config('admin.route.prefix', 'admin'), '/');

            if ($prefix !== '' && Str::startsWith($path, $prefix.'/')) {
                $path = substr($path, strlen($prefix) + 1);
            }

            $segments = array_values(array_filter(explode('/', $path), static function ($s) {
                return $s !== '';
            }));

            $last = end($segments) ?: null;

            if ($last === 'create') {
                return CrudPermission::ACTION_CREATE;
            }

            if ($last === 'edit') {
                return CrudPermission::ACTION_EDIT;
            }

            if ($last !== null && is_numeric((string) $last)) {
                return CrudPermission::ACTION_VIEW;
            }

            return CrudPermission::ACTION_LIST;
        }

        if ($method === 'POST') {
            return CrudPermission::ACTION_CREATE;
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            return CrudPermission::ACTION_EDIT;
        }

        if ($method === 'DELETE') {
            return CrudPermission::ACTION_DELETE;
        }

        return CrudPermission::ACTION_VIEW;
    }
}
