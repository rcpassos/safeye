<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\PrepareCheckFormData;
use Tests\TestCase;

final class PrepareCheckFormDataActionTest extends TestCase
{
    public function test_adds_user_id_to_data(): void
    {
        $action = app(PrepareCheckFormData::class);

        $data = [
            'name' => 'Test Check',
            'endpoint' => 'https://example.com',
        ];

        $result = $action->handle($data, 123);

        $this->assertEquals(123, $result['user_id']);
        $this->assertEquals('Test Check', $result['name']);
        $this->assertEquals('https://example.com', $result['endpoint']);
    }

    public function test_replaces_spaces_with_semicolons_in_notify_emails(): void
    {
        $action = app(PrepareCheckFormData::class);

        $data = [
            'notify_emails' => 'email1@example.com email2@example.com email3@example.com',
        ];

        $result = $action->handle($data, 456);

        $this->assertEquals('email1@example.com;email2@example.com;email3@example.com', $result['notify_emails']);
    }

    public function test_sets_notify_emails_to_null_when_empty(): void
    {
        $action = app(PrepareCheckFormData::class);

        $data = ['notify_emails' => ''];
        $result = $action->handle($data, 789);

        $this->assertNull($result['notify_emails']);
    }

    public function test_sets_notify_emails_to_null_when_null(): void
    {
        $action = app(PrepareCheckFormData::class);

        $data = ['notify_emails' => null];
        $result = $action->handle($data, 101);

        $this->assertNull($result['notify_emails']);
    }

    public function test_preserves_notify_emails_without_spaces(): void
    {
        $action = app(PrepareCheckFormData::class);

        $data = ['notify_emails' => 'admin@example.com'];
        $result = $action->handle($data, 202);

        $this->assertEquals('admin@example.com', $result['notify_emails']);
    }
}
