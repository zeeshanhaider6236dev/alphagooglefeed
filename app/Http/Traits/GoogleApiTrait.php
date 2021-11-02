<?php
namespace App\Http\Traits;

use Illuminate\Support\Str;
use App\Http\Traits\ShopifyTrait;
use Illuminate\Support\Facades\Http;


trait GoogleApiTrait {
    
    use ShopifyTrait;

    private $token;
    private $merchantId;
    private $accountId;
    private $googleUser;

    public function ContentApiRequest($api, $params = null, $postparams = null, $method = 'get')
    {
        $response =  Http::withToken($this->token)
        ->$method($this->makeGoogleUrl($api, 'contentApis', $params),$postparams);
        if($response->status() == 400 || $response->status() == 403):
            logger()->channel('request')->info(json_encode($response->json()));
        endif;
        // logger()->channel('request')->info($response->status());
        // logger()->channel('request')->info(json_encode($response->json()));
        if($response->status() == 200 || ($response->status() == 403 && $api == "claimWebsite")):
            return $response->json();
        endif;
        if($response->status() == 401):
            if($this->refreshToken($this->googleUser)):
                $res =  Http::withToken($this->token)->$method($this->makeGoogleUrl($api, 'contentApis', $params),$postparams);
                if($res->status() == 200):
                    return $res->json();
                endif;
            endif;
        endif;
        return false;
    }

    public function siteVerificationApiRequest($api ,$params = null, $postparams = null, $method = 'get' ,$googleUser = null)
    {
        $response =  Http::withToken($this->token)
        ->$method($this->makeGoogleUrl($api, 'siteVerificationApis', $params),$postparams);
        return $response; 
        if($response->status() == 200):
            return $response->json();
        endif;
        if($response->status() == 401):
            if($this->refreshToken($this->googleUser)):
                $res =  Http::withToken($this->token)->$method($this->makeGoogleUrl($api, 'siteVerificationApis', $params),$postparams);
                if($res->status() == 200):
                    return $res->json();
                endif;
            endif;
        endif;
        info(json_encode($response->json()));
        return false;
    }

    public function makeGoogleUrl($api, $type, $data = null)
    {
        foreach (config("googleApi.$type.$api") as $index => $value):
            $url[] = $value;
            if(isset($data[$index])):
                $url[] = $data[$index];
            endif;
		endforeach;
		return implode('', $url);
    }

    public function getMerchantAccounts($token = null,$googleUser = null)
    {
        if(!$this->googleUser):
            $this->googleUser = $googleUser ? $googleUser : auth()->user();
        endif;
        if(!$this->token):
            $this->token = $token ? $token : $this->googleUser->settings->googleAccessToken;
        endif;
        $response = $this->ContentApiRequest('getMainMerchantAccount');
        if($response): 
            $accounts = [];
            if(isset($response['accountIdentifiers'])):
                foreach ($response['accountIdentifiers'] as $value):
                    if(isset($value['aggregatorId']) && !isset($value['merchantId'])):
                        $accounts[] = $value['aggregatorId'];
                    endif;
                    if(isset($value['merchantId'])):
                        $accounts[] = $value['merchantId'];
                    endif;
                endforeach;
            endif;
            foreach ($accounts as $key => $value):
                $accounts[$key] = $this->getSubMerchantAccounts($value);
            endforeach;
            return $accounts;
        else:
            return [];
        endif;
    }

    public function getSubMerchantAccounts($merchantId){
        $res = $this->getAccountInfo($merchantId);
        $response = $this->ContentApiRequest('getSubMerchantAccounts',[$merchantId]);
        $subAccounts = [];
        if($res):
            $subAccounts['merchantId'] =  $merchantId;
            $subAccounts['merchantName'] = $res['name'];
        endif;
        $subAccounts['subAccounts'] = [];
        if($response):
            if(isset($response['resources'])):
                foreach($response['resources'] as $single):
                    $subAccounts['subAccounts'][] = [
                        'id' => $single['id'],
                        'name' => $single['name']
                    ]; 
                endforeach;
            endif;
        endif;
        return $subAccounts;
    }

