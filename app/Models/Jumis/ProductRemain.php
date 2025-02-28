<?php

namespace App\Models\Jumis;

use App\Jumis;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRemain extends Jumis
{
    protected $table = 'ProductRemain';
    protected $primaryKey = 'ProductRemainID';

    protected $casts = [
        'LastInterWarehouseTransfer'=> 'date',
        'LastSalesInvoice' => 'date',
        'PurchaseInvoice' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'PartnerID', 'PartnerID');
    }
}
