<?php

namespace App\Filament\Resources\Finance\TransactionResource\Pages;

use App\Filament\Resources\Finance\TransactionResource;
use App\Filament\Resources\Finance\TransactionResource\Widgets\OverdueTransactionsTable;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    /**
     * @return array<class-string>
     */
    protected function getFooterWidgets(): array
    {
        return [
            OverdueTransactionsTable::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }
}
