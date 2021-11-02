<?php

namespace App\Jobs;

use App\Http\Traits\CommonTrait;
use App\User;
use App\Models\Setting;
use App\Http\Traits\ShopifyTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AfterAuthenticateJob implements ShouldQueue
{
    use Dispatchable, SerializesModels,ShopifyTrait,CommonTrait;

    public function handle()
    {
        $shop = auth()->user();
        $setting=$shop->settings;
        if($setting == null):
            $themeId = "{$this->getMainThemeId()}";
            $shopApi = $this->shopApi(['body','shop']);
            $data = [
                'user_id' => $shop->id,
                'themeId' => $themeId,
                'domain' => $shopApi['domain'],
                'country' => $shopApi['country_code'],
                'currency' => $shopApi['currency'],
                'language' => $shopApi['primary_locale'],
                'store_name' => $shopApi['name'],
                'store_email' => $shopApi['email'],
                'store_phone' => $shopApi['phone'],
                'country_name' => $shopApi['country_name'],
                'plan_display_name' => $shopApi['plan_display_name']
            ];
            $setting = Setting::create($data);
            $shop->load('settings');
            // $this->welcomeEmail([ 
            //     'name'=> $setting->store_name,
            //     'email'=> $setting->store_email
            // ]);
        endif;
    }
}
