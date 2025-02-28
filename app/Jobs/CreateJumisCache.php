<?php

namespace App\Jobs;

use App\Models\Jumis\Partner;
use App\Models\Jumis\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateJumisCache implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Product::list();
        Partner::list();
        Partner::listWarehouses();
    }
}
