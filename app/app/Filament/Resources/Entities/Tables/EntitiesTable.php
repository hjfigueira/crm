<?php

namespace App\Filament\Resources\Entities\Tables;

use App\Models\Tenant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EntitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'person',
                        'info' => 'company',
                    ])
                    ->formatStateUsing(fn (string $state): string => $state === 'company' ? 'Pessoa Jurídica' : 'Pessoa Física')
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Telefone')
                    ->toggleable(),

                TextColumn::make('tenant_id')
                    ->label('Tenant')
                    ->formatStateUsing(function ($state): string {
                        if ($state === null) {
                            return '—';
                        }
                        $name = Tenant::query()->whereKey($state)->value('name');
                        return $name ? $name : (string) $state;
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'person' => 'Pessoa Física',
                        'company' => 'Pessoa Jurídica',
                    ]),
                SelectFilter::make('tenant_id')
                    ->label('Tenant')
                    ->options(fn () => Tenant::query()->pluck('name', 'id')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
