<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use App\Enums\CheckType;
use App\Enums\HTTPMethod;
use App\Models\Assertion;
use App\Models\Check;
use App\Models\Group;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;

final class CheckImporter extends Importer
{
    protected static ?string $model = Check::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label(__('checks.name'))
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('group')
                ->label(__('checks.group'))
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->fillRecordUsing(function () {
                    // Group is handled in resolveRecord()
                }),
            ImportColumn::make('type')
                ->label(__('checks.type'))
                ->requiredMapping()
                ->rules(['required', 'string', 'in:http']),
            ImportColumn::make('endpoint')
                ->label(__('checks.endpoint'))
                ->requiredMapping()
                ->rules(['required', 'url', 'max:255']),
            ImportColumn::make('http_method')
                ->label(__('checks.http_method'))
                ->requiredMapping()
                ->rules(['required', 'string', 'in:GET,POST,PUT,PATCH,DELETE,OPTIONS,HEAD']),
            ImportColumn::make('interval')
                ->label(__('checks.interval'))
                ->requiredMapping()
                ->rules(['required', 'integer', 'min:1'])
                ->numeric(),
            ImportColumn::make('request_timeout')
                ->label(__('checks.request_timeout'))
                ->requiredMapping()
                ->rules(['required', 'integer', 'min:1'])
                ->numeric(),
            ImportColumn::make('request_headers')
                ->label(__('checks.request_headers'))
                ->rules(['nullable', 'json']),
            ImportColumn::make('request_body')
                ->label(__('checks.request_body'))
                ->rules(['nullable', 'json']),
            ImportColumn::make('notify_emails')
                ->label(__('checks.notify_emails'))
                ->rules(['nullable', 'string']),
            ImportColumn::make('active')
                ->label(__('checks.active'))
                ->requiredMapping()
                ->rules(['required', 'boolean'])
                ->boolean(),
            ImportColumn::make('assertions_data')
                ->label(__('checks.assertions'))
                ->requiredMapping()
                ->rules(['required', 'json'])
                ->fillRecordUsing(function () {
                    // Assertions are handled in resolveRecord()
                }),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('checks.import_completed', [
            'count' => number_format($import->successful_rows),
            'rows' => str('row')->plural($import->successful_rows),
        ]);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.__('checks.import_failed', [
                'count' => number_format($failedRowsCount),
                'rows' => str('row')->plural($failedRowsCount),
            ]);
        }

        return $body;
    }

    public function resolveRecord(): ?Check
    {
        // Find or create group for the current user
        $group = Group::query()->firstOrCreate(
            [
                'name' => $this->data['group'],
                'user_id' => Auth::id(),
            ],
            [
                'name' => $this->data['group'],
                'user_id' => Auth::id(),
            ]
        );

        // Parse JSON fields if they exist
        $requestHeaders = $this->data['request_headers']
            ? json_decode($this->data['request_headers'], true)
            : [];
        $requestBody = $this->data['request_body']
            ? json_decode($this->data['request_body'], true)
            : null;

        // Create the check
        $check = Check::query()->create([
            'user_id' => Auth::id(),
            'group_id' => $group->id,
            'name' => $this->data['name'],
            'type' => CheckType::from($this->data['type']),
            'endpoint' => $this->data['endpoint'],
            'http_method' => HTTPMethod::from($this->data['http_method']),
            'interval' => (int) $this->data['interval'],
            'request_timeout' => (int) $this->data['request_timeout'],
            'request_headers' => $requestHeaders,
            'request_body' => $requestBody,
            'notify_emails' => $this->data['notify_emails'] ?? '',
            'active' => (bool) $this->data['active'],
        ]);

        // Parse and create assertions
        $assertionsData = json_decode($this->data['assertions_data'], true);

        if (is_array($assertionsData)) {
            foreach ($assertionsData as $assertionData) {
                Assertion::query()->create([
                    'check_id' => $check->id,
                    'type' => AssertionType::from($assertionData['type']),
                    'sign' => AssertionSign::from($assertionData['sign']),
                    'value' => $assertionData['value'],
                ]);
            }
        }

        return $check;
    }
}
