<?php

namespace App\Jobs;

use App\Http\Traits\GoogleApiTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteSingleProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GoogleApiTrait;
    protected $user;
    protected $product;

    public function __construct($user,$product)
    {
        $this->user = $user;
        $this->product = $product;
    }

    public function handle()
    {
        $this->product->load('variants');
        $toDelete = [];
        foreach ($this->product->variants as $key => $variant) :
            $toDelete[] = [
                "batchId" => $key,
                "merchantId" => $this->user->settings->merchantAccountId,
                "method" => "delete",
                'productId' => $this->convertVariantToGoogleFormat($variant,$this->product->productId,true,true,$this->user)
            ];
        endforeach;
        $this->deleteBulkProductsFromMerchantAccount(['entries' => $toDelete],$this->user);
        $this->product->delete();
    }
}
