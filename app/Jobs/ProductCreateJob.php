<?php namespace App\Jobs;

use App\User;
use App\Models\ShopProduct;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Http\Traits\GoogleApiTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProductCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GoogleApiTrait;
    
    public $shopDomain;
    public $data;
    public $user;
    protected $values;
    protected $toUpload;

    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    public function handle()
    {
        $product = json_decode(json_encode($this->data));
        $this->user = User::where('name', $this->shopDomain->toNative())
            ->whereNotNull('password')
            ->whereHas('settings', function ($query) {
                $query->whereNotNull('merchantAccountId');
                $query->where('setup',1);
            })
            ->first();
        if($product->published_at):
            if($this->planCheck($this->user)):
                return;
            endif;
            if($this->user->settings->whichProducts != 'all'):
                $collectionIds = collect($this->getProductCollectionIds($product->id,$this->user))->pluck('collection_id')->toArray();
                if(!in_array($this->user->settings->collectionsId,$collectionIds)):
                    return;
                endif;
            endif;
            $single = [
                'user_id' => $this->user->id,
                'productId' => $product->id,
                'title' => $product->title,
                'image' => $product->image->src ?? '',
                'product_category_id' => $this->user->settings->product_category_id,
                'ageGroup' => $this->user->settings->ageGroup,
                'gender' => $this->user->settings->gender,
                'productCondition' => $this->user->settings->productCondition
            ];    
            foreach ($product->variants as $variant) :
                $single['variants'][] = [
                    'user_id' => $this->user->id,
                    'productId' => $product->id,
                    'variantId' => $variant->id,
                    'sku' => $variant->sku
                ];
                if($this->user->settings->variantSubmission == 'first'):
                    break;
                endif;
            endforeach;
            $dbproduct = new ShopProduct($single);
            $this->makeFeed($product,$dbproduct);
        endif;
    }

    public function makeFeed($product,$dbproduct)
    {
        $this->values = [
            'channel' => "online",
            "targetCountry" => $this->user->settings->country,
            "contentLanguage" => $this->user->settings->language,
            "adult" => false,
            "brand" => $this->user->settings->domain
        ];
        if(isset($product->product_type)):
            if($product->product_type):
                $this->values['productTypes'] = [ $product->product_type ];
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
        $this->values['gender'] = $dbproduct->gender;
        $this->values['condition'] = $dbproduct->productCondition;
        $this->values['ageGroup'] = $dbproduct->ageGroup;
        if($this->user->settings->additionalImages):
            $this->values['additionalImageLinks'] = array_slice(array_map(function($element){
                return $element->src;
            },$product->images),0,9);
        endif;
        $this->values = array_merge($this->values,[
            "description" => $dbproduct->seoDescription ? $dbproduct->seoDescription : Str::limit($product->body_html, 4990, ' (...)'),        
            "canonicalLink" => "https://".$this->user->settings->domain."/collections/all/products/".$product->handle,
        ]);
        foreach ($dbproduct->variants as $key => $value) :
            $variant = collect($product->variants)->where('id',$value->variantId)->first();
            $this->values['availability'] = "out of stock";
            if(isset($variant->inventory_quantity)):
                $this->values['availability'] = $variant->inventory_quantity > 0 ? "in stock" : "out of stock" ;
            endif;
            $this->values['link'] = "https://".$this->user->settings->domain."/collections/all/products/".$product->handle.'?utm_source=Google&utm_medium=Merchant%20Products%20Sync&utm_campaign=ALPHAFEED&utm_content=Shopping%20Ads';
            $src = false;
            foreach($product->images as $image):
                foreach ($image->variant_ids as $variantImageId) :
                    if($variantImageId == $variant->id):
                        $src = $image->src;
                        break;
                    endif;
                endforeach;
                if($src):
                    break;
                endif;
            endforeach;
            $this->values['imageLink'] = $src ? $src : $dbproduct->image;
            if($this->user->settings->salePrice):
                if($variant->compare_at_price):
                    if($variant->compare_at_price > $variant->price):
                        $this->values['price'] =[
                            'value' => $variant->compare_at_price,
                            'currency' => $this->user->settings->currency
                        ];
                        $this->values['salePrice'] =[
                            'value' => $variant->price,
                            'currency' => $this->user->settings->currency
                        ];
                    else:
                        $this->values['price'] = [
                            "value" => $variant->price,
                            'currency' => $this->user->settings->currency
                        ];
                    endif;
                else:
                    $this->values['price'] = [
                        "value" => $variant->price,
                        'currency' => $this->user->settings->currency
                    ];
                endif;
            else:
                $this->values['price'] = [
                    "value" => $variant->price,
                    'currency' => $this->user->settings->currency
                ];
            endif;
            if($this->user->settings->productIdFormat == "global"):
                $this->values['id'] = "Shopify_".$this->user->settings->country."_".$product->id."_".$variant->id;
            elseif($this->user->settings->productIdFormat == "sku"):
                    $this->values['id'] = $variant->sku;
            else:
                $this->values['id'] = $variant->id;
            endif;
            $this->values['offerId'] =  $this->values['id'];
            $this->values['mpn'] = $variant->sku;
            $this->values['shippingWeight'] = [
                "value" => $variant->weight,
                "unit" => $variant->weight_unit
            ];
            if(isset($variant->barcode) && $this->user->settings->gtinSubmission):
                $this->values['gtin'] = $variant->barcode;
            endif;
            $titlearr = [];
            for ($i=1; $i <= 3; $i++):
                $prop = "option".$i;
                if($variant->$prop != null):
                    if($variant->$prop != 'Default Title'):
                        $titlearr[] = $variant->$prop;
                    endif;
                endif;
            endfor;
            $this->values['title'] = ($dbproduct->seoTitle ? $dbproduct->seoTitle : $product->title).((count($titlearr) > 0 ) ? "/".implode('/',$titlearr) : '');
            for ($i=0; $i < count($product->options); $i++) :
                if(in_array(strtolower($product->options[$i]->name),['color','size','material'])):
                    $prop = "option".($i+1);
                    if(strtolower($product->options[$i]->name) == 'size'):
                        $this->values['sizes'] = [Str::limit($variant->$prop,'95','...')];
                    else:
                        $this->values[strtolower($product->options[$i]->name)] = Str::limit($variant->$prop,'95','...');
                    endif;
                endif;
            endfor;
            $this->toUpload[] = [
                "batchId" => $key,
                "merchantId" => $this->user->settings->merchantAccountId,
                "method" => "insert",
                "product" => $this->values
            ];
        endforeach;
        $this->uploadBulkProductsToMerchantAccount(['entries' => $this->toUpload],$this->user);
    }
}
