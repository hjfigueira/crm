<?php

namespace App\Filament\Resources\Entities\Schemas;

use App\Models\Tenant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Support\Colors\Color;
use Filament\Schemas\Components\Utilities\Get;

class EntityForm
{
    public static function configure(Schema $schema): Schema
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
        $user = auth()->user();
        $isSuper = $user && method_exists($user, 'hasRole') && ($user->hasRole('Super Admin', null) || $user->hasRole('Super Admin'));

        return $schema
            ->components([
                Section::make('General')
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->description('Dados básicos e tipo de cadastro')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nome')
                                    ->placeholder('Nome do contato ou razão social')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Radio::make('type')
                                    ->label('Tipo')
                                    ->inline()
                                    ->options([
                                        'person' => 'Pessoa Física',
                                        'company' => 'Pessoa Jurídica',
                                    ])
                                    ->default('person')
                                    ->live(),
                            ]),

                        Grid::make(3)
                            ->key('dynamicTypeFields')
                            ->schema(fn (Get $get): array => match ($get('type')) {
                                'company' => [
                                    Fieldset::make('Empresa')
                                        ->schema([
                                            TextInput::make('company_legal_name')
                                                ->label('Razão social')
                                                ->maxLength(255)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($state, callable $set) => $set('name', $state)),
                                            TextInput::make('company_trade_name')
                                                ->label('Nome fantasia')
                                                ->maxLength(255),
                                            TextInput::make('company_cnpj')
                                                ->label('CNPJ')
                                                ->maxLength(20),
                                            TextInput::make('company_state_registration')
                                                ->label('Inscrição estadual')
                                                ->maxLength(50),
                                            TextInput::make('company_municipal_registration')
                                                ->label('Inscrição municipal')
                                                ->maxLength(50),
                                            TextInput::make('company_website')
                                                ->label('Website')
                                                ->url()
                                                ->maxLength(255),
                                        ])
                                        ->columns(2)
                                        ->columnSpanFull(),
                                ],
                                default => [
                                    Fieldset::make('Pessoa')
                                        ->schema([
                                            Grid::make(2)->schema([
                                                TextInput::make('person_first_name')
                                                    ->label('Nome')
                                                    ->maxLength(100)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, callable $set, Get $get): void {
                                                        $last = (string) $get('person_last_name');
                                                        $set('name', trim($state . ' ' . $last));
                                                    }),
                                                TextInput::make('person_last_name')
                                                    ->label('Sobrenome')
                                                    ->maxLength(100)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, callable $set, Get $get): void {
                                                        $first = (string) $get('person_first_name');
                                                        $set('name', trim($first . ' ' . $state));
                                                    }),
                                            ]),
                                            TextInput::make('person_cpf')
                                                ->label('CPF')
                                                ->maxLength(20),
                                            TextInput::make('person_rg')
                                                ->label('RG')
                                                ->maxLength(50),
                                            DatePicker::make('person_birth_date')
                                                ->label('Data de nascimento')
                                                ->native(false),
                                            Select::make('person_gender')
                                                ->label('Gênero')
                                                ->options([
                                                    'female' => 'Feminino',
                                                    'male' => 'Masculino',
                                                    'other' => 'Outro',
                                                ])
                                                ->preload(),
                                        ])
                                        ->columns(2)
                                        ->columnSpanFull(),
                                ],
                            }),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                Section::make('Contato')
                    ->icon(Heroicon::OutlinedAtSymbol)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('email')->email()->label('E-mail')->maxLength(255),
                                TextInput::make('phone')->label('Telefone')->maxLength(30),
                                TextInput::make('mobile')->label('Celular')->maxLength(30),
                            ]),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                Section::make('Endereço')
                    ->icon(Heroicon::OutlinedMapPin)
                    ->schema([
                        Grid::make(6)->schema([
                            TextInput::make('address_postal_code')->label('CEP')->maxLength(20),
                            TextInput::make('address_state')->label('UF')->maxLength(2),
                            TextInput::make('address_city')->label('Cidade')->maxLength(100)->columnSpan(2),
                            TextInput::make('address_district')->label('Bairro')->maxLength(100)->columnSpan(2),
                            TextInput::make('address_line1')->label('Endereço')->maxLength(255)->columnSpan(3),
                            TextInput::make('address_number')->label('Número')->maxLength(50),
                            TextInput::make('address_line2')->label('Complemento')->maxLength(255)->columnSpan(2),
                            TextInput::make('address_country')->label('País')->maxLength(2)->default('BR'),
                        ]),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                Section::make('Notas')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->schema([
                        RichEditor::make('notes')
                            ->label('Anotações')
                            ->toolbarButtons(['bold','italic','strike','link','bulletList','orderedList'])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                ...($isSuper
                    ? [
                        Section::make('Tenant')
                            ->schema([
                                Select::make('tenant_id')
                                    ->label('Tenant')
                                    ->options(fn () => Tenant::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),
                            ])
                            ->collapsed(),
                    ]
                    : [
                        Hidden::make('tenant_id')->default($tenant?->id),
                    ]
                ),
            ])
            ->columns(1);
    }
}
