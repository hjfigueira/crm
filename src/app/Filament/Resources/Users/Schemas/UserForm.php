<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('User Details')
                            ->schema([
                                TextInput::make('name')
                                    ->required(),
                                TextInput::make('email')
                                    ->label('Email address')
                                    ->email()
                                    ->required(),
                                DateTimePicker::make('email_verified_at'),
                            ]),
                        Tab::make('Roles and Permissions')
                            ->schema([
                                Section::make('Roles')
                                    ->columnSpanFull()
                                    ->schema([
                                        \Filament\Forms\Components\CheckboxList::make('roles')
                                            ->label('Roles')
                                            ->relationship('roles', 'name')
                                            ->columns(4),
                                    ]),
                                Section::make('Permissions')
                                    ->columnSpanFull()
                                    ->schema([
                                        \Filament\Forms\Components\CheckboxList::make('permissions')
                                            ->label('Permissions')
                                            ->relationship('permissions', 'name')
                                            ->columns(4),
                                    ]),
                            ]),
                        Tab::make('Security')
                            ->schema([
                                Section::make('Change Password')
                                    ->description('Leave blank to keep the current password.')
                                    ->collapsible()
                                    ->columnSpanFull()
                                    ->collapsed()
                                    ->schema([
                                        TextInput::make('password')
                                            ->label('New Password')
                                            ->password()
                                            ->revealable()
                                            ->minLength(8)
                                            ->rule('confirmed')
                                            ->dehydrated(fn ($state): bool => filled($state))
                                            ->required(fn (string $context): bool => $context === 'create'),
                                        TextInput::make('password_confirmation')
                                            ->label('Confirm Password')
                                            ->password()
                                            ->revealable()
                                            ->dehydrated(false),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
