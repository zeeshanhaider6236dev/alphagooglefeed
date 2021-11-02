<?php

namespace App\Jobs;

use App\Models\ShopProduct;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Http\Traits\GoogleApiTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateProductDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GoogleApiTrait;
    public $timeout = 9000;
    public $tries = 1;
    protected $user;
    protected $products;
    protected $validated;
    protected $values;
    protected $toUpload;
    protected $count;
    
    public function __construct($shop,$products,$validated)
    {
        $this->user = $shop;
        $this->products = $products;
        $this->validated = $validated;
        $this->toUpload = [];
        $this->count = 0;
    }

    public function handle()
    {
        if($this->user->settings->merchantAccountId):
            $found = ShopProduct::where('user_id' , $this->user->id)->whereIn('productId',$this->products->pluck('id')->toArray())->get();
            $foundIds = $found->pluck('productId');
            $allIds = $this->products->where('published_at','!=',null)->pluck('id');
            $toInsertIds = $allIds->diff($foundIds);
            $inserted = collect([]);
            foreach ($toInsertIds as $pId):
                if($this->planCheck($this->user)):
                    break;
                endif;
                $single = [
                    'user_id'       => $this->user->id,
                    'productId'     => $pId,
                    'title'         => $this->products->where('id',$pId)->first()['title'],
                    'image'         => $this->products->where('id',$pId)->first()['image']['src']
                ];
                foreach ($this->products->where('id',$pId)->first()['variants'] as $pv) :
                    $single['variants'][] = [
                        'user_id'   => $this->user->id,
                        'productId' => $pId,
                        'variantId' => $pv['id'],
                        'sku'       => $pv['sku']
                    ];
                    if($this->user->settings->variantSubmission == 'first'):
                        break;                    
                    endif;
                endforeach;
                $inserted[] = new ShopProduct($single);
            endforeach;
            $found = $inserted->merge($found);
            foreach ($found as $p) :
                $single = [];
                if($this->validated['title']):
                    $single['seoTitle'] = $this->validated['title'];
                endif;
                if($this->validated['description']):
                    $single['seoDescription'] = $this->validated['description'];
                endif;
                if(isset($this->validated['product_category_id'])):
                    $single['product_category_id'] = $this->validated['product_category_id'];
                endif;
                if($this->validated['ageGroup']):
                    $single['ageGroup'] = $this->validated['ageGroup'];
                endif;
                if($this->validated['gender']):
                    $single['gender'] = $this->validated['gender'];
                endif;
                if($this->validated['productCondition']):
                    $single['productCondition'] = $this->validated['productCondition'];
                endif;
                if(isset($this->validated['customLabel'])):
                    foreach ($this->validated['customLabel'] as $pc) :
                        $single['labels'][] = [
                            'label'     => $pc,
                            'productId' => $p->productId,
                            'user_id'   => $this->user->id
                        ];
                    endforeach;
                endif;
                $p->fill($single)->save();
            endforeach;
            $this->makeFeed($this->products,$found);
        endif;
    }

    public function makeFeed($products,$dbproducts)
    {
        foreach ($dbproducts as $p) :
            $product = $products->where('id',$p->productId)->first();
            $this->values = [
                'channel' => "online",
                "targetCountry" => $this->user->settings->country,
                "contentLanguage" => $this->user->settings->language,
                "adult" => false,
                "brand" => $this->user->settings->domain
            ];
            if(isset($product['product_type'])):
                if($product['product_type']):
                    $this->values['productTypes'] = [ $product['product_type'] ];
                endif;
            endif;
            if(isset($this->validated['product_category_id']) || $this->user->settings->product_category_id):
                $this->values['googleProductCategory'] = $p->product_category_id ?  $p->category->value : $this->user->settings->productCategory->value;
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
            $this->values['gender'] = $this->validated['gender'] ? $this->validated['gender'] : $this->user->settings->gender;
            $this->values['condition'] = $this->validated['productCondition'] ? $this->validated['productCondition'] : $this->user->settings->productCondition;
            $this->values['ageGroup'] = $this->validated['ageGroup'] ? $this->validated['ageGroup'] : $this->user->settings->ageGroup;
            if(isset($this->validated['customLabel'])):
                foreach ($this->validated['customLabel'] as $cKey => $value) :
                    $this->values['customLabel'.$cKey] = $value;
                endforeach;
            else:
                foreach ($p->labels as $cKey => $value) :
                    $this->values['customLabel'.$cKey] = $value->label;
                endforeach;
            endif;
            if($this->user->settings->additionalImages):
                $this->values['additionalImageLinks'] = array_slice(array_map(function($element){
                    return $element['src'];
                },$product['images']),0,9);
            endif;
            $this->values = array_merge($this->values,[
                "description" => $this->validated['description'] ? $this->validated['description'] : ($p->seoDescription ? $p->seoDescription : Str::limit($product['body_html'], 4990, '(...)') ),
                "canonicalLink" => "https://".$this->user->settings->domain."/collections/all/products/".$product['handle'],
            ]);
            foreach ($p->variants as $v):
                $variant = collect($product['variants'])->where('id',$v->variantId)->first();
                $this->values['availability'] = "out of stock";
                if($product['published_at']):
                    if(isset($variant['inventory_quantity'])):
                        $this->values['availability'] = $variant['inventory_quantity'] > 0 ? "in stock" : "out of stock" ;
                    endif;
                endif;
                $src = false;
                foreach($product['images'] as $image):
                    foreach ($image['variant_ids'] as $varaintImageId) {
                        if($varaintImageId == $variant['id']):
                            $src = $image['src'];
                            break;
                        endif;
                    }
                    if($src):
                        break;
                    endif;
                endforeach;
                $this->values['imageLink'] = $src ? $src : $p->image;
                $this->values['link'] = "https://".$this->user->settings->domain."/collections/all/products/".$product['handle']."?variant=".$variant['id'].'&utm_source=Google&utm_medium=Merchant%20Products%20Sync&utm_campaign=ALPHAFEED&utm_content=Shopping%20Ads';

                if($this->user->settings->salePrice):
                    if($variant['compare_at_price']):
                        if($variant['compare_at_price'] > $variant['price']):
                            $this->values['price'] =[
                                'value' => $variant['compare_at_price'],
                                'currency' => $this->user->settings->currency
                            ];
                            $this->values['salePrice'] =[
                                'value' => $variant['price'],
                                'currency' => $this->user->settings->currency
                            ];
                        else:
                            $this->values['price'] =[
                                'value' => $variant['price'],
                                'currency' => $this->user->settings->currency
                            ];
                        endif;
                    else:
                        $this->values['price'] = [
                            "value" => $variant['price'],
                            'currency' => $this->user->settings->currency
                        ];
                    endif;
                else:
                    $this->values['price'] = [
                        "value" => $variant['price'],
                        'currency' => $this->user->settings->currency
                    ];
                endif;
                $this->values['item_group_id'] = $product['id'];
                if($this->user->settings->productIdFormat == "global"):
                    $this->values['id'] = "Shopify_".$this->user->settings->country."_".$product['id']."_".$variant["id"];
                elseif($this->user->settings->productIdFormat == "sku"):
                        $this->values['id'] = $variant['sku'];
                else:
                    $this->values['id'] = $variant['id'];
                endif;
                $this->values['offerId'] =  $this->values['id'];
                $this->values['mpn'] = $variant['sku'];
                $this->values['shippingWeight'] = [
                    "value" => $variant['weight'],
                    "unit" => $variant['weight_unit']
                ];
                if(isset($variant['barcode']) && $this->user->settings->gtinSubmission):
                    $this->values['gtin'] = $variant['barcode'];
                endif;
                $titlearr = [];
                for ($i=1; $i <= 3; $i++) :
                    if($variant["option".$i] != null):
                        if($variant["option".$i] != 'Default Title'):
                            $titlearr[] = $variant["option".$i];
                        endif;
                    endif;
                endfor;
                $this->values['title'] = ( $p->seoTitle ? $p->seoTitle : $product['title'] ).((count($titlearr) > 0 ) ? "/".implode('/',$titlearr) : '');
                for ($i=0; $i < count($product['options']); $i++) :
                    if(in_array(strtolower($product['options'][$i]['name']),['color','size','material'])):
                        if(strtolower($product['options'][$i]['name']) == 'size'):
                            $this->values['sizes'] = [
                                Str::limit($variant['option'.($i+1)],'95','...')
                            ];
                        else:
                            $this->values[strtolower($product['options'][$i]['name'])] = Str::limit($variant['option'.($i+1)],'95','...');
                        endif;
                    endif;
                endfor;
                $this->toUpload[] = [
                    "batchId" => $this->count,
                    "merchantId" => $this->user->settings->merchantAccountId,
                    "method" => "insert",
                    "product" => $this->values
                ];
                $this->count++;
            endforeach;
        endforeach;
        // info(json_encode($this->toUpload));
        $this->uploadBulkProductsToMerchantAccount(['entries' => $this->toUpload],$this->user);
    }
}
