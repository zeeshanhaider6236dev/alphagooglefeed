<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\GoogleApiTrait;
use Laravel\Socialite\Facades\Socialite;

class SetupController extends Controller
{
    use GoogleApiTrait;

    public function redirectToProvider()
    {
        return Socialite::driver('google')
        ->stateless()
        ->scopes(config('googleApi.scopes'))
        ->with(["access_type" => "offline", "prompt" => "consent select_account"])
        ->redirect();
    }

    public function handleProviderCallback(Request $request)
    {
        $user = Socialite::driver('google')->stateless()->user();
        $data['googleAccountId'] = $user->getId();
        $data['googleAccountEmail'] = $user->getEmail();
        $data['googleAccessToken'] = $user->token;
        $data['googleRefreshToken'] = $user->refreshToken;
        $data['expiresIn'] = $user->expiresIn;
        $shop = auth()->user();
        if($shop->settings->update($data)):
            $shop->load('settings');
            return '<script type="text/javascript">window.close();</script>';
        else:
            return "Something Went Wrong.";
        endif;
    }

    public function disconnect()
    {
        if($this->disconnectGoogle()):
            return response()->json(['status' => true,'success' => "Account Disconnected."]);
        endif;
        return response()->json(['status' => false,'error' => "Something Went Wrong."]);
    }

    public function accountConnect(Request $request)
    {
        $account = $this->getAccountInfo($request->account_id);
        if($account):
            if(auth()->user()->settings->update(['merchantAccountId' => $request->account_id,'merchantAccountName' => $account['name']])):
                auth()->user()->load('settings');
                return response()->json(['success' =>'Account Connected.','status' => true]);
            else:
                return response()->json(['error' => 'Could Not Save Account Details.']);
            endif;
        else:
            return response()->json(['error' => 'Cound Not Get Merchant Account Details.']);
        endif; 
    }

    public function accountDisconnect()
    {
        if(auth()->user()->settings->update(['merchantAccountId' => null ,'merchantAccountName' => null])):
            auth()->user()->load('settings');
            return response()->json(['success' => 'Account Disconnected.','status' => true]);
        else:
            return response()->json(['error' => 'Something Went Wrong.','status' => false]);
        endif;
    }

    public function domainVerify()
    {
        $res = $this->getSiteVerificationToken();
        if($res):
            if($this->addTokenToTheme($res['token'])):
                if($this->verifySite()):
                    if($this->updateDomaintoMerchantAccount()):
                        if($this->claimSite()):
                            return response()->json(['message' => 'Website Claimed.','status' => true]);
                        else:
                            return response()->json(['message' => 'Could Not Claim.']);
                        endif;
                    else:
                        return response()->json(['message' => 'Could Not Update Domain Name.']);
                    endif;
                else:
                    return response()->json(['message' => 'Could Not Verify.']);
                endif;
            else:
                return response()->json(['message' => 'Could Not Add Token to Theme.']);
            endif;
        else:
            return response()->json(['message' => 'Could Not get Site Verification Token.']);
        endif;    
        return response()->json(['message'=> 'Could Not Claim Domain.']);    
    }
}
