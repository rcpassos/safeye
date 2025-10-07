<?php

declare(strict_types=1);

use App\Actions\PrepareCheckFormData;

test('adds user id to data', function () {
    $action = app(PrepareCheckFormData::class);

    $data = [
        'name' => 'Test Check',
        'endpoint' => 'https://example.com',
    ];

    $result = $action->handle($data, 123);

    expect($result['user_id'])->toBe(123);
    expect($result['name'])->toBe('Test Check');
    expect($result['endpoint'])->toBe('https://example.com');
});

test('replaces spaces with semicolons in notify emails', function () {
    $action = app(PrepareCheckFormData::class);

    $data = [
        'notify_emails' => 'email1@example.com email2@example.com email3@example.com',
    ];

    $result = $action->handle($data, 456);

    expect($result['notify_emails'])->toBe('email1@example.com;email2@example.com;email3@example.com');
});

test('sets notify emails to null when empty', function () {
    $action = app(PrepareCheckFormData::class);

    $data = ['notify_emails' => ''];
    $result = $action->handle($data, 789);

    expect($result['notify_emails'])->toBeNull();
});

test('sets notify emails to null when null', function () {
    $action = app(PrepareCheckFormData::class);

    $data = ['notify_emails' => null];
    $result = $action->handle($data, 101);

    expect($result['notify_emails'])->toBeNull();
});

test('preserves notify emails without spaces', function () {
    $action = app(PrepareCheckFormData::class);

    $data = ['notify_emails' => 'admin@example.com'];
    $result = $action->handle($data, 202);

    expect($result['notify_emails'])->toBe('admin@example.com');
});
