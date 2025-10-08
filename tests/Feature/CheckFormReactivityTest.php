<?php

declare(strict_types=1);

use App\Enums\CheckType;
use App\Filament\App\Resources\CheckResource;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('check form shows correct fields based on type selection', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Filament::setCurrentPanel(Filament::getPanel('app'));

    // Test create form can be loaded with HTTP defaults
    $component = Livewire::test(CheckResource\Pages\CreateCheck::class)
        ->assertSuccessful()
        ->assertFormFieldExists('type')
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('endpoint')
        ->assertFormFieldExists('config.method')
        ->assertFormFieldExists('config.timeout');

    // Switch to PING type and verify form fields change
    $component
        ->fillForm([
            'type' => CheckType::PING->value,
        ])
        ->assertFormFieldExists('config.count')
        ->assertFormFieldExists('config.timeout');
});
