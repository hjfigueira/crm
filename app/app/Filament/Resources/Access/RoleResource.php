<?php

namespace App\Filament\Resources\Access;

use App\Filament\Resources\Access\RoleResource\Pages;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Hidden;
use Filament\Schemas\Components\Select as SchemaSelect;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Role;
use App\Models\Tenant;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    // Used by Filament's tenancy to determine ownership relation on the model
    public static ?string $tenantOwnershipRelationshipName = 'tenant';

    protected static null|string|\UnitEnum $navigationGroup = 'Access Control';

    protected static null|string|\BackedEnum $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Roles';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        // Allow Super Admins always; otherwise require manage roles permission in current tenant
        $isSuper = method_exists($user, 'hasRole') && ($user->hasRole('Super Admin', null) || $user->hasRole('Super Admin'));

        if ($isSuper) {
            return true;
        }

        return method_exists($user, 'hasPermissionTo') && app()->bound('currentTenant')
            ? $user->hasPermissionTo('manage roles', app('currentTenant'))
            : false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        $isSuper = $user && method_exists($user, 'hasRole') && ($user->hasRole('Super Admin', null) || $user->hasRole('Super Admin'));

        if ($isSuper) {
            return $query; // no restriction
        }

        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if ($tenant !== null) {
            $query->where('tenant_id', $tenant->id);
        } else {
            $query->whereRaw('1 = 0'); // no tenant context, hide all
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
                ->label('Role Name'),
            TextInput::make('guard_name')
                ->default('web')
                ->maxLength(255)
                ->helperText('Guard to use for this role (usually "web").'),
            // Tenant selection: Super Admin can choose, others hidden & set to current tenant
            ...($isSuper
                ? [
                    SchemaSelect::make('tenant_id')
                        ->label('Tenant')
                        ->options(fn () => Tenant::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ]
                : [
                    Hidden::make('tenant_id')
                        ->default($tenant?->id),
                ]
            ),
            SchemaSelect::make('permissions')
                ->label('Permissions')
                ->relationship('permissions', 'name', modifyQueryUsing: function (Builder $query) use ($isSuper, $tenant): void {
                    if (! $isSuper && $tenant !== null) {
                        $query->where('permissions.tenant_id', $tenant->id);
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
                    ->label('Role')
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
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}

namespace App\Filament\Resources\Access\RoleResource\Pages;

use App\Filament\Resources\Access\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;
}

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Non-super admins: force to current tenant
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

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;
}
