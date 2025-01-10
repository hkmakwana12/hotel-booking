<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $modelLabel = 'Room';

    protected static ?string $recordTitleAttribute = 'room_number';

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('branch_id')
                    ->label('Branch')
                    ->options(fn() => Branch::pluck('name', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateHydrated(
                        fn($set, $state) => $state == null && $set('category_id', null)
                    )
                    ->afterStateHydrated(
                        fn($set, $get) => $set('branch_id', Category::where('id', $get('category_id'))->value('branch_id'))
                    ),
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(
                        fn(callable $get) => Category::where('branch_id', $get('branch_id'))->pluck('name', 'id')->toArray()
                    )
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive(),
                Forms\Components\TextInput::make('room_number')
                    ->required()
                    ->maxLength(128)
                    ->label('Room Number'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('room_number')
                    ->searchable()
                    ->sortable()
                    ->label('Room Number'),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRooms::route('/'),
        ];
    }
}
