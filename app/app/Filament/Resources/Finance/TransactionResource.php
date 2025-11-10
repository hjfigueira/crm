<?php

namespace App\Filament\Resources\Finance;

use App\Filament\Resources\Finance\TransactionResource\Pages;
use App\Models\Transaction;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static null|string|BackedEnum $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static null|string|\UnitEnum $navigationGroup = 'Finance';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make()->schema([
                TextInput::make('description')->required()->maxLength(255),
                Select::make('type')
                    ->options([
                        Transaction::TYPE_PAYABLE => 'Payable',
                        Transaction::TYPE_RECEIVABLE => 'Receivable',
                    ])->required(),
                TextInput::make('category')->maxLength(255),
                TextInput::make('amount')->numeric()->required()->prefix('$'),
                DatePicker::make('due_date')->required(),
                DatePicker::make('payment_date'),
                Select::make('status')
                    ->options([
                        Transaction::STATUS_PENDING => 'Pending',
                        Transaction::STATUS_PAID => 'Paid',
                        Transaction::STATUS_OVERDUE => 'Overdue',
                    ])->required(),
            ])->columns(2),
            Grid::make()->schema([
                Select::make('cost_center_id')
                    ->label('Cost Center')
                    ->relationship('costCenter', 'name')
                    ->searchable(),
                Select::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name')
                    ->searchable(),
            ])->columns(2),
            FileUpload::make('attachment_path')
                ->label('Attachment')
                ->directory('attachments/transactions')
                ->visibility('private')
                ->downloadable()
                ->openable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')->searchable()->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Transaction::TYPE_PAYABLE => 'danger',
                        Transaction::TYPE_RECEIVABLE => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
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
                TextColumn::make('costCenter.name')->label('Cost Center')->toggleable()->sortable(),
                TextColumn::make('project.name')->label('Project')->toggleable()->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        Transaction::TYPE_PAYABLE => 'Payable',
                        Transaction::TYPE_RECEIVABLE => 'Receivable',
                    ]),
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
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('due_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('due_date', '<=', $date));
                    }),
            ])
            ->actions([
                EditAction::make(),
                Action::make('markAsPaid')
                    ->label('Mark as Paid')
                    ->icon(Heroicon::OutlinedCheck)
                    ->visible(fn (Transaction $record): bool => $record->status !== Transaction::STATUS_PAID)
                    ->action(function (Transaction $record, array $data): void {
                        $record->markAsPaid($data['payment_date'] ?? null);
                    })
                    ->form([
                        DatePicker::make('payment_date')->label('Payment Date')->default(now()),
                    ])
                    ->requiresConfirmation(),
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->action(function (Transaction $record): void {
                        $copy = $record->replicate();
                        $copy->status = Transaction::STATUS_PENDING;
                        $copy->payment_date = null;
                        $copy->save();
                    }),
                DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make(),
                CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
