<?php

namespace App\Jobs;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Http\Traits\GoogleApiTrait;
use App\Models\ShopProduct;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UploadAllProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GoogleApiTrait;
    public $timeout = 9000;
    public $tries = 1;
    protected $user;
    protected $values;
    protected $products;
    protected $toUpload;
    protected $count;
    
    public function __construct($shop)
    {
        $this->user = $shop;
        $this->toUpload = [];
    }

    public function handle()
    {   
        if($this->user->settings->merchantAccountId):
            $limit = $this->user->settings->variantSubmission == 'all' ? 10 : 250;
            if($this->user->settings->whichProducts == "all"):
                $requests =  $this->shopifyApiRequest("getProducts", null , [ 'limit' => $limit ],null,$this->user);
            else:
                $requests =  $this->shopifyApiRequest("getCollectionProducts", $this->user->settings->collectionsId , [ 'limit' => $limit ],null,$this->user);
            endif;
            $this->count = 1;
            $products = json_decode(json_encode($requests['body']['products']),true);
            $dbproducts = $this->addProductsToDatabase($products);
            $this->makeFeed($products,$dbproducts);
            while(isset($requests['link']['next'])):
                if($this->planCheck($this->user)):
                    break;
                endif;
                if($this->user->settings->whichProducts == "all"):
                    $requests =  $this->shopifyApiRequest("getProducts", null , ['limit' => $limit, 'page_info' => $requests['link']['next']],null,$this->user);
                else:
                    $requests =  $this->shopifyApiRequest("getCollectionProducts", $this->user->settings->collectionsId ,['limit' => $limit, 'page_info' => $requests['link']['next']],null,$this->user);
                endif;
                if(isset($requests['body']['products'])):
                    $this->toUpload = [];
                    $products = json_decode(json_encode($requests['body']['products']),true);
                    $dbproducts = $this->addProductsToDatabase($products);
                    $this->makeFeed($products,$dbproducts);
                endif;
            endwhile;
        endif;
    }

    public function makeFeed($products,$dbproducts)
    {
        foreach ($dbproducts as $p) :
            $product = collect($products)->where('id',$p->productId)->first();
            if($this->user->settings->whichProducts != "all"):
                $product['variants'] = $this->shopifyApiRequest("getVariants", $product['id'],["limit" => 100],['body','variants'],$this->user);
            endif;
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
            if($p->product_category_id):
                $this->values['googleProductCategory'] = $p->category->value;
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
            $this->values['gender'] = $p->gender;
            $this->values['condition'] = $p->productCondition;
            $this->values['ageGroup'] = $p->ageGroup;
            if($this->user->settings->additionalImages):
                $this->values['additionalImageLinks'] = array_slice(array_map(function($element){
                    return $element['src'];
                },$product['images']),0,9);
            endif;
            $this->values = array_merge($this->values,[
                "description" => Str::limit($product['body_html'], 4990, '(...)'),
                "canonicalLink" => "https://".$this->user->settings->domain."/collections/all/products/".$product['handle'],
            ]);
            foreach ($product['variants'] as $variant) :
                $this->values['availability'] = "out of stock";
                if($product['published_at']):
                    if(isset($variant['inventory_quantity'])):
                        $this->values['availability'] = $variant['inventory_quantity'] > 0 ? "in stock" : "out of stock" ;
                    endif;
                endif;
                $this->values['link'] = "https://".$this->user->settings->domain."/collections/all/products/".$product['handle']."?variant=".$variant['id'].'utm_source=Google&utm_medium=Merchant%20Products%20Sync&utm_campaign=ALPHAFEED&utm_content=Shopping%20Ads';
                $src = false;
                foreach($product['images'] as $image):
                    foreach ($image['variant_ids'] as $variantImageId) {
                        if($variantImageId == $variant['id']):
                            $src = $image['src'];
                            break;
                        endif;
                    }
                    if($src):
                        break;
                    endif;
                endforeach;
                $this->values['imageLink'] = $src ? $src : $p->image;
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
                else:
                    $this->values['price'] = [
                        "value" => $variant['price'],
                        'currency' => $this->user->settings->currency
                    ];
                endif;
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
                for ($i=1; $i <= 3; $i++):
                    if($variant["option".$i] != null):
                        if($variant["option".$i] != 'Default Title'):
                            $titlearr[] = $variant["option".$i];
                        endif;
                    endif;
                endfor;
                $this->values['title'] = $product['title'].((count($titlearr) > 0 ) ? "/".implode('/',$titlearr) : '');
                for ($i=0; $i < count($product['options']); $i++) :
                    if(in_array(strtolower($product['options'][$i]['name']),['color','size','material'])):
                        if($variant['option'.($i+1)]):
                            if(strtolower($product['options'][$i]['name']) == 'size'):
                                $this->values['sizes'] = [Str::limit($variant['option'.($i+1)],'95', '...')];
                            else:
                                $this->values[strtolower($product['options'][$i]['name'])] = Str::limit($variant['option'.($i+1)],'95', '...');
                            endif;
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
                if($this->user->settings->variantSubmission == "first"):
                    break;
                endif;
            endforeach;
        endforeach;
        $this->uploadBulkProductsToMerchantAccount(['entries' => $this->toUpload],$this->user);
    }

    public function addProductsToDatabase($products)
    {
        $dbproducts = [];
        foreach($products as $product):
            if($product['published_at']):
                if($this->user->settings->whichProducts != "all"):
                    $product['variants'] = $this->shopifyApiRequest("getVariants", $product['id'],["limit" => 100],['body','variants'],$this->user);
                endif;
                $single = [
                    'user_id' => $this->user->id,
                    'productId' => $product['id'],
                    'title' => $product['title'],
                    'image' => $product['image']['src'] ?? '',
                    'product_category_id' => $this->user->settings->product_category_id,
                    'ageGroup' => $this->user->settings->ageGroup,
                    'gender' => $this->user->settings->gender,
                    'productCondition' => $this->user->settings->productCondition
                ];    
                $flag = false;
                if($this->user->settings->variantSubmission == 'first'):
                    $flag = true;
                endif;
                foreach ($product['variants'] as $variant) :
                    $single['variants'][] = [
                        'user_id' => $this->user->id,
                        'productId' => $product['id'],
                        'variantId' => $variant['id'],
                        'sku' => $variant['sku']
                    ] ;
                    if($flag):
                        break;
                    endif;
                endforeach;
                $dbproducts [] = new ShopProduct($single);
            endif;
        endforeach;
        return collect($dbproducts);
    }
}
