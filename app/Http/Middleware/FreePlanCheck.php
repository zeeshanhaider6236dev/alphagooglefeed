<?php

namespace App\Http\Middleware;

use App\Http\Traits\ShopifyApiTrait;
use Closure;

class FreePlanCheck
{
    use ShopifyApiTrait;
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
                $this->createSnippetFile($shop->theme_id,$shop->default_currency,$shop);    
            endif;
        endif;
        return $next($request);
    }
}
