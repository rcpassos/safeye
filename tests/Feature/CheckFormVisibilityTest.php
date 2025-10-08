<?php

declare(strict_types=1);

use App\Filament\App\Resources\CheckResource;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('form fields show and hide correctly when switching check types', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Filament::setCurrentPanel(Filament::getPanel('app'));

    $component = Livewire::test(CheckResource\Pages\CreateCheck::class)
        ->assertSuccessful();

    // Test HTTP type (default) - should show HTTP fields
    $component
        ->assertFormFieldExists('type')
        ->assertFormFieldExists('config.method')
        ->assertFormFieldExists('config.timeout')
        ->assertFormFieldExists('config.headers')
        ->assertFormFieldExists('config.body')
        ->assertFormFieldDoesNotExist('config.count');

    // Switch to PING type - should hide HTTP fields and show PING fields
    $component
        ->fillForm(['type' => 'ping'])
        ->assertFormFieldExists('type')
        ->assertFormFieldDoesNotExist('config.method')
        ->assertFormFieldDoesNotExist('config.headers')
        ->assertFormFieldDoesNotExist('config.body')
        ->assertFormFieldExists('config.count')
        ->assertFormFieldExists('config.timeout');

    // Switch back to HTTP type - should show HTTP fields and hide PING fields
    $component
        ->fillForm(['type' => 'http'])
        ->assertFormFieldExists('type')
        ->assertFormFieldExists('config.method')
        ->assertFormFieldExists('config.timeout')
        ->assertFormFieldExists('config.headers')
        ->assertFormFieldExists('config.body')
        ->assertFormFieldDoesNotExist('config.count');
});
