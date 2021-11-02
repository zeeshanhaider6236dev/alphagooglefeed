<?php

namespace App\Http\Middleware;

use Closure;

class PlanCheck
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
        $shop = auth()->user();
        if($shop->plan_id == null):
            $settings = $shop->settings;
            if( $settings->multi_currency || $settings->decimal_rounding || $settings->geolocation):
                $settings->multi_currency = 0;
                $settings->decimal_rounding = 0;
                $settings->geolocation = 0;
                $settings->save();
            endif;
        endif;
        return $next($request);
    }
}
