<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Traits\ShopifyApiTrait;
use Illuminate\Support\Facades\Config;
use Osiset\ShopifyApp\Http\Middleware\Billable;

class CustomBillable extends Billable
{
    use ShopifyApiTrait;

    public function handle($request, Closure $next)
    {
        if (Config::get('shopify-app.billing_enabled') === true) {
            $shop = auth()->user();
            if (!$shop->isFreemium() && !$shop->isGrandfathered() && $shop->plan == null) {
                return redirect()->route('plans');
            }
        }
        return $next($request);
    }
}
