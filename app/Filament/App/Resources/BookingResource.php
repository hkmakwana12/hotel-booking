<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Room;
use App\Models\State;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $modelLabel = 'Booking';

    protected static ?string $recordTitleAttribute = 'total_amount';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->options(
                            fn() => Category::where('branch_id', Filament::getTenant()->id)->pluck('name', 'id')->toArray()
                        )
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(
                            fn($set) => $set('room_id', null)
                        )
                        ->afterStateHydrated(
                            function (callable $set, $record) {
                                if ($record)
                                    $set('category_id', $record->room->category_id);
                            }
                        ),
                    Forms\Components\Select::make('room_id')
                        ->label('Room')
                        ->relationship(
                            name: 'room',
                            titleAttribute: 'room_number',
                            modifyQueryUsing: fn(Builder $query, callable $get) => $query->where('category_id', $get('category_id'))
                        )
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(
                            function (callable $set, callable $get) {
                                $set('price', Room::find((int) $get('room_id'))->category->price);
                                static::calculateTotalAmount($set, $get);
                            }
                        ),
                    Forms\Components\Select::make('customer_id')
                        ->label('Customer')
                        ->relationship(
                            name: 'customer',
                            titleAttribute: 'first_name',
                            modifyQueryUsing: fn(Builder $query) => $query->whereBelongsTo(Filament::getTenant())
                                ->orderBy('first_name')->orderBy('last_name')
                        )
                        ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->name} ({$record->email})")
                        ->searchable(['first_name', 'last_name', 'email'])
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\Hidden::make('branch_id')->default(Filament::getTenant()->id),
                            Forms\Components\Group::make([
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
                        ])
                        ->createOptionAction(
                            fn(Action $action) => $action->modalWidth('3xl'),
                        ),
                    Forms\Components\TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            fn($set, $get) => static::calculateTotalAmount($set, $get)
                        ),
                    Forms\Components\DatePicker::make('check_in')
                        ->required()
                        ->default(now()->format('Y-m-d'))
                        ->live()
                        ->afterStateUpdated(
                            fn($set, $get) => static::calculateTotalAmount($set, $get)
                        ),
                    Forms\Components\DatePicker::make('check_out')
                        ->required()
                        ->default(now()->addDay()->format('Y-m-d'))
                        ->live()
                        ->afterStateUpdated(
                            fn($set, $get) => static::calculateTotalAmount($set, $get)
                        ),
                    Forms\Components\TextInput::make('adults')
                        ->required()
                        ->numeric()
                        ->default(1)
                        ->minValue(1),
                    Forms\Components\TextInput::make('children')
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                    Forms\Components\TextInput::make('total_amount')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                    Forms\Components\TextInput::make('arrival_time'),
                    Forms\Components\Textarea::make('notes')
                        ->columnSpanFull(),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.room_number')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state, $record) => $record->room->category->name . ' - ' . $state),
                Tables\Columns\TextColumn::make('check_in')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_days')
                    ->label('No of Days')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return now()->parse($record->check_in)->diffInDays(now()->parse($record->check_out));
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('adults')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable()
                    ->summarize([
                        Sum::make()->money(),
                        Average::make()->money(),
                    ]),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money()
                    ->sortable()
                    ->summarize([
                        Sum::make()->money(),
                        Average::make()->money(),
                    ]),
                Tables\Columns\TextColumn::make('arrival_time')
                    ->searchable()
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
            ->defaultSort(column: 'created_at', direction: 'desc')
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }

    public static function calculateTotalAmount($set, $get)
    {
        $checkIn = $get('check_in');
        $checkOut = $get('check_out');
        $price = $get('price');

        $totalAmount = 0;

        $totalDays = now()->parse($checkIn)->diffInDays(now()->parse($checkOut));

        if ($checkIn && $checkOut && $price) {
            $totalAmount = $price * $totalDays;
        }

        $set('total_amount', $totalAmount);
    }
}
