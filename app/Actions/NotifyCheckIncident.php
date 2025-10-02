<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\NotifyCheckIncident as NotifyCheckIncidentMail;
use App\Models\CheckHistory;
use Illuminate\Support\Facades\Mail;

final class NotifyCheckIncident
{
    public function handle(array $emails, CheckHistory $checkHistory): void
    {
        Mail::to($emails)->send(new NotifyCheckIncidentMail($checkHistory));
    }
}
