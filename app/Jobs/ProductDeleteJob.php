<?php namespace App\Jobs;

use App\Http\Traits\GoogleApiTrait;
use App\Models\ShopProduct;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProductDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GoogleApiTrait;

    
    public $shopDomain;
    public $data;

    
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
        ->whereHas('products', function ($query) use ($product) {
            $query->where('productId',$product->id);
        })
        ->has('products.variants')
        ->with(['products' => function($q) use ($product) {
            $q->where('productId',$product->id);
        },'products.variants'])
        ->first();
        if($this->user):
            $dbproduct = $this->user->products->first();
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
    }
}
