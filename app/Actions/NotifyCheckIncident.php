<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CheckHistory;
use App\Notifications\CheckIncidentNotification;
use Illuminate\Support\Facades\Notification;

final class NotifyCheckIncident
{
    public function handle(array $emails, CheckHistory $checkHistory): void
    {
        $check = $checkHistory->check;
        $notification = new CheckIncidentNotification($checkHistory);

        // Send database notification to the check owner
        if ($check->user) {
            $check->user->notify($notification);
        }

        // Send email notifications to configured email addresses
        if (! empty($emails)) {
            Notification::route('mail', $emails)
                ->notify($notification);
        }
    }
}
