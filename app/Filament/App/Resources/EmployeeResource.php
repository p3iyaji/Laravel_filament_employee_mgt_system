<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\EmployeeResource\Pages;
use App\Filament\App\Resources\EmployeeResource\RelationManagers;
use App\Models\City;
use App\Models\Department;
use App\Models\Employee;
use App\Models\State;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Details')
                    ->description('Localization details')
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->relationship(name: 'country', titleAttribute: 'name') //if not native false then we get the traditional select with ...select and option
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function(Set $set){
                                $set('state_id', null);
                                $set('city_id', null);
                            }  )
                            ->required(),
                        Forms\Components\Select::make('state_id')
                            ->options(fn(Get $get): Collection => State::query()
                                ->where('country_id', $get('country_id'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('city_id', null) )
                            ->required(),
                        Forms\Components\Select::make('city_id')
                            ->options(fn(Get $get): Collection => City::query()
                                ->where('state_id', $get('state_id'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('department_id')
                            //->options(Department::all()->pluck('name', 'id'))
                              ->relationship(
                                  name: 'department',
                                  titleAttribute: 'name',
                                  modifyQueryUsing: fn(Builder $query) => $query->whereBelongsTo(Filament::getTenant())
                            )
                            ->required()
                    ])->columns(4),

                Forms\Components\Section::make('User Name')
                    ->description('Put the user name details here')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('middle_name')
                            ->required()
                            ->maxLength(255),
                    ])->columns(3),
                Forms\Components\Section::make('Address and Dates')
                    ->description('Enter address and date details here.')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('zip_code')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('dated_of_birth')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                        Forms\Components\DatePicker::make('date_hire')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                    ])->columns(4)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('middle_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('country.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('city.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('department.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('zip_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dated_of_birth')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_hire')
                    ->date()
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Employee Info')
                    ->schema([
                        TextEntry::make('first_name'),
                        TextEntry::make('last_name'),
                        TextEntry::make('middle_name'),
                    ])->columns(3),
                Section::make('Country Details')
                    ->schema([
                        TextEntry::make('country.name'),
                        TextEntry::make('state.name'),
                        TextEntry::make('city.name'),
                    ])->columns(3),
                Section::make('Address Info')
                    ->schema([
                        TextEntry::make('address'),
                        TextEntry::make('zip_code'),
                        TextEntry::make('department.name'),

                    ])->columns(3),
                Section::make('Date Info')
                    ->schema([
                        TextEntry::make('date_of_birth'),
                        TextEntry::make('date_hire')
                    ])->columns(2)

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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
