<?php

namespace App\Http\Middleware;

use Closure;

class SetupCheckFalse
{
    public function handle($request, Closure $next)
    {
        if(auth()->user()->settings->setup):
            return redirect()->route('home');
        endif;
        return $next($request);
    }
}
