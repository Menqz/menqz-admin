<?php

namespace MenqzAdmin\Admin\Middleware;

use Closure;
use MenqzAdmin\Admin\Auth\PasswordPolicy;
use MenqzAdmin\Admin\Facades\Admin;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \config(['auth.defaults.guard' => 'admin']);

        $redirectTo = admin_base_path(config('admin.auth.redirect_to', 'auth/login'));

        if (Admin::guard()->guest() && !$this->shouldPassThrough($request)) {
            return redirect()->to($redirectTo);
        }

        if (
            Admin::guard()->check()
            && !$this->shouldPassPasswordPolicy($request)
            && PasswordPolicy::shouldForceChange(Admin::user())
        ) {
            admin_toastr(trans('admin.password_change_required'), 'warning');

            return redirect()->to(admin_url('auth/setting'));
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        // The following routes do not authenticate the login
        $excepts = config('admin.auth.excepts', []);

        array_delete($excepts, [
            '_handle_action_',
            '_handle_form_',
            '_handle_selectable_',
            '_handle_renderable_',
        ]);

        return collect($excepts)
            ->map('admin_base_path')
            ->contains(function ($except) use ($request) {
                if ($except !== '/') {
                    $except = trim($except, '/');
                }

                return $request->is($except);
            });
    }

    /**
     * Determine if the request should bypass password change enforcement.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassPasswordPolicy($request)
    {
        $excepts = [
            'auth/logout',
            'auth/setting',
            'auth/setting/*',
            '_handle_action_',
            '_handle_form_',
            '_handle_selectable_',
            '_handle_renderable_',
        ];

        return collect($excepts)
            ->map('admin_base_path')
            ->contains(function ($except) use ($request) {
                if ($except !== '/') {
                    $except = trim($except, '/');
                }

                return $request->is($except);
            });
    }
}
