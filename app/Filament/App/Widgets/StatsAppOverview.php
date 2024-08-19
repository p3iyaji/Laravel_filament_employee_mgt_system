<?php

namespace App\Filament\App\Widgets;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsAppOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Users', Team::find(filament::getTenant())->first()->members->count())
                ->description('All users')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Departments', Department::query()->whereBelongsTo(filament::getTenant())->count())
                ->description('All teams')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Employees', Employee::query()->whereBelongsTo(filament::getTenant())->count())
                ->description('All employees')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
