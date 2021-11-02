<?php

namespace App\Console\Commands;

use App\Jobs\SyncProductStatusJob;
use Illuminate\Console\Command;

class SyncProductStatusCommand extends Command
{

    protected $signature = 'Google:SyncProductStatus';

    protected $description = 'Sync Google Uploaded Products To Shopify';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        SyncProductStatusJob::dispatch();
    }
}
