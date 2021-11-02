<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Country;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Traits\ShopifyTrait;
use App\Http\Traits\ShopifyApiTrait;
use Osiset\ShopifyApp\Storage\Models\Plan;
use Osiset\ShopifyApp\Actions\CancelCurrentPlan;

class SettingController extends Controller
{
    use ShopifyApiTrait,ShopifyTrait;

    // public function Geolocation(){
    //     if($this->premiumChecks()):
    //         $settings = auth()->user()->settings;
    //         $flag = true;
    //         if($settings->geolocation):
    //             $settings->geolocation = 0;
    //         else:
    //             $settings->geolocation = 1;
    //             $flag = false;            
    //         endif;
    //         $settings->save();
    //         $this->createSnippetFile(auth()->user()->theme_id,auth()->user()->default_currency);
    //         if(auth()->user()->settings->enable):
    //             $this->includeSnippet(auth()->user()->theme_id);
    //         endif;
    //         if($flag){
    //             return response()->json(['success' => "Geolocation Disabled."]);
    //         }else{
    //             return response()->json(['success' => "Geolocation Enabled."]);
    //         }
    //     else:
    //         return response()->json(['error' => "You must Purchase premium plan."]);
    //     endif;
    //     return response()->json(['error' => "Something Went Wrong."]);
        
        
    // }

    // public function Multicurrency(){
    //     if($this->premiumChecks()):
    //         $settings = auth()->user()->settings;
    //         $flag = true;
    //         if($settings->multi_currency):
    //             $settings->multi_currency = 0;
    //         else:
    //             $settings->multi_currency = 1;
    //             $flag = false; 
    //         endif;
    //         $settings->save();
    //         $this->createSnippetFile(auth()->user()->theme_id,auth()->user()->default_currency);
    //         if(auth()->user()->settings->enable):
    //             $this->includeSnippet(auth()->user()->theme_id);
    //         endif;
    //         if($flag){
    //             return response()->json(['success' => "MultiCurrency Disabled"]);
    //         }else{
    //             return response()->json(['success' => "Multicurrency Enabled"]);
    //         }
    //     else:
    //         return response()->json(['error' => "You must Purchase premium plan."]);
    //     endif;
    //     return response()->json(['error' => "Something Went Wrong."]);
    // }

    // public function notification(){
    //     $settings = auth()->user()->settings;
    //     $flag = true;
    //     if($settings->notification):
    //         $settings->notification = 0;
    //     else:
    //         $settings->notification = 1;
    //         $flag = false; 
    //     endif;
    //     $settings->save();
    //     $this->createSnippetFile(auth()->user()->theme_id,auth()->user()->default_currency);
    //     if(auth()->user()->settings->enable):
    //         $this->includeSnippet(auth()->user()->theme_id);
    //     endif;
    //     if($flag){
    //         return response()->json(['success' => "Notification Disabled"]);
    //     }else{
    //         return response()->json(['success' => "Notification Enabled"]);
    //     }
    // }

    // public function decimal(){
    //     if($this->premiumChecks()):
    //         $settings = auth()->user()->settings;
    //         $flag = true;
    //         if($settings->decimal_rounding):
    //             $settings->decimal_rounding = 0;
    //         else:
    //             $settings->decimal_rounding = 1;
    //             $flag = false; 
    //         endif;
    //         $settings->save();
    //         $this->createSnippetFile(auth()->user()->theme_id,auth()->user()->default_currency);
    //         if(auth()->user()->settings->enable):
    //             $this->includeSnippet(auth()->user()->theme_id);
    //         endif;
    //         if($flag){
    //             return response()->json(['success' => "Rounding Disabled"]);
    //         }else{
    //             return response()->json(['success' => "Rounding Enabled"]);
    //         }
    //     else:
    //         return response()->json(['error' => "You must Purchase premium plan."]);
    //     endif;
    //     return response()->json(['error' => "Something Went Wrong."]);
    // }
    public function freePlan(CancelCurrentPlan $cancelCurrentPlanAction){
        if(auth()->user()->plan_id):
            $this->cancelCharge();
            $cancelCurrentPlanAction(auth()->user()->getId());
        endif;
        auth()->user()->update([
            'shopify_freemium' => 1,
            'plan_id' => null
        ]);
        return redirect()->route('home');
    }
}