<?php

namespace App\Jobs;

use App\Components\DB\TenderImport;
use App\Models\Settings;
use App\Models\Shop;
use App\Models\Tender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SyncTender implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $import = new TenderImport();
        try
        {
            $shops = Shop::list();

            foreach ($shops as $shop) {
                Tender::where('doc_no_serial', $shop->doc_serial)
                    ->whereNull('deleted_at')
                    ->with('docLines')
                    ->chunk(Settings::read('doc_confirm_chunk',100),function ($documents) use ($import) {
                        foreach ($documents as $tender) {
                            $import->importStoreDocWithRetries($tender);
                        }
                    });
            }

        } catch (\Exception $e) {
            $this->fail($e);
        }
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('sync_tender'))->dontRelease()];
    }
}
