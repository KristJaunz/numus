<?php

    namespace App\Filament\Resources\TenderResource\Widgets;

    use App\Models\Tender;
    use Filament\Forms\Components\DatePicker;
    use Filament\Forms\Components\Grid;
    use Filament\Widgets\StatsOverviewWidget;
    use Filament\Widgets\StatsOverviewWidget\StatCard;
    use Filament\Forms\Contracts\HasForms;
    use Filament\Forms\Concerns\InteractsWithForms;
    use Illuminate\Support\Carbon;

    class TenderStatsOverview extends StatsOverviewWidget implements HasForms
    {
    use InteractsWithForms;

    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    protected function getFormSchema(): array
    {
    return [
    Grid::make(2)->schema([
    DatePicker::make('dateFrom')
    ->label('Datums no')
    ->default(Carbon::today()),

    DatePicker::make('dateTo')
    ->label('Datums līdz')
    ->default(Carbon::today()),
    ]),
    ];
    }

    protected function getCards(): array
    {
    $from = $this->dateFrom ? Carbon::parse($this->dateFrom)->startOfDay() : Carbon::today()->startOfDay();
    $to = $this->dateTo ? Carbon::parse($this->dateTo)->endOfDay() : Carbon::today()->endOfDay();

    $seriesData = Tender::query()
    ->selectRaw('doc_no_serial, COUNT(*) as transaction_count,
    SUM(amount_cash) as total_cash,
    SUM(amount_card) as total_card,
    SUM(amount_gift) as total_gift,
    SUM(amount_cash + amount_card + amount_gift) as total_amount')
    ->whereBetween('created_at', [$from, $to])
    ->groupBy('doc_no_serial')
    ->get();

    $cards = [];

    foreach ($seriesData as $series) {
    $cards[] = StatCard::make("Sērija: {$series->doc_no_serial}", "{$series->transaction_count} darījumi")
    ->description(
    "Skaidrā: " . number_format($series->total_cash, 2) . " EUR | " .
    "Karte: " . number_format($series->total_card, 2) . " EUR | " .
    "Dāvanu karte: " . number_format($series->total_gift, 2) . " EUR | " .
    "Kopā: " . number_format($series->total_amount, 2) . " EUR"
    );
    }

    return $cards;
    }
    }

