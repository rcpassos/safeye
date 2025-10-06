<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Check;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

final class CheckExporter extends Exporter
{
    protected static ?string $model = Check::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('checks.id')),
            ExportColumn::make('name')
                ->label(__('checks.name')),
            ExportColumn::make('group.name')
                ->label(__('checks.group')),
            ExportColumn::make('type')
                ->label(__('checks.type'))
                ->formatStateUsing(fn ($state): string => $state->value),
            ExportColumn::make('endpoint')
                ->label(__('checks.endpoint')),
            ExportColumn::make('http_method')
                ->label(__('checks.http_method'))
                ->formatStateUsing(fn ($state): string => $state->value),
            ExportColumn::make('interval')
                ->label(__('checks.interval')),
            ExportColumn::make('request_timeout')
                ->label(__('checks.request_timeout')),
            ExportColumn::make('request_headers')
                ->label(__('checks.request_headers'))
                ->formatStateUsing(fn ($state): string => $state ? json_encode($state) : ''),
            ExportColumn::make('request_body')
                ->label(__('checks.request_body'))
                ->formatStateUsing(fn ($state): string => $state ? json_encode($state) : ''),
            ExportColumn::make('notify_emails')
                ->label(__('checks.notify_emails')),
            ExportColumn::make('active')
                ->label(__('checks.active'))
                ->formatStateUsing(fn ($state): string => $state ? '1' : '0'),
            ExportColumn::make('assertions_data')
                ->label(__('checks.assertions'))
                ->formatStateUsing(function (Check $record): string {
                    $assertions = $record->assertions->map(fn ($assertion): array => [
                        'type' => $assertion->type->value,
                        'sign' => $assertion->sign->value,
                        'value' => $assertion->value,
                    ])->toArray();

                    return json_encode($assertions);
                }),
            ExportColumn::make('created_at')
                ->label(__('checks.created_at')),
            ExportColumn::make('updated_at')
                ->label(__('checks.updated_at')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('checks.export_completed', [
            'count' => number_format($export->successful_rows),
            'rows' => str('row')->plural($export->successful_rows)->toString(),
        ]);

        if (($failedRowsCount = $export->getFailedRowsCount()) !== 0) {
            $body .= ' '.__('checks.export_failed', [
                'count' => number_format($failedRowsCount),
                'rows' => str('row')->plural($failedRowsCount)->toString(),
            ]);
        }

        return $body;
    }
}
