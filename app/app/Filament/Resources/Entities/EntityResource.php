<?php

namespace App\Filament\Resources\Entities;

use App\Filament\Resources\Entities\Pages\CreateEntity;
use App\Filament\Resources\Entities\Pages\EditEntity;
use App\Filament\Resources\Entities\Pages\ListEntities;
use App\Filament\Resources\Entities\Pages\ViewEntity;
use App\Filament\Resources\Entities\Schemas\EntityForm;
use App\Filament\Resources\Entities\Schemas\EntityInfolist;
use App\Filament\Resources\Entities\Tables\EntitiesTable;
use App\Models\Entity;
use App\Models\Tenant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EntityResource extends Resource
{
    protected static ?string $model = Entity::class;

    // Used by Filament's tenancy to determine ownership relation on the model
    public static ?string $tenantOwnershipRelationshipName = 'tenant';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EntityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EntityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EntitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEntities::route('/'),
            'create' => CreateEntity::route('/create'),
            'view' => ViewEntity::route('/{record}'),
            'edit' => EditEntity::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        $isSuper = $user && method_exists($user, 'hasRole') && ($user->hasRole('Super Admin', null) || $user->hasRole('Super Admin'));
        if ($isSuper) {
            return $query;
        }

        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
        if ($tenant !== null) {
            $query->where('tenant_id', $tenant->id);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
