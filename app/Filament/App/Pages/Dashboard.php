<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Filament\App\Widgets\CheckHistoryChart;
use App\Filament\App\Widgets\CheckStatsOverview;
use App\Filament\App\Widgets\ResponseTimeChart;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

final class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('dateRange')
                            ->label(__('dashboard.filters.date_range'))
                            ->options([
                                '24h' => __('dashboard.filters.ranges.24h'),
                                '7d' => __('dashboard.filters.ranges.7d'),
                                '30d' => __('dashboard.filters.ranges.30d'),
                                '90d' => __('dashboard.filters.ranges.90d'),
                                'custom' => __('dashboard.filters.ranges.custom'),
                            ])
                            ->default('30d')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set): void {
                                if ($state !== 'custom') {
                                    $set('startDate', null);
                                    $set('endDate', null);
                                }
                            }),
                        DatePicker::make('startDate')
                            ->label(__('dashboard.filters.start_date'))
                            ->maxDate(now())
                            ->visible(fn (Get $get): bool => $get('dateRange') === 'custom')
                            ->required(fn (Get $get): bool => $get('dateRange') === 'custom'),
                        DatePicker::make('endDate')
                            ->label(__('dashboard.filters.end_date'))
                            ->maxDate(now())
                            ->visible(fn (Get $get): bool => $get('dateRange') === 'custom')
                            ->required(fn (Get $get): bool => $get('dateRange') === 'custom')
                            ->afterOrEqual('startDate'),
                    ])
                    ->columns(3),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            CheckStatsOverview::class,
            CheckHistoryChart::class,
            ResponseTimeChart::class,
        ];
    }

    public function getColumns(): array
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }
}
