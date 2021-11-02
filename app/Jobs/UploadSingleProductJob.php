<?php

namespace App\Jobs;

use App\Models\ShopProduct;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Http\Traits\GoogleApiTrait;
use App\Models\ShopProductVariant;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UploadSingleProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GoogleApiTrait;
    public $timeout = 9000;
    public $tries = 1;
    protected $user;
    protected $product;
    protected $variantId;
    protected $variant;
    protected $values;
    protected $toUpload;

    public function __construct($shop,$product,$variantId)
    {
        $this->user = $shop;
        $this->product = $product;
        $this->variantId = $variantId;
        $this->toUpload = [];
    }

    public function handle()
    {
        if($this->user->settings->merchantAccountId):
            if($this->product->published_at):
                $this->product = json_decode(json_encode($this->product),true);
                $this->variant = collect($this->product['variants'])->where('id',$this->variantId)->first();
                $dbproduct = ShopProduct::where(['user_id' => $this->user->id,'productId' => $this->product['id']])->first();
                if($dbproduct):
                    $dbvariant = ShopProductVariant::where(['user_id' => $this->user->id, 'productId' => $this->product['id'], 'variantId' => $this->variantId])->first();
                    if(!$dbvariant):
                        ShopProductVariant::create([
                            'user_id' => $this->user->id,
                            'productId' => $this->product['id'],
                            'shop_product_id' => $dbproduct->id,
                            'variantId' => $this->variantId,
                            'sku' => $this->variant['sku']
                        ]);
                    endif;
                else:
                    if($this->planCheck($this->user)):
                        return;
                    endif;
                    $single = [
                        'user_id' => $this->user->id,
                        'productId' => $this->product['id'],
                        'title' => $this->product['title'],
                        'image' => $this->product['image']['src'] ?? '',
                        'product_category_id' => $this->user->settings->product_category_id,
                        'ageGroup' => $this->user->settings->ageGroup,
                        'gender' => $this->user->settings->gender,
                        'productCondition' => $this->user->settings->productCondition,
                        'variants' => [
                            [
                                'user_id' => $this->user->id,
                                'productId' => $this->product['id'],
                                'variantId' => $this->variant['id'],
                                'sku' => $this->variant['sku']
                            ] 
                        ]
                    ];    
                    $dbproduct = new ShopProduct($single);
                endif;
                $this->makeFeed($this->product,$dbproduct);
            endif;
        endif;
    }

    public function makeFeed($product,$dbproduct)
    {
        $this->values = [
            'channel' => "online",
            "targetCountry" => $this->user->settings->country,
            "contentLanguage" => $this->user->settings->language,
            "adult" => false,
            "brand" => $this->user->settings->domain,
            'description' => Str::limit($product['body_html'], 4990, '(...)'),
            "canonicalLink" => "https://".$this->user->settings->domain."/collections/all/products/".$product['handle'],
            'link' => "https://".$this->user->settings->domain."/collections/all/products/".$product['handle'].'?variant='.$this->variant['id'].'&utm_source=Google&utm_medium=Merchant%20Products%20Sync&utm_campaign=ALPHAFEED&utm_content=Shopping%20Ads',
            'mpn' => $this->variant['sku'],
            'shippingWeight' => [
                "value" => $this->variant['weight'],
                "unit" => $this->variant['weight_unit']
            ]
        ];
        if(isset($product['product_type'])):
            if($product['product_type']):
                $this->values['productTypes'] = [ $product['product_type'] ];
            endif;
        endif;
        $this->values['availability'] = "out of stock";
        if($product['published_at']):
            if(isset($this->variant['inventory_quantity'])):
                $this->values['availability'] = $this->variant['inventory_quantity'] > 0 ? "in stock" : "out of stock" ;
            endif;
        endif;
        if($dbproduct->product_category_id):
            $this->values['googleProductCategory'] = $dbproduct->category->value;
        endif;
        if($this->user->settings->shipping == 'auto'):
            $this->values['shippingLabel'] = config('googleApi.strings.AutomaticShippingLabel');
            $this->values['shipping'] = [
                'price' => [
                    "value" => 0,
                    'currency' => $this->user->settings->currency
                ],
                'country' => $this->user->settings->country
            ];
        endif;
        $this->values['gender'] =$dbproduct->gender;
        $this->values['condition'] = $dbproduct->productCondition;
        $this->values['ageGroup'] = $dbproduct->ageGroup;        
        foreach ($dbproduct->labels as $cKey => $value) :
            $this->values['customLabel'.$cKey] = $value->label;
        endforeach;
        if($this->user->settings->additionalImages):
            $this->values['additionalImageLinks'] = array_slice(array_map(function($element){
                return $element['src'];
            },collect($product['images'])->toArray()),0,9);
        endif;
        $src = false;
        foreach($product['images'] as $image):
            foreach ($image['variant_ids'] as $variantImageId) {
                if($variantImageId == $this->variant['id']):
                    $src = $image['src'];
                    break;
                endif;
            }
            if($src):
                break;
            endif;
        endforeach;
        $this->values['imageLink'] = $src ? $src : $dbproduct->image;
        if($this->user->settings->salePrice):
            if($this->variant['compare_at_price']):
                if($this->variant['compare_at_price'] > $this->variant['price']):
                    $this->values['price'] =[
                        'value' => $this->variant['compare_at_price'],
                        'currency' => $this->user->settings->currency
                    ];
                    $this->values['salePrice'] =[
                        'value' => $this->variant['price'],
                        'currency' => $this->user->settings->currency
                    ];
                else:
                    $this->values['price'] =[
                        'value' => $this->variant['price'],
                        'currency' => $this->user->settings->currency
                    ];
                endif;
            else:
                $this->values['price'] = [
                    "value" => $this->variant['price'],
                    'currency' => $this->user->settings->currency
                ];
            endif;
        else:
            $this->values['price'] = [
                "value" => $this->variant['price'],
                'currency' => $this->user->settings->currency
            ];
        endif;
        $this->values['item_group_id'] = $product['id'];
        if($this->user->settings->productIdFormat == "global"):
            $this->values['id'] = "Shopify_".$this->user->settings->country."_".$product['id']."_".$this->variantId;
        elseif($this->user->settings->productIdFormat == "sku"):
            $this->values['id'] = $this->variant['sku'];
        else:
            $this->values['id'] = $this->variantId;
        endif;
        $this->values['offerId'] =  $this->values['id'];
        if(isset($this->variant['barcode']) && $this->user->settings->gtinSubmission):
            $this->values['gtin'] = $this->variant['barcode'];
        endif;
        $titlearr = [];
        for ($i=1; $i <= 3; $i++):
            if($this->variant["option".$i] != null):
                if($this->variant["option".$i] != 'Default Title'):
                    $titlearr[] = $this->variant["option".$i];
                endif;
            endif;
        endfor;
        $this->values['title'] = $product['title'].((count($titlearr) > 0 ) ? "/".implode('/',$titlearr) : '');
        for ($i=0; $i < count($product['options']); $i++) :
            if(in_array(strtolower($product['options'][$i]['name']),['color','size','material'])):
                if(strtolower($product['options'][$i]['name']) == 'size'):
                    $this->values['sizes'] = [$this->variant['option'.($i+1)]];
                else:
                    $this->values[strtolower($product['options'][$i]['name'])] = $this->variant['option'.($i+1)];
                endif;
            endif;
        endfor;
        $this->toUpload[] = [
            "batchId" => time(),
            "merchantId" => $this->user->settings->merchantAccountId,
            "method" => "insert",
            "product" => $this->values
        ];
        $this->uploadBulkProductsToMerchantAccount(['entries' => $this->toUpload],$this->user);
    }
}
