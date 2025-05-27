<?php

namespace MenqzAdmin\Admin\Controllers;

use Illuminate\Support\Arr;
use MenqzAdmin\Admin\Admin;

class Dashboard
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function title()
    {
        return view('admin::dashboard.title');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function environment()
    {
        $envs = [
            ['name' => 'PHP version',       'value' => 'PHP/'.PHP_VERSION],
            ['name' => 'Laravel version',   'value' => app()->version()],
            ['name' => 'CGI',               'value' => php_sapi_name()],
            ['name' => 'Uname',             'value' => php_uname()],
            ['name' => 'Server',            'value' => Arr::get($_SERVER, 'SERVER_SOFTWARE')],

            ['name' => 'Cache driver',      'value' => config('cache.default')],
            ['name' => 'Session driver',    'value' => config('session.driver')],
            ['name' => 'Queue driver',      'value' => config('queue.default')],

            ['name' => 'Timezone',          'value' => config('app.timezone')],
            ['name' => 'Locale',            'value' => config('app.locale')],
            ['name' => 'Env',               'value' => config('app.env')],
            ['name' => 'URL',               'value' => config('app.url')],
        ];

        return view('admin::dashboard.environment', compact('envs'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function extensions()
    {
        $extensions = [
            'helpers' => [
                'name' => 'menqz-admin-ext/helpers',
                'link' => 'https://github.com/menqz-admin-org/helpers',
                'icon' => 'cogs',
            ],
            'log-viewer' => [
                'name' => 'menqz-admin-ext/log-viewer',
                'link' => 'https://github.com/menqz-admin-org/log-viewer',
                'icon' => 'database',
            ],
            'backup' => [
                'name' => 'menqz-admin-ext/backup',
                'link' => 'https://github.com/menqz-admin-org/backup',
                'icon' => 'copy',
            ],
            'config' => [
                'name' => 'menqz-admin-ext/config',
                'link' => 'https://github.com/menqz-admin-org/config',
                'icon' => 'toggle-on',
            ],
            'api-tester' => [
                'name' => 'menqz-admin-ext/api-tester',
                'link' => 'https://github.com/menqz-admin-org/api-tester',
                'icon' => 'sliders-h',
            ],
            'media-manager' => [
                'name' => 'menqz-admin-ext/media-manager',
                'link' => 'https://github.com/menqz-admin-org/media-manager',
                'icon' => 'file',
            ],
            'scheduling' => [
                'name' => 'menqz-admin-ext/scheduling',
                'link' => 'https://github.com/menqz-admin-org/scheduling',
                'icon' => 'clock',
            ],
            'reporter' => [
                'name' => 'menqz-admin-ext/reporter',
                'link' => 'https://github.com/menqz-admin-org/reporter',
                'icon' => 'bug',
            ],
            'redis-manager' => [
                'name' => 'menqz-admin-ext/redis-manager',
                'link' => 'https://github.com/menqz-admin-org/redis-manager',
                'icon' => 'flask',
            ],
            'grid-sortable' => [
                'name' => 'menqz-admin-ext/grid-sortable',
                'link' => 'https://github.com/menqz-admin-org/grid-sortable',
                'icon' => 'arrows-alt-v',
            ],
        ];

        foreach ($extensions as &$extension) {
            $name = explode('/', $extension['name']);
            $extension['installed'] = array_key_exists(end($name), Admin::$extensions);
        }

        return view('admin::dashboard.extensions', compact('extensions'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function dependencies()
    {
        $json = file_get_contents(base_path('composer.json'));

        $dependencies = json_decode($json, true)['require'];

        return Admin::component('admin::dashboard.dependencies', compact('dependencies'));
    }
}
