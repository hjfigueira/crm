<?php

namespace App\Filament\Resources\Entities\Schemas;

use Filament\Infolists\Components\Grid as InfoGrid;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EntityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                InfoSection::make('Resumo')
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->schema([
                        InfoGrid::make(3)->schema([
                            TextEntry::make('name')->label('Nome')->columnSpan(2),
                            TextEntry::make('type')->label('Tipo')->formatStateUsing(fn (string $state): string => $state === 'company' ? 'Pessoa Jurídica' : 'Pessoa Física'),
                        ]),
                    ]),

                InfoSection::make('Contato')
                    ->icon(Heroicon::OutlinedAtSymbol)
                    ->schema([
                        InfoGrid::make(3)->schema([
                            TextEntry::make('email')->label('E-mail')->copyable(),
                            TextEntry::make('phone')->label('Telefone')
                                ->hidden(fn ($record) => empty($record->phone)),
                            TextEntry::make('mobile')->label('Celular')
                                ->hidden(fn ($record) => empty($record->mobile)),
                        ]),
                    ]),

                InfoSection::make('Endereço')
                    ->icon(Heroicon::OutlinedMapPin)
                    ->schema([
                        InfoGrid::make(3)->schema([
                            TextEntry::make('address_line1')->label('Endereço')->columnSpan(2),
                            TextEntry::make('address_number')->label('Número'),
                            TextEntry::make('address_line2')->label('Complemento')->hidden(fn ($record) => empty($record->address_line2)),
                            TextEntry::make('address_district')->label('Bairro'),
                            TextEntry::make('address_city')->label('Cidade'),
                            TextEntry::make('address_state')->label('UF'),
                            TextEntry::make('address_postal_code')->label('CEP'),
                            TextEntry::make('address_country')->label('País'),
                        ]),
                    ]),

                InfoSection::make('Pessoa Jurídica')
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->hidden(fn ($record) => $record->type !== 'company')
                    ->schema([
                        InfoGrid::make(2)->schema([
                            TextEntry::make('company_legal_name')->label('Razão social'),
                            TextEntry::make('company_trade_name')->label('Nome fantasia'),
                            TextEntry::make('company_cnpj')->label('CNPJ'),
                            TextEntry::make('company_state_registration')->label('Inscrição estadual'),
                            TextEntry::make('company_municipal_registration')->label('Inscrição municipal'),
                            TextEntry::make('company_website')->label('Website')->url(fn ($state) => $state)->openUrlInNewTab(),
                        ]),
                    ]),

                InfoSection::make('Pessoa Física')
                    ->icon(Heroicon::OutlinedUser)
                    ->hidden(fn ($record) => $record->type !== 'person')
                    ->schema([
                        InfoGrid::make(2)->schema([
                            TextEntry::make('person_first_name')->label('Nome'),
                            TextEntry::make('person_last_name')->label('Sobrenome'),
                            TextEntry::make('person_cpf')->label('CPF'),
                            TextEntry::make('person_rg')->label('RG'),
                            TextEntry::make('person_birth_date')->label('Data de nascimento')->date('d/m/Y'),
                            TextEntry::make('person_gender')->label('Gênero')->formatStateUsing(fn ($state) => match ($state) {
                                'female' => 'Feminino',
                                'male' => 'Masculino',
                                default => 'Outro',
                            }),
                        ]),
                    ]),

                InfoSection::make('Notas')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->schema([
                        TextEntry::make('notes')->label('Anotações')->prose(),
                    ]),
            ]);
    }
}
