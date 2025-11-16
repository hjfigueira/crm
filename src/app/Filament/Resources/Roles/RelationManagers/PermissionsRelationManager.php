<?php

namespace App\Filament\Resources\Roles\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Manage the many-to-many relationship between Roles and Permissions
 * via the `role_has_permissions` pivot table provided by spatie/laravel-permission.
 */
class PermissionsRelationManager extends RelationManager
{
    /**
     * The relationship name on the parent model.
     */
    protected static string $relationship = 'permissions';

    /**
     * Display attribute for related records.
     */
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Permission')->searchable()->sortable(),
                TextColumn::make('guard_name')->label('Guard')->sortable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(false)
                    ->recordSelectSearchColumns(['name'])
                    ->recordSelectOptionsQuery(function ($query) {
                        return $query
                            ->where('guard_name', $this->getOwnerRecord()->guard_name)
                            ->orderBy('name');
                    }),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->bulkActions([
                DetachBulkAction::make(),
            ]);
    }
}
