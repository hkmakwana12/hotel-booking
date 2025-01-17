<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 4;

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('first_name')
                        ->label("First Name")
                        ->required()
                        ->maxWidth(128),
                    Forms\Components\TextInput::make('last_name')
                        ->label("Last Name")
                        ->maxWidth(128),
                    Forms\Components\TextInput::make('email')
                        ->label("Email Address")
                        ->email()
                        ->maxWidth(128),
                    Forms\Components\TextInput::make('phone')
                        ->label("Phone Number")
                        ->tel()
                        ->maxWidth(15),
                    Forms\Components\Textarea::make('address')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('country_id')
                        ->relationship('country', 'name')
                        ->searchable()
                        ->preload()
                        ->default(233)
                        ->live()
                        ->afterStateUpdated(
                            fn(callable $set) => $set('state_id', null)
                        ),
                    Forms\Components\Select::make('state_id')
                        ->relationship('state', 'name')
                        ->searchable()
                        ->preload()
                        ->label("State / Province")
                        ->options(
                            fn(Forms\Get $get): Collection => State::query()
                                ->where("country_id", $get("country_id"))
                                ->pluck("name", "id")
                        ),
                    Forms\Components\TextInput::make('city')
                        ->maxWidth(128),
                    Forms\Components\TextInput::make('zipcode')
                        ->label("Zip / Postal code")
                        ->maxWidth(10),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('state.name')
                    ->label('State / Province')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('zipcode')
                    ->label('Zip / Postal code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
