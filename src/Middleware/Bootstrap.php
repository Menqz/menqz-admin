<?php

namespace MenqzAdmin\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use MenqzAdmin\Admin\Facades\Admin;

class Bootstrap
{
    public function handle(Request $request, Closure $next)
    {
        Admin::bootstrap();

        return $next($request);
    }
}
