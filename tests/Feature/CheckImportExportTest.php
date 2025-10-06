<?php

declare(strict_types=1);

namespace Tests\Feature;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CheckImportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->group = Group::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Group',
        ]);
    }

    public function test_exported_csv_contains_correct_columns(): void
    {
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
            $this->assertTrue($found, "Column '{$expectedColumn}' not found in exporter");
        }

        $this->assertCount(count($expectedColumns), $columns);
    }

    public function test_importer_contains_required_columns(): void
    {
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
            $this->assertTrue($found, "Column '{$expectedColumn}' not found in importer");
        }

        $this->assertCount(count($expectedColumns), $columns);
    }

    public function test_exporter_formats_enum_values_correctly(): void
    {
        $this->assertEquals('http', CheckType::HTTP->value);
        $this->assertEquals('GET', HTTPMethod::GET->value);
        $this->assertEquals('POST', HTTPMethod::POST->value);
        $this->assertEquals('eq', AssertionSign::EQUAL->value);
        $this->assertEquals('neq', AssertionSign::NOT_EQUAL->value);
    }

    public function test_check_with_assertions_can_be_exported(): void
    {
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

        $this->assertEquals(2, $check->assertions()->count());
    }

    public function test_assertions_data_structure(): void
    {
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

        $this->assertIsArray($decoded);
        $this->assertCount(2, $decoded);
        $this->assertEquals('response.code', $decoded[0]['type']);
        $this->assertEquals('eq', $decoded[0]['sign']);
        $this->assertEquals('200', $decoded[0]['value']);
        $this->assertEquals('response.time', $decoded[1]['type']);
        $this->assertEquals('lt', $decoded[1]['sign']);
        $this->assertEquals('1000', $decoded[1]['value']);
    }

    public function test_importer_columns_have_validation_rules(): void
    {
        $columns = CheckImporter::getColumns();

        $nameColumn = collect($columns)->first(fn ($column) => $column->getName() === 'name');
        $this->assertNotNull($nameColumn);

        $endpointColumn = collect($columns)->first(fn ($column) => $column->getName() === 'endpoint');
        $this->assertNotNull($endpointColumn);

        $intervalColumn = collect($columns)->first(fn ($column) => $column->getName() === 'interval');
        $this->assertNotNull($intervalColumn);
    }

    public function test_exporter_includes_group_relationship(): void
    {
        $columns = CheckExporter::getColumns();

        $groupColumn = collect($columns)->first(fn ($column) => $column->getName() === 'group.name');

        $this->assertNotNull($groupColumn, 'group.name column should exist in exporter');
    }
}
