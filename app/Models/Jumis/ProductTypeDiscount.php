<?php

namespace App\Models\Jumis;


use App\Jumis;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ProductTypeDiscount extends Jumis
{
    protected $table = 'ProductTypeDiscount';
    protected $primaryKey = 'DiscountID';

    protected $casts = [
        'DiscountFrom' => 'date',
        'DiscountTo' => 'date'
    ];

    public function scopeActiveLatest($query){
        return $query->whereDate('DiscountFrom','<=', \Carbon\Carbon::now()->toDateString())
            ->whereDate('DiscountTo','>', Carbon::now()->toDateString())
            ->orderBy('DiscountID','asc');
    }

    public function scopeActiveByAmount($query){
        return $query->whereDate('DiscountFrom','<=', \Carbon\Carbon::now()->toDateString())
            ->whereDate('DiscountTo','>', Carbon::now()->toDateString())
            ->orderBy('DiscountPercent','asc');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class,'ProductID','ProductID');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductType::class,'ProductTypeID','ProductTypeID');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class,'PartnerID','PartnerID');
    }

    public static function calculateDiscount(float $price, float $percentage): float
    {
        return round($price - ($price * ($percentage / 100)),2);
    }

    public static function calculatePercentage($oldPrice, $newPrice): float
    {
        $percentChange = (($oldPrice - $newPrice) / $oldPrice) * 100;
        return round(abs($percentChange),2);
    }

    public function getPrice(float $price): float
    {
        return round($price - ($price * ($this->DiscountPercent / 100)),2);
    }

    public static function getDiscountList(Carbon $date, $currencyId,$priceType,$column = 'DiscountFrom') {

        return ProductTypeDiscount::with('product')->whereDate($column, $date)
            ->when(settings('general', 'discount_discover_by') == 0,
                fn($query) => $query->orderBy('DiscountID','asc'),
                fn($query) => $query->orderBy('DiscountPercent','asc'))

            ->join('ProductPrice', function ($query) use ($currencyId,$priceType) {
                $query->on('ProductPrice.ProductID', '=', 'ProductTypeDiscount.ProductID')
                    ->where('ProductPrice.CurrencyID', $currencyId)
                    ->where('ProductPrice.TypeID', $priceType);
            })
            ->select('ProductTypeDiscount.*', 'ProductPrice.Price')
            ->get();
    }

}
