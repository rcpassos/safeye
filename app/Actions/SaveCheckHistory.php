<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CheckHistoryType;
use App\Models\Check;
use App\Models\CheckHistory;

final readonly class SaveCheckHistory
{
    public function __construct(
        private NotifyCheckIncident $notifyCheckIncident
    ) {}

    public function handle(
        Check $check,
        array $metadata,
        array $rootCause,
        CheckHistoryType $type
    ): CheckHistory {
        $history = new CheckHistory();
        $history->check_id = $check->id;
        $history->metadata = $metadata;
        $history->root_cause = $rootCause;
        $history->type = $type;
        $history->notified_emails = $check->notify_emails;
        $history->save();

        // Send notifications when there's an error
        if ($type === CheckHistoryType::ERROR) {
            $emails = [];

            // Parse email addresses if configured
            if (! empty($check->notify_emails)) {
                $emails = preg_split("/\r\n|\r|\n/", (string) $check->notify_emails);
            }

            // Always send notification (database notification to user + optional emails)
            $this->notifyCheckIncident->handle($emails, $history);
        }

        return $history;
    }
}
