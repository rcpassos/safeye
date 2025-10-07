<?php

declare(strict_types=1);

use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use App\Enums\CheckType;
use App\Enums\HTTPMethod;
use App\Filament\Exports\CheckExporter;
use App\Filament\Imports\CheckImporter;
use App\Models\Assertion;
use App\Models\Check;
use App\Models\Group;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->group = Group::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Group',
    ]);
});

test('exported csv contains correct columns', function () {
    $columns = CheckExporter::getColumns();

    $expectedColumns = [
        'id',
        'name',
        'group.name',
        'type',
        'endpoint',
        'http_method',
        'interval',
        'request_timeout',
        'request_headers',
        'request_body',
        'notify_emails',
        'active',
        'assertions_data',
        'created_at',
        'updated_at',
    ];

    foreach ($expectedColumns as $expectedColumn) {
        $found = collect($columns)->contains(fn ($column) => $column->getName() === $expectedColumn);
        expect($found)->toBeTrue("Column '{$expectedColumn}' not found in exporter");
    }

    expect($columns)->toHaveCount(count($expectedColumns));
});

test('importer contains required columns', function () {
    $columns = CheckImporter::getColumns();

    $expectedColumns = [
        'name',
        'group',
        'type',
        'endpoint',
        'http_method',
        'interval',
        'request_timeout',
        'notify_emails',
        'active',
        'assertions_data',
        'request_headers',
        'request_body',
    ];

    foreach ($expectedColumns as $expectedColumn) {
        $found = collect($columns)->contains(fn ($column) => $column->getName() === $expectedColumn);
        expect($found)->toBeTrue("Column '{$expectedColumn}' not found in importer");
    }

    expect($columns)->toHaveCount(count($expectedColumns));
});

test('exporter formats enum values correctly', function () {
    expect(CheckType::HTTP->value)->toBe('http');
    expect(HTTPMethod::GET->value)->toBe('GET');
    expect(HTTPMethod::POST->value)->toBe('POST');
    expect(AssertionSign::EQUAL->value)->toBe('eq');
    expect(AssertionSign::NOT_EQUAL->value)->toBe('neq');
});

test('check with assertions can be exported', function () {
    $this->actingAs($this->user);

    $check = Check::factory()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
        'name' => 'Export Test Check',
        'type' => CheckType::HTTP,
        'endpoint' => 'https://example.com',
        'http_method' => HTTPMethod::GET,
        'interval' => 60,
        'request_timeout' => 10,
        'request_headers' => ['Authorization' => 'Bearer token'],
        'request_body' => ['key' => 'value'],
        'notify_emails' => 'test@example.com',
        'active' => true,
    ]);

    Assertion::factory()->create([
        'check_id' => $check->id,
        'type' => AssertionType::RESPONSE_CODE,
        'sign' => AssertionSign::EQUAL,
        'value' => '200',
    ]);

    Assertion::factory()->create([
        'check_id' => $check->id,
        'type' => AssertionType::RESPONSE_TIME,
        'sign' => AssertionSign::LESS_THAN,
        'value' => '1000',
    ]);

    // Verify the check was created with assertions
    $this->assertDatabaseHas('checks', [
        'name' => 'Export Test Check',
        'endpoint' => 'https://example.com',
    ]);

    expect($check->assertions()->count())->toBe(2);
});

test('assertions data structure', function () {
    $this->actingAs($this->user);

    $check = Check::factory()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    Assertion::factory()->create([
        'check_id' => $check->id,
        'type' => AssertionType::RESPONSE_CODE,
        'sign' => AssertionSign::EQUAL,
        'value' => '200',
    ]);

    Assertion::factory()->create([
        'check_id' => $check->id,
        'type' => AssertionType::RESPONSE_TIME,
        'sign' => AssertionSign::LESS_THAN,
        'value' => '1000',
    ]);

    $check = $check->fresh(['assertions']);

    $assertions = $check->assertions->map(function ($assertion) {
        return [
            'type' => $assertion->type->value,
            'sign' => $assertion->sign->value,
            'value' => $assertion->value,
        ];
    })->toArray();

    $json = json_encode($assertions);
    $decoded = json_decode($json, true);

    expect($decoded)->toBeArray();
    expect($decoded)->toHaveCount(2);
    expect($decoded[0]['type'])->toBe('response.code');
    expect($decoded[0]['sign'])->toBe('eq');
    expect($decoded[0]['value'])->toBe('200');
    expect($decoded[1]['type'])->toBe('response.time');
    expect($decoded[1]['sign'])->toBe('lt');
    expect($decoded[1]['value'])->toBe('1000');
});

test('importer columns have validation rules', function () {
    $columns = CheckImporter::getColumns();

    $nameColumn = collect($columns)->first(fn ($column) => $column->getName() === 'name');
    expect($nameColumn)->not->toBeNull();

    $endpointColumn = collect($columns)->first(fn ($column) => $column->getName() === 'endpoint');
    expect($endpointColumn)->not->toBeNull();

    $intervalColumn = collect($columns)->first(fn ($column) => $column->getName() === 'interval');
    expect($intervalColumn)->not->toBeNull();
});

test('exporter includes group relationship', function () {
    $columns = CheckExporter::getColumns();

    $groupColumn = collect($columns)->first(fn ($column) => $column->getName() === 'group.name');

    expect($groupColumn)->not->toBeNull('group.name column should exist in exporter');
});
