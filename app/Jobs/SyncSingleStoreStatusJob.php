<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Http\Traits\GoogleApiTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncSingleStoreStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GoogleApiTrait;
    public $timeout = 9000;
    public $tries = 1;
    protected $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $chunkLimit = $this->user->settings->variantSubmission == 'all' ? 10 : 1000;
        foreach ($this->user->products->chunk($chunkLimit) as $chunk ) :
            $this->updateProductStatuses($chunk,$this->user);
        endforeach;
    }
}
