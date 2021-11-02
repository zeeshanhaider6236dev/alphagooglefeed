<?php namespace App\Jobs;

use App\User;
use App\Models\ShopProduct;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Models\ShopProductVariant;
use App\Http\Traits\GoogleApiTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProductUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GoogleApiTrait;

    public $shopDomain;
    public $data;
    public $user;
    protected $values;
    protected $toUpload;
    protected $toDelete;
    protected $count;

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
            ->with(['products' => function($q) use ($product) {
                $q->where('productId',$product->id);
            },'products.variants'])
            ->first();
        if($this->user):
            $dbproduct = null;
            if($this->user->products->count()):
                $dbproduct = $this->user->products->first();
            endif;
            if($this->user->settings->whichProducts != "all"):
                $collectionIds = collect($this->getProductCollectionIds($product->id,$this->user))->pluck('collection_id')->toArray();
                $chk2 = in_array($this->user->settings->collectionsId,$collectionIds);
                if(!$chk2):
                    if($dbproduct):
                        $toDelete = [];
                        foreach ($dbproduct->variants as $key => $variant) :
                            $toDelete[] = [
                                "batchId" => $key,
                                "merchantId" => $this->user->settings->merchantAccountId,
                                "method" => "delete",
                                'productId' => $this->convertVariantToGoogleFormat($variant,$dbproduct->productId,true,true,$this->user)
                            ];
                        endforeach;
                        $this->deleteBulkProductsFromMerchantAccount(['entries' => $toDelete],$this->user);
                        $dbproduct->delete();
                    endif;
                    return;
                endif;
            endif;
            if($dbproduct):
                $dbproduct->update([
                    'title' => $product->title,
                    'image' => $product->image->src ?? ''
                ]);
            else:
                if($this->planCheck($this->user)):
                    return;
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
            endif;
            $existedVariants = $dbproduct->variants->pluck('variantId');
            if($this->user->settings->variantSubmission == 'all'):
                $toAddVariants = collect($product->variants)->pluck('id')->diff($existedVariants);
                $toAddData = [];
                foreach ($toAddVariants as $v) :
                    $variant = collect($product->variants)->where('id',$v)->first();
                    $toAddData[] = [
                        'variantId' => $variant->id,
                        'user_id' => $this->user->id,
                        'sku' => $variant->sku,
                        'productId' => $product->id
                    ];
                endforeach;
                $dbproduct->variants()->createMany($toAddData);
                $dbproduct->load('variants');
            endif;
            $toDeleteVariants = $existedVariants->diff(collect($product->variants)->pluck('id'));
            $toDelete = [];
            foreach ($toDeleteVariants as $key => $variant) :
                $toDelete[] = [
                    "batchId" => $key,
                    "merchantId" => $this->user->settings->merchantAccountId,
                    "method" => "delete",
                    'productId' => $this->convertVariantToGoogleFormat($variant,$dbproduct->productId,true,true,$this->user)
                ];
            endforeach;
            $this->deleteBulkProductsFromMerchantAccount(['entries' => $toDelete],$this->user);
            ShopProductVariant::where(['productId' => $dbproduct->productId])->whereIn('variantId',$toDeleteVariants)->delete();
            $this->count =1;
            $this->toUpload = [];
            $this->makeFeed($product,$dbproduct);
        endif;
    }
    public function makeFeed($product,$dbproduct)
    {
        foreach ($dbproduct->variants as $dv) :
            $variant = collect($product->variants)->where('id',$dv->variantId)->first();
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
            if($this->user->settings->product_category_id || $dbproduct->product_category_id):
                $this->values['googleProductCategory'] = $dbproduct->product_category_id ? $dbproduct->category->value : $this->user->settings->productCategory->value;
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
            if($this->user->settings->gender || $dbproduct->gender):
                $this->values['gender'] = $dbproduct->gender ? $dbproduct->gender : $this->user->settings->gender;
            endif;
            if($dbproduct->productCondition || $this->user->settings->productCondition):
                $this->values['condition'] = $dbproduct->productCondition ? $dbproduct->productCondition : $this->user->settings->productCondition;
            endif;
            if($dbproduct->ageGroup || $this->user->settings->ageGroup):
                $this->values['ageGroup'] = $dbproduct->ageGroup ? $dbproduct->ageGroup : $this->user->settings->ageGroup;
            endif;
            foreach ($dbproduct->labels as $cKey => $value) :
                $this->values['customLabel'.$cKey] = $value->label;
            endforeach;
            if($this->user->settings->additionalImages):
                $this->values['additionalImageLinks'] = array_slice(array_map(function($element){
                    return $element->src;
                },$product->images),0,9);
            endif;
            $this->values = array_merge($this->values,[
                "description" => $dbproduct->seoDescription ? $dbproduct->seoDescription : Str::limit($product->body_html, 4990, ' (...)'),
                "canonicalLink" => "https://".$this->user->settings->domain."/collections/all/products/".$product->handle,
            ]);
            $this->values['availability'] = "out of stock";
            if($product->published_at):
                if(isset($variant->inventory_quantity)):
                    $this->values['availability'] = $variant->inventory_quantity > 0 ? "in stock" : "out of stock" ;
                endif;
            endif;
            $src = false;
            foreach($product->images as $image):
                foreach ($image->variant_ids as $varaintImageId) {
                    if($varaintImageId == $variant->id):
                        $src = $image->src;
                        break;
                    endif;
                }
                if($src):
                    break;
                endif;
            endforeach;
            $this->values['imageLink'] = $src ? $src : $dbproduct->image;
            $this->values['link'] = "https://".$this->user->settings->domain."/collections/all/products/".$product->handle."?variant=".$variant->id.'&utm_source=Google&utm_medium=Merchant%20Products%20Sync&utm_campaign=ALPHAFEED&utm_content=Shopping%20Ads';
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
                        $this->values['price'] =[
                            'value' => $variant->price,
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
            $this->values['item_group_id'] = $product->id;
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
            for ($i=1; $i <= 3; $i++) :
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
                "batchId" => $this->count,
                "merchantId" => $this->user->settings->merchantAccountId,
                "method" => "insert",
                "product" => $this->values
            ];
            $this->count++;
        endforeach;
        $this->uploadBulkProductsToMerchantAccount(['entries' => $this->toUpload],$this->user);
    }
}
