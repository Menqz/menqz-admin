<?php

namespace MenqzAdmin\Admin\Middleware;

use Closure;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\RateLimiter;
use MenqzAdmin\Admin\Facades\Admin;

class Throttle
{
    protected $loginView = 'admin::login';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $rate_limit_key = session('login_throttle_key');

        if (! $rate_limit_key) {
            return $next($request);
        }

        // throttle this
        if (Admin::guard()->guest() && config('admin.auth.throttle_logins')) {
            $throttle_attempts = config('admin.auth.throttle_attempts', 5);
            if (RateLimiter::tooManyAttempts($rate_limit_key, $throttle_attempts)) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('attempts', $this->getToManyAttemptsMessage());

                return response()->view($this->loginView, ['errors' => $errors], 429);
            }
        }

        return $next($request);
    }

    protected function getToManyAttemptsMessage()
    {
        return Lang::has('auth.to_many_attempts')
            ? trans('auth.to_many_attempts')
            : 'To many attempts!';
    }
}
