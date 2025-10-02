<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\CheckResource\Pages;

use App\Actions\PrepareCheckFormData;
use App\Filament\App\Resources\CheckResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

final class CreateCheck extends CreateRecord
{
    protected static string $resource = CheckResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return app(PrepareCheckFormData::class)->handle($data, Auth::id());
    }
}
