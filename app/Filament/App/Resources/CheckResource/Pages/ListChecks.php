<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\CheckResource\Pages;

use App\Filament\App\Resources\CheckResource;
use App\Filament\Exports\CheckExporter;
use App\Filament\Imports\CheckImporter;
use App\Models\Check;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ListChecks extends ListRecords
{
    protected static string $resource = CheckResource::class;

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->sortable()
                ->searchable(),
            TextColumn::make('type')
                ->sortable()
                ->searchable(),
            TextColumn::make('endpoint')
                ->sortable()
                ->searchable(),
            TextColumn::make('http_method')
                ->sortable()
                ->searchable(),
            TextColumn::make('interval')
                ->suffix(' seconds')
                ->sortable(),
            IconColumn::make('active')
                ->boolean(),
        ])->actions([
            ViewAction::make(),
            EditAction::make(),
        ])
            ->bulkActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(CheckExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()->badge(Check::query()->count()),
            'active' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true)),
            'inactive' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false)),
        ];
    }

    public function getDefaultActiveTab(): string
    {
        return 'active';
    }

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(CheckImporter::class),
            ExportAction::make()
                ->exporter(CheckExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
