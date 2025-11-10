<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PayablesTable;
use App\Filament\Widgets\ReceivablesTable;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

class FinanceDashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Finance Dashboard';

    protected static null|string|BackedEnum $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static null|string|\UnitEnum $navigationGroup = 'Finance';

    protected static string $routePath = 'finance';

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            PayablesTable::class,
            ReceivablesTable::class,
        ];
    }
}
