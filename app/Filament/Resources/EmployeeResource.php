<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\RelationManagers\StatesRelationManager;
use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\City;
use App\Models\Department;
use App\Models\Employee;
use App\Models\State;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Employee Management';

    //the below will run a global search on a single column
    protected static ?string $recordTitleAttribute = 'first_name';

    //this also handles single colunm but overwrites the above search style
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->last_name;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name','last_name', 'zip_code', 'department.name', 'country.name', 'state.name', 'city.name'];
    }

    //global search that will give you some details in the search
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
          'Country' => $record->country->name,
            'State' => $record->state->name,
            'City' => $record->city->name,
            'Department' => $record->department->name
        ];
    }

    //using eloquent query builder to speed up the resources thereby making the above load faster
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['country', 'state', 'city', 'department']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() < 5 ? 'warning' : 'success';
    }

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
                        ->options(Department::all()->pluck('name', 'id'))
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
                SelectFilter::make('Department')
                    ->relationship('department', 'name')
                    ->preload()
                    ->searchable()
                    ->label('Filter by Department')
                    ->indicator('Department'),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'From ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    })

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    //->successNotification(null)
                   // ->successNotificationTitle('Employee Deleted')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Employee Deleted')
                        ->body('The Employee deleted successfully'))

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
