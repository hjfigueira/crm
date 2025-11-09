<?php

namespace App\Filament\Resources\Access;

use App\Filament\Resources\Access\PermissionResource\Pages;
use App\Models\Tenant;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Hidden;
use Filament\Schemas\Components\Select as SchemaSelect;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static null|string|\UnitEnum $navigationGroup = 'Access Control';

    protected static null|string|\BackedEnum $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Permissions';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if ($user === null) {
            return false;
        }

        $isSuper = method_exists($user, 'hasRole') && ($user->hasRole('Super Admin', null) || $user->hasRole('Super Admin'));
        if ($isSuper) {
            return true;
        }

        return method_exists($user, 'hasPermissionTo') && app()->bound('currentTenant')
            ? $user->hasPermissionTo('manage permissions', app('currentTenant'))
            : false;
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

    public static function form(Schema $schema): Schema
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
        $user = auth()->user();
        $isSuper = $user && method_exists($user, 'hasRole') && ($user->hasRole('Super Admin', null) || $user->hasRole('Super Admin'));

        return $schema->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->label('Permission Name'),
            TextInput::make('guard_name')
                ->default('web')
                ->maxLength(255),
            ...($isSuper
                ? [
                    SchemaSelect::make('tenant_id')
                        ->label('Tenant')
                        ->options(fn () => Tenant::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ]
                : [
                    Hidden::make('tenant_id')->default($tenant?->id),
                ]
            ),
            SchemaSelect::make('roles')
                ->label('Roles')
                ->relationship('roles', 'name', modifyQueryUsing: function (Builder $query) use ($isSuper, $tenant): void {
                    if (! $isSuper && $tenant !== null) {
                        $query->where('roles.tenant_id', $tenant->id);
                    }
                })
                ->multiple()
                ->preload()
                ->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Permission')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->sortable()
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
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tenant_id')
                    ->label('Tenant')
                    ->options(fn () => Tenant::query()->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}

namespace App\Filament\Resources\Access\PermissionResource\Pages;

use App\Filament\Resources\Access\PermissionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;
}

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $isSuper = $user && method_exists($user, 'hasRole') && ($user->hasRole('Super Admin', null) || $user->hasRole('Super Admin'));
        if (! $isSuper && app()->bound('currentTenant')) {
            $data['tenant_id'] = app('currentTenant')->id;
        }
        if (! isset($data['guard_name'])) {
            $data['guard_name'] = 'web';
        }
        return $data;
    }
}

class EditPermission extends EditRecord
{
    protected static string $resource = PermissionResource::class;
}
