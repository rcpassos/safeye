<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CheckType;
use App\Models\Check;

final readonly class RunSingleCheck
{
    public function __construct(
        private RunHttpCheck $runHttpCheck,
        private RunPingCheck $runPingCheck
    ) {}

    public function handle(Check $check): void
    {
        match ($check->type) {
            CheckType::HTTP => $this->runHttpCheck->handle($check),
            CheckType::PING => $this->runPingCheck->handle($check),
        };
    }
}
