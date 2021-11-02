<?php

namespace App\Jobs;

use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\Http\Traits\GoogleApiTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncProductStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GoogleApiTrait;
    public $timeout = 9000;
    public $tries = 1;

    public function __construct()
    {
        //
    }
    
    public function handle()
    {
        $shops = User::where('updated_at', '<=', Carbon::now()->subDay())
            ->whereNotNull('password')
            ->whereHas('settings', function ($query) {
                $query->whereNotNull('merchantAccountId');
                $query->where('setup',1);
            })
            ->has('products.variants')
            ->with('products.variants')
            ->get();
        foreach ($shops as $user) :
            $chunkLimit = $user->settings->variantSubmission == 'all' ? 10 : 1000;
            foreach ($user->products->chunk($chunkLimit) as $chunk ) :
                $this->updateProductStatuses($chunk,$user);
            endforeach;
            $user->touch();
        endforeach;
    }

}
