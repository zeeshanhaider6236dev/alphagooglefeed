<?php

namespace App\Http\Middleware;

use Closure;

class SetupCheckTrue
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!auth()->user()->settings->setup):
            return redirect()->route('setup');
        endif;
        return $next($request);
    }
}
