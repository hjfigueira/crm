<?php

namespace App\Filament\Resources\Finance\TransactionResource\Widgets;

use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

/**
 * Displays only overdue transactions, to be embedded on the Transactions list page.
 */
class OverdueTransactionsTable extends BaseTableWidget
{
    protected static ?string $heading = 'Overdue Transactions';

    /**
     * Limit the widget to the Transaction model and filter to overdue.
     */
    public function getTableQuery(): Builder
    {
        return Transaction::query()->overdue()->orderBy('due_date');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('amount')
                    ->money('USD', true)
                    ->sortable(),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('days_overdue')
                    ->label('Days Overdue')
                    ->sortable(),
                TextColumn::make('costCenter.name')
                    ->label('Cost Center')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('project.name')
                    ->label('Project')
                    ->toggleable()
                    ->searchable(),
            ])
            ->actions([
                Action::make('markAsPaid')
                    ->label('Mark Paid')
                    ->icon(Heroicon::OutlinedCheck)
                    ->visible(fn (Transaction $record): bool => $record->status !== Transaction::STATUS_PAID)
                    ->action(function (Transaction $record): void {
                        $record->markAsPaid();
                        Notification::make()
                            ->title('Transaction marked as paid')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('sendReminder')
                    ->label('Send Reminder')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->action(function (Transaction $record): void {
                        // In a real app, dispatch a job / notification here.
                        Notification::make()
                            ->title('Reminder sent')
                            ->body('A reminder was sent for: ' . $record->description)
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->headerActions([
                // Optional: quick export of overdue items from the widget
                ExportAction::make()
                    ->label('Export Overdue'),
            ])
            ->paginated([10, 25, 50]);
    }
}
