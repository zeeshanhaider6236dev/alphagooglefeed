<?php

namespace App\Jobs;

use App\Http\Traits\CommonTrait;
use App\Http\Traits\ShopifyTrait;
use App\User;
use Osiset\ShopifyApp\Actions\CancelCurrentPlan;
use Osiset\ShopifyApp\Contracts\Commands\Shop as IShopCommand;
use Osiset\ShopifyApp\Contracts\Objects\Values\ShopDomain;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;

class AppUninstalledJob extends \Osiset\ShopifyApp\Messaging\Jobs\AppUninstalledJob
{
    use ShopifyTrait,CommonTrait;
    protected $shopDomain;

    public function __construct(ShopDomain $domain)
    {
        $this->shopDomain = $domain;
    }
    
    public function handle(IShopCommand $shopCommand,IShopQuery $shopQuery,CancelCurrentPlan $cancelCurrentPlanAction)
    : bool{
        $shop = User::where('name' , $this->shopDomain->toNative())->first();
        if($shop):
            $shop->status = false;
            $shop->settings->update([
                'setup' => false,
                'googleAccessToken' => null,
                'googleRefreshToken' => null,
                'googleAccountId' => null,
                'googleAccountEmail' => null,
                'merchantAccountId' => null,
                'merchantAccountName' => null,
            ]);
            $shop->shopify_freemium = false;
            $shop->save();
            $shop->products()->delete();
            if($shop->settings->store_email !=null){
                // $this->UninstallEmail(['name'=>$shop->settings->store_name,'email'=>$shop->settings->store_email]);
            }
            // package logic to uninstall app
            $shop = $shopQuery->getByDomain($this->shopDomain);
            $shopId = $shop->getId();
            $cancelCurrentPlanAction($shopId);
            $shopCommand->clean($shopId);
            $shopCommand->softDelete($shopId);
        endif;
        return true;
    }
}