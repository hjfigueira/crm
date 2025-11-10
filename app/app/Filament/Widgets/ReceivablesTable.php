<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Illuminate\Database\Eloquent\Builder;

class ReceivablesTable extends BaseTableWidget
{
    protected static ?string $heading = 'Receivables';

    public function getTableQuery(): Builder
    {
        return Transaction::query()
            ->where('type', Transaction::TYPE_RECEIVABLE)
            ->with(['costCenter', 'project'])
            ->orderByDesc('due_date');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')->label('Description')->searchable()->sortable(),
                TextColumn::make('amount')->money('USD', true)->sortable(),
                TextColumn::make('due_date')->date()->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Transaction::STATUS_PENDING => 'warning',
                        Transaction::STATUS_PAID => 'success',
                        Transaction::STATUS_OVERDUE => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('costCenter.name')->label('Cost Center')->toggleable()->sortable()->searchable(),
                TextColumn::make('project.name')->label('Project')->toggleable()->sortable()->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Transaction::STATUS_PENDING => 'Pending',
                        Transaction::STATUS_PAID => 'Paid',
                        Transaction::STATUS_OVERDUE => 'Overdue',
                    ]),
                SelectFilter::make('cost_center_id')
                    ->label('Cost Center')
                    ->relationship('costCenter', 'name'),
                SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name'),
                Filter::make('due_date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from'),
                        \Filament\Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('due_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('due_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('markAsPaid')
                    ->label('Mark Received')
                    ->icon(Heroicon::OutlinedCheck)
                    ->visible(fn (Transaction $record): bool => $record->status !== Transaction::STATUS_PAID)
                    ->action(function (Transaction $record, array $data): void {
                        // For receivables, marking as received still uses payment_date/status logic
                        $record->markAsPaid($data['payment_date'] ?? null);
                    })
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('payment_date')->label('Received Date')->default(now()),
                    ])
                    ->requiresConfirmation(),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()->label('Export Receivables'),
            ])
            ->paginated([10, 25, 50]);
    }
}
