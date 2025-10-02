<?php

declare(strict_types=1);

namespace App\Actions;

final class PrepareCheckFormData
{
    public function handle(array $data, int $userId): array
    {
        $data['user_id'] = $userId;
        $data['notify_emails'] = isset($data['notify_emails']) && $data['notify_emails']
            ? str_replace(' ', ';', $data['notify_emails'])
            : null;

        return $data;
    }
}
