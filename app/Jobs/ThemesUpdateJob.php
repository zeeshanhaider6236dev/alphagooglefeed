<?php namespace App\Jobs;

use App\Http\Traits\GoogleApiTrait;
use App\User;
use Illuminate\Support\Str;
use stdClass;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Osiset\ShopifyApp\Contracts\Objects\Values\ShopDomain;

class ThemesUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GoogleApiTrait;

    public $user;
    public $shopDomain;
    public $data;

    
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    public function handle()
    {
        $this->user = User::with('settings')->where('name', $this->shopDomain->toNative())->whereNotNull('password')->has('settings')->first();
        if($this->data->role == 'main'):
            if ($this->user->settings->themeId != $this->data->id) :
                $this->user->settings->update(['themeId' => $this->data->id]);
                $this->user->load('settings');
            endif;
            $html =  $this->shopifyApiRequest("getSingleAsset",$this->user->settings->themeId,["asset[key]" => config('shopifyApi.strings.theme_liquid_file')],['body','asset','value'],$this->user);
            if($html):
                if(!Str::contains($html,config('shopifyApi.strings.app_start_identifier'))):
                    $res = $this->getSiteVerificationToken($this->user);
                    if($res['token']):
                        $pos = strpos($html,config('shopifyApi.strings.app_include_before_tag'));
                        $newhtml = substr($html,0,$pos).config('shopifyApi.strings.app_include')[0].$res['token'].config('shopifyApi.strings.app_include')[1].substr($html,$pos);
                        $toupdate = [
                            "asset" => [
                                "key" => config('shopifyApi.strings.theme_liquid_file'),
                                "value" => $newhtml
                            ]
                        ];
                        $this->shopifyApiRequest("saveSingleAsset",$this->user->settings->themeId,$toupdate,['status'],$this->user,'PUT');
                    endif;
                endif;
            endif;
        endif;
    }
}
