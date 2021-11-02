<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use App\Http\Traits\GoogleApiTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ChangeSettingFeedDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GoogleApiTrait;
    public $timeout = 9000;
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        $this->user->load('products.variants');
        $count = 1;
        $limit = $this->user->settings->variantSubmission == 'all' ? 10 : 1000;
        foreach ($this->user->products->chunk($limit) as $chunk ) :
            $toDelete = [];
            foreach ($chunk as $p) :
                foreach ($p->variants as $variant) :
                    $toDelete[] = [
                        "batchId" => $count,
                        "merchantId" => $this->user->settings->merchantAccountId,
                        "method" => "delete",
                        'productId' => $this->convertVariantToGoogleFormat($variant,$p->productId,true,true,$this->user)
                    ];
                    $count++;
                endforeach;
            endforeach;
            $this->deleteBulkProductsFromMerchantAccount(['entries' => $toDelete],$this->user);         
        endforeach;
        $this->user->products()->delete();
    }
}
