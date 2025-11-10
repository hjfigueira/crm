<?php

namespace App\Filament\Resources\Finance;

use App\Models\CostCenter;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CostCenterResource extends Resource
{
    protected static ?string $model = CostCenter::class;

    protected static null|string|BackedEnum $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static null|string|\UnitEnum $navigationGroup = 'Finance';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make()->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('description')->maxLength(500)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('description')->limit(60)->toggleable(),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => CostCenterResource\Pages\ListCostCenters::route('/'),
            'create' => CostCenterResource\Pages\CreateCostCenter::route('/create'),
            'edit' => CostCenterResource\Pages\EditCostCenter::route('/{record}/edit'),
        ];
    }
}
