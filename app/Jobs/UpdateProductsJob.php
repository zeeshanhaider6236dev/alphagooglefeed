<?php

namespace App\Jobs;

use App\Models\ShopProduct;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Models\ShopProductVariant;
use App\Http\Traits\GoogleApiTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GoogleApiTrait;
    public $timeout = 9000;
    public $tries = 1;
    protected $user;
    protected $products;
    protected $productIds;
    protected $varientIds;
    protected $validated;
    protected $variant;
    protected $values;
    protected $toUpload;
    
    public function __construct($shop,$products,$productIds,$varientIds,$validated)
    {
        $this->user = $shop;
        $this->products = $products;
        $this->productIds = $productIds;
        $this->varientIds = $varientIds;
        $this->validated = $validated;
        $this->toUpload = [];
    }

    public function handle()
    {
        if($this->user->settings->merchantAccountId):
            $inserted = collect([]);
            foreach ($this->productIds as $key => $pId) :
                $p = ShopProduct::where(['user_id' => $this->user->id,'productId' => $pId])->first();
                if($p):
                    $inserted [] = $p;
                    $v = ShopProductVariant::where(['user_id' => $this->user->id,'productId' => $pId,'variantId' => $this->varientIds[$key]])->first();
                    if(!$v):
                        $vs = collect($this->products->where('id',$pId)->first()['variants']);
                        ShopProductVariant::create([
                            'user_id' => $this->user->id,
                            'productId' => $pId,
                            'shop_product_id' => $p->id,
                            'variantId' => $this->varientIds[$key],
                            'sku' => $vs->where('id',$this->varientIds[$key])->first()['sku']
                        ]);
                    endif;
                else:
                    $vs = collect($this->products->where('id',$pId)->first()['variants']);
                    $single = [
                        'user_id' => $this->user->id,
                        'productId' => $pId,
                        'title' => $this->products->where('id',$pId)->first()['title'],
                        'image' => $this->products->where('id',$pId)->first()['image']['src'],
                        'variants' => [
                            [
                                'user_id' => $this->user->id,
                                'productId' => $pId,
                                'variantId' => $this->varientIds[$key],
                                'sku' => $vs->where('id',$this->varientIds[$key])->first()['sku']
                            ] 
                        ]
                    ];    
                    $inserted[] = new ShopProduct($single);
                endif;
            endforeach;
            $this->makeFeed($this->products,$inserted);
        endif;
    }

    public function makeFeed($products,$dbProducts)
    {
        foreach ($this->varientIds as $key => $variantId):
            $this->values = [
                'channel' => "online",
                "targetCountry" => $this->user->settings->country,
                "contentLanguage" => $this->user->settings->language,
                "adult" => false,
                "brand" => $this->user->settings->domain
            ];
            if($this->user->settings->shipping == 'auto'):
                $this->values['shipping'] = [
                    'price' => [
                        "value" => 0,
                        'currency' => $this->user->settings->currency
                    ],
                    'country' => $this->user->settings->country
                ];
            endif;
            if(isset($this->validated['product_category_id'])):
                $this->values['googleProductCategory'] = $this->validated['product_category_id'];
            endif;
            if(isset($this->validated['gender']) && $this->validated['gender'] != 'blank'):
                $this->values['gender'] = $this->validated['gender'];
            endif;
            if(isset($this->validated['productCondition']) && $this->validated['productCondition'] != 'blank'):
                $this->values['condition'] = $this->validated['productCondition'];
            endif;
            if(isset($this->validated['ageGroup']) && $this->validated['ageGroup'] != 'blank'):
                $this->values['ageGroup'] = $this->validated['ageGroup'];
            endif;
            if(isset($this->validated['cutomLabel'])):
                foreach ($this->validated['cutomLabel'] as $cKey => $value) :
                    $this->values['customLabel'.$cKey] = $value;
                endforeach;
            endif;
            foreach ($products as $pKey => $product):
                if($product['id'] == $this->productIds[$key]):
                    if($product['published_at']):
                        $dbproduct = $dbProducts->where('productId',$product['id'])->first();
                        if(isset($product['product_type'])):
                            if($product['product_type']):
                                $this->values['productTypes'] = [ $product['product_type'] ];
                            endif;
                        endif;
                        foreach ($product['variants'] as $pVariant):
                            if($pVariant['id'] == $variantId):
                                $this->variant = $pVariant;
                                break;
                            endif;
                        endforeach;
                        if($this->user->settings->additionalImages):
                            $this->values['additionalImageLinks'] = array_slice(array_map(function($element){
                                return $element['src'];
                            },collect($product['images'])->toArray()),0,9);
                        endif;
                        $this->values = array_merge($this->values,[
                            "description" => ($dbproduct->seoDescription ? $dbproduct->seoDescription :  Str::limit($product['body_html'], 4990, ' (...)')),
                            "canonicalLink" => "https://".$this->user->settings->domain."/collections/all/products/".$product['handle'],
                        ]);
                        $this->values['availability'] = "out of stock";
                        if($product['published_at']):
                            if(isset($this->variant['inventory_quantity'])):
                                $this->values['availability'] = $this->variant['inventory_quantity'] > 0 ? "in stock" : "out of stock" ;
                            endif;
                        endif;
                        $this->values['link'] = "https://".$this->user->settings->domain."/collections/all/products/".$product['handle'].'?utm_source=Google&utm_medium=Merchant%20Products%20Sync&utm_campaign=ALPHAFEED&utm_content=Shopping%20Ads';
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
                        if($src):
                            $this->values['imageLink'] = $src;
                        else:
                            $this->values['imageLink'] = $product['image']['src'];
                        endif;
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
                            $this->values['id'] = "Shopify_".$this->user->settings->country."_".$product['id']."_".$variantId;
                        elseif($this->user->settings->productIdFormat == "sku"):
                            $this->values['id'] = $this->variant['sku'];
                        else:
                            $this->values['id'] = $variantId;
                        endif;
                        $this->values['offerId'] =  $this->values['id'];
                        $this->values['mpn'] = $this->variant['sku'];
                        $this->values['shippingWeight'] = [
                            "value" => $this->variant['weight'],
                            "unit" => $this->variant['weight_unit']
                        ];
                        if(isset($this->variant['barcode'])):
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
                        $this->values['title'] = ($dbproduct->seoTitle ? $dbproduct->seoTitle :  $product['title']).((count($titlearr) > 0 ) ? "/".implode('/',$titlearr) : '');
                        for ($i=0; $i < count($product['options']); $i++) :
                            if(in_array(strtolower($product['options'][$i]['name']),['color','size','material'])):
                                if(strtolower($product['options'][$i]['name']) == 'size'):
                                    $this->values['sizes'] = [Str::limit($this->variant['option'.($i+1)],'95','...')];
                                else:
                                    $this->values[strtolower($product['options'][$i]['name'])] = Str::limit($this->variant['option'.($i+1)],'95','...');
                                endif;
                            endif;
                        endfor;
                        $this->toUpload[] = [
                            "batchId" => $key,
                            "merchantId" => $this->user->settings->merchantAccountId,
                            "method" => "insert",
                            "product" => $this->values
                        ];
                        break;
                    endif;
                endif;
            endforeach;
        endforeach;
        // info(json_encode($this->toUpload));
        $this->uploadBulkProductsToMerchantAccount(['entries' => $this->toUpload],$this->user);
    }
}