    public function getAccountInfo($accountid,$token = null,$googleUser = null)
    {
        if(!$this->googleUser):
            $this->googleUser = $googleUser ? $googleUser : auth()->user();
        endif;
        if(!$this->token):
            $this->token = $token ? $token : $this->googleUser->settings->googleAccessToken;
        endif;
        return $this->ContentApiRequest('getAccountInfo',[$accountid,$accountid]);
    }

    public function updateDomaintoMerchantAccount()
    {
        if(!$this->googleUser):
            $this->googleUser = auth()->user();
        endif;
        if(!$this->token):
            $this->token = $this->googleUser->settings->googleAccessToken;
        endif;
        $accounts = [
            $this->googleUser->settings->merchantAccountId,
            $this->googleUser->settings->merchantAccountId
        ];
        $data = [
            'websiteUrl' => "https://".$this->googleUser->settings->domain,
            'id' => $this->googleUser->settings->merchantAccountId,
            'name' => $this->googleUser->settings->merchantAccountName,
            "users" => [
                [
                    "emailAddress" => $this->googleUser->settings->googleAccountEmail,
                    "admin" => true
                ]
            ]
        ];
        $response =  $this->ContentApiRequest('updateAccountInfo', $accounts, $data, 'put');
        return $response;
    }
    public function updateShippingtoMerchantAccount()
    {
        if(!$this->googleUser):
            $this->googleUser = auth()->user();
        endif;
        if(!$this->token):
            $this->token = $this->googleUser->settings->googleAccessToken;
        endif;
        $accounts = [
            $this->googleUser->settings->merchantAccountId,
            $this->googleUser->settings->merchantAccountId
        ];
        // $data = [
        //     "accountId" => $this->googleUser->settings->merchantAccountId,
        //     "services" => [
        //         [
        //             "name" => config('googleApi.strings.AutomaticShippingName'),
        //             "deliveryCountry" => auth()->user()->settings->country,
        //             "currency" => auth()->user()->settings->currency,
        //             "rateGroups" => [
        //                 [
        //                     "applicableShippingLabels" => [
        //                         "KG"
        //                     ],
        //                     "name" => "Free Shipping KG",
        //                     'mainTable' => [
        //                         "rowHeaders" => [
        //                             "weights" => [
        //                                 [
        //                                     "value" => "100",
        //                                     "unit" => "kg"
        //                                 ],
        //                                 [
        //                                     "value" => "infinity",
        //                                     "unit" => "kg"
        //                                 ]
        //                             ]
        //                         ],
        //                         "rows" => [
        //                             [
        //                                 "cells" => [
        //                                     [
        //                                         "noShipping" => true
        //                                     ]
        //                                 ]
        //                             ],
        //                             [
        //                                 "cells" => [
        //                                     [
        //                                         "noShipping" => true
        //                                     ]
        //                                 ]
        //                             ]
        //                         ]
        //                     ]
        //                 ],
        //                 [
        //                     "applicableShippingLabels" => [
        //                         "LB"
        //                     ],
        //                     "name" => "Free Shipping LB",
        //                     'mainTable' => [
        //                         "rowHeaders" => [
        //                             "weights" =>[
        //                                 [
        //                                     "value" => "100",
        //                                     "unit" => "lb"
        //                                 ],
        //                                 [
        //                                     "value" => "infinity",
        //                                     "unit" => "lb"
        //                                 ]
        //                             ]
        //                         ],
        //                         "rows" => [
        //                             [
        //                                 "cells" => [
        //                                     [
        //                                         "noShipping" => true
        //                                     ]
        //                                 ],
        //                             ],
        //                             [
        //                                 "cells" => [
        //                                     [
        //                                         "noShipping" => true
        //                                     ]
        //                                 ]
        //                             ]
        //                         ]
        //                     ]
        //                 ]
        //             ],
        //             "deliveryTime" => [
        //                 "minTransitTimeInDays" => 7,
        //                 "maxTransitTimeInDays" => 9,
        //                 "minHandlingTimeInDays" => 1,
        //                 "maxHandlingTimeInDays" => 2,
        //             ],
        //             "active" => true,
        //             "eligibility" => "All scenarios"
        //         ]
        //     ]
        // ];
        $data = [
            "accountId" => $this->googleUser->settings->merchantAccountId,
            "services" => [
                [
                    "name" => config('googleApi.strings.AutomaticShippingName'),
                    "deliveryCountry" => auth()->user()->settings->country,
                    "currency" => auth()->user()->settings->currency,
                    "rateGroups" => [
                        [
                            "name" => "Price",
                            'mainTable' => [
                                "rowHeaders" => [
                                    "prices" => [
                                        [
                                            "value" => "100",
                                            "currency" => auth()->user()->settings->currency
                                        ],
                                        [
                                            "value" => "infinity",
                                            "currency" => auth()->user()->settings->currency
                                        ]
                                    ]
                                ],
                                "rows" => [
                                    [
                                        "cells" => [
                                            [
                                                "noShipping" => true
                                            ]
                                        ]
                                    ],
                                    [
                                        "cells" => [
                                            [
                                                "noShipping" => true
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                    ],
                    "deliveryTime" => [
                        "minTransitTimeInDays" => 7,
                        "maxTransitTimeInDays" => 9,
                        "minHandlingTimeInDays" => 1,
                        "maxHandlingTimeInDays" => 2,
                    ],
                    "active" => true,
                    "eligibility" => "All scenarios"
                ]
            ]
        ];
        $response =  $this->ContentApiRequest('updateShippingSettings', $accounts, $data, 'put');
        return $response;
    }

    public function claimSite()
    {
        if(!$this->googleUser):
            $this->googleUser = auth()->user();
        endif;
        if(!$this->token):
            $this->token = auth()->user()->settings->googleAccessToken;
        endif;
        $accounts = [
            $this->googleUser->settings->merchantAccountId,
            $this->googleUser->settings->merchantAccountId
        ];
        $response =  $this->ContentApiRequest('claimWebsite', $accounts, [], 'post');
        if(isset($response['error'])):
            if(Str::contains($response['error']['message'], $this->googleUser->settings->googleAccountEmail)):
                return true;
            endif;
            return false;
        endif;
        return $response;
    }

    public function getSiteVerificationToken($user = null)
    {
        if(!$this->googleUser):
            $this->googleUser = $user ? $user : auth()->user();
        endif;
        if(!$this->token):
            $this->token = $this->googleUser->settings->googleAccessToken;
        endif;
        $data = [
            "site" =>  [
                "identifier" => "https://".$this->googleUser->settings->domain,
                "type" => "SITE"
            ],
            "verificationMethod" => "META"
        ];
        return $this->siteVerificationApiRequest('getSiteVerificationToken',null,$data,'POST');
    }

    public function verifySite()
    {
        if(!$this->googleUser):
            $this->googleUser = auth()->user();
        endif;
        if(!$this->token):
            $this->token = $this->googleUser->settings->googleAccessToken;
        endif;
        $data = [
            "site" => [
                "type" => "SITE",
                "identifier" => "https://".$this->googleUser->settings->domain
            ]
        ];
        return isset($this->siteVerificationApiRequest('verifySite',null,$data,'post')['id']) ? true : false;
    }

    // public function getMerchantAccountProducts($merchantId){
    //     $this->setup($merchantId);
    //     $response = $this->ContentApiRequest('getAllProducts',[$this->merchantId]);
    //     return $response->status() == 200 ?  $response['resources'] : null;
    // }

    public function refreshToken($googleUser)
    {
        if(!$this->googleUser):
            $this->googleUser = $googleUser ? $googleUser : auth()->user();
        endif;
        $response =  Http::post($this->makeGoogleUrl('refreshToken', 'contentApis', null),[
            'client_id' => config('googleApi.client_id'),
            'client_secret' => config('googleApi.client_secret'),
            'refresh_token' =>  $this->googleUser->settings->googleRefreshToken,
            'grant_type' => 'refresh_token',
        ]);
        if($response->status() == 200):
            $settings = $this->googleUser->settings;
            $settings->googleAccessToken = $response['access_token'];
            if($settings->save(['timestamps' => false])):
                $this->googleUser->load('settings');
                $this->token = $response['access_token'];
                return true;
            endif;
        endif;
        return false;
    }

    public function disconnectGoogle()
    {
        $response =  Http::withHeaders(["Content-type" => "application/x-www-form-urlencoded"])->post($this->makeGoogleUrl('revokeToken', 'contentApis', null).auth()->user()->settings->googleAccessToken);
        if($response->status() == 200):
            $update = [
                'googleAccessToken' => null,
                'googleRefreshToken' => null,
                'googleAccountId' => null,
                'googleAccountEmail' => null,
                'merchantAccountId' => null ,
                'merchantAccountName' => null
            ];
            if(auth()->user()->settings->update($update)):
                return true;
            endif;
        endif;
        return false;
    }

    public function uploadProductToMerchantAccount($data, $shop = null)
    {
        if($shop):
            $this->googleUser = $shop;
        else:
            $this->googleUser = auth()->user();
        endif;
        if(!$this->token):
            $this->token = $this->googleUser->settings->googleAccessToken;
        endif;
        return $this->ContentApiRequest('addProduct',[$this->googleUser->settings->merchantAccountId],$data,'post');
    }

    public function uploadBulkProductsToMerchantAccount($toUpload, $shop = null)
    {
        if($shop):
            $this->googleUser = $shop;
        else:
            $this->googleUser = auth()->user();
        endif;
        if(!$this->token):
            $this->token = $this->googleUser->settings->googleAccessToken;
        endif;
        $response = $this->ContentApiRequest('addBulkProducts',null,$toUpload,'post');
        return $response;
    }

    public function deleteBulkProductsFromMerchantAccount($toDelete, $shop = null)
    {
        if($shop):
            $this->googleUser = $shop;
        else:
            $this->googleUser = auth()->user();
        endif;
        if(!$this->token):
            $this->token = $this->googleUser->settings->googleAccessToken;
        endif;
        $response = $this->ContentApiRequest('removeBulkProducts',null,$toDelete,'post');
        return $response;
    }

    public function updateProductStatuses($products,$googleUser = null, $token = null)
    {   
        if(!$this->googleUser):
            $this->googleUser = $googleUser ? $googleUser : auth()->user();
        endif;
        if(!$this->token):
            $this->token = $token ? $token : $this->googleUser->settings->googleAccessToken;
        endif;
        $data['entries'] = [];
        $count = 0;
        foreach ($products as $key => $product):
            $single = [
                "merchantId" => $this->googleUser->settings->merchantAccountId,
                "method" => "get",
                "includeAttributes" => false
            ];
            $flag = false;
            if($this->googleUser->settings->whichProducts == "first"):
                $flag = true;
            endif;
            foreach ($product->variants as $key2 => $value):
                $single['batchId'] = $count;
                $single['productId'] = $this->convertProductIdToGoogleFormat($product,$value);
                $data['entries'][] = $single;
                $count++;
                if($flag):
                    break;
                endif;
            endforeach;
        endforeach;
        $response = $this->ContentApiRequest('getStatuses',null,$data,'POST');
        if(isset($response['entries'])):
            foreach ($products as $product) :
                $statuses = [];
                foreach ($product->variants as $value) :
                    foreach ($response['entries'] as  $entry):
                        if(isset($entry['productStatus'])):
                            if($this->convertProductIdToGoogleFormat($product,$value) == $entry['productStatus']['productId']):
                                $status = $this->getStatusToProduct($entry['productStatus']['destinationStatuses']);
                                if($status):
                                    $value->update(['status' => $status]);
                                    $statuses[] = $status;
                                endif;
                                break;
                            endif;
                        endif;
                    endforeach;
                endforeach;
                if($statuses):
                    $product->update([
                        'status' => array_unique($statuses)
                    ]);
                endif;
            endforeach;
        endif;
    }

    public function convertProductIdToGoogleFormat($product,$variant,$full = true)
    {
        if($this->googleUser->settings->productIdFormat == "global"):
            $formattedId =  "Shopify_".$this->googleUser->settings->country."_".$product->productId."_".$variant->variantId;
        elseif($this->googleUser->settings->productIdFormat == "sku"):
            $formattedId = $variant->sku;
        else:
            $formattedId = $variant->id;
        endif;
        return $full ? "online:".$this->googleUser->settings->language.":".$this->googleUser->settings->country.":".$formattedId : $formattedId;
    }

    public function getStatusToProduct($entry){
        foreach ($entry as $status) :
            if($status['destination'] == "Shopping"):
                $status = $status['status'];
                if($status == 'approved'):
                    return config('shopifyApi.strings.googleStatusApproved');
                elseif($status == 'disapproved'):
                    return config('shopifyApi.strings.googleStatusDisapproved');
                elseif($status == 'pending'):
                    return config('shopifyApi.strings.googleStatusPending');
                endif;
                break;
            endif;
        endforeach;
    }

    public function getVariantStatuses($product,$variants,$googleUser = null, $token = null)
    {   
        if(!$this->googleUser):
            $this->googleUser = $googleUser ? $googleUser : auth()->user();
        endif;
        if(!$this->token):
            $this->token = $token ? $token : $this->googleUser->settings->googleAccessToken;
        endif;
        $data['entries'] = [];
        foreach ($variants as $key => &$variant):
            $data['entries'][] = [
                "batchId" => $key,
                "merchantId" => $this->googleUser->settings->merchantAccountId,
                "method" => "get",
                "productId" => $this->convertVariantToGoogleFormat($variant,$product['id']),
                "includeAttributes" => false
            ];
            if($variant['image_id'] != null):
                foreach ($product['images'] as $value) :
                    if($value['id'] == $variant['image_id']):
                        $variant['image'] = $value['src'];
                        $variants[$key] = $variant;
                        break;
                    endif;
                endforeach;
            else:
                $variant['image'] = $product['image']['src'] ?? '';
                $variants[$key] = $variant;
            endif;
        endforeach;
        $response = $this->ContentApiRequest('getStatuses',null,$data,'POST');
        if(isset($response['entries'])):
            foreach ($response['entries'] as  $entry):
                foreach ($variants as $key2 => &$variant):
                    if(isset($entry['productStatus'])):
                        if($this->convertVariantToGoogleFormat($variant,$product['id']) == $entry['productStatus']['productId']):
                            $destinations = [];
                            foreach ($entry['productStatus']['destinationStatuses'] as $destination) :
                                $destinations[] = [
                                    'destination' => $destination['destination'],
                                    'status' => $destination['status']
                                ];
                            endforeach;
                            $variant['googleStatus'] = $destinations;
                            $errors = [];
                            if(isset($entry['productStatus']['itemLevelIssues'])):
                                foreach ($entry['productStatus']['itemLevelIssues'] as $issue) :
                                    if(isset($issue['code'])):
                                        $errors[] = $issue['code'];
                                    endif;
                                    if(isset($issue['detail'])):
                                        $errors[] = $issue['detail'];
                                    endif;
                                endforeach;
                            endif;
                            $variant['errors'] = $errors;
                            $variants[$key2] = $variant;
                            break;
                        endif;
                    endif;
                endforeach;
            endforeach;
        endif;
        return $variants;
    }

    public function convertVariantToGoogleFormat($variant,$productId,$full = true,$object = false,$googleUser = null,$token= null)
    {
        if(!$this->googleUser):
            $this->googleUser = $googleUser ? $googleUser : auth()->user();
        endif;
        if(!$this->token):
            $this->token = $token ? $token : $this->googleUser->settings->googleAccessToken;
        endif;
        if($object):
            if($this->googleUser->settings->productIdFormat == "global"):
                $formattedId =  "Shopify_".$this->googleUser->settings->country."_".$productId."_".$variant->variantId;
            elseif($this->googleUser->settings->productIdFormat == "sku"):
                $formattedId = $variant->sku;
            else:
                $formattedId = $variant->variantId;
            endif;
        else:
            if($this->googleUser->settings->productIdFormat == "global"):
                $formattedId =  "Shopify_".$this->googleUser->settings->country."_".$productId."_".$variant['id'];
             elseif($this->googleUser->settings->productIdFormat == "sku"):
                $formattedId = $variant['sku'];
             else:
                $formattedId = $variant['id'];
             endif;
        endif;
        if($full):
            return "online:".$this->googleUser->settings->language.":".$this->googleUser->settings->country.":".$formattedId;
        endif;
        return $formattedId;
    }

    public function updateProducts($products,$googleUser = null, $token = null)
    {
        if(!$this->googleUser):
            $this->googleUser = $googleUser ? $googleUser : auth()->user();
        endif;
        if(!$this->token):
            $this->token = $token ? $token : $this->googleUser->settings->googleAccessToken;
        endif;
        foreach ($products as $product):
            if($this->user->settings->whichProducts != "all"):
                $product['variants'] = $this->shopifyApiRequest("getVariants", $product['id'],["limit" => 100],['body','variants'],$this->googleUser);
            endif;
            $values = [
                // 'channel' => "online",
                // "targetCountry" => $this->user->settings->country,
                // "contentLanguage" => $this->user->settings->language,
                "googleProductCategory" => $this->user->settings->product_category_id,
                // "brand" => $this->user->settings->domain
            ];
            if($this->user->settings->gender != "blank"):
                $this->values['gender'] = $this->user->settings->gender;
            endif;
            if($this->user->settings->productCondition != "blank"):
                $this->values['condition'] = $this->user->settings->productCondition;
            endif;
            if($this->user->settings->ageGroup != "blank"):
                $this->values['ageGroup'] = $this->user->settings->ageGroup;
            endif;
            $this->uploadProductToMerchantAccount($this->values,$this->user);
        endforeach;
    }

    // public function getGoogleProduct($variant,$productId,$googleUser =null, $token=null){
    //     if(!$this->googleUser):
    //         $this->googleUser = $googleUser ? $googleUser : auth()->user();
    //     endif;
    //     if(!$this->token):
    //         $this->token = $token ? $token : $this->googleUser->settings->googleAccessToken;
    //     endif;
    //     return $this->ContentApiRequest('getProduct',[$this->googleUser->settings->merchantAccountId,$this->convertVariantToGoogleFormat($variant,$productId,true,true)],null,'get');
    // }

    // public function deleteProductFromFeed($productId,$googleUser =null, $token=null)
    // {
    //     if(!$this->googleUser):
    //         $this->googleUser = $googleUser ? $googleUser : auth()->user();
    //     endif;
    //     if(!$this->token):
    //         $this->token = $token ? $token : $this->googleUser->settings->googleAccessToken;
    //     endif;
    //     return $this->ContentApiRequest('deleteProduct',[$this->googleUser->settings->merchantAccountId,$productId],null,'delete');
    // }

}