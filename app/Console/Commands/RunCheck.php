<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use App\Enums\CheckHistoryType;
use App\Mail\NotifyCheckIncident;
use App\Models\Check;
use App\Models\CheckHistory;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

final class RunCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run check for all the checks that are active';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $checks = Check::where('active', true)->get();

        foreach ($checks as $check) {
            $lastChecked = $check->last_run_at;
            $now = Carbon::now();

            if (is_null($lastChecked) || $lastChecked->diffInMinutes($now) >= $check->interval) {
                $this->doTheCheck($check);
            }
        }
    }

    private function doTheCheck(Check $check): void
    {
        $client = new Client([
            'timeout' => $check->request_timeout,
        ]);

        $client->requestAsync($check->http_method->value, $check->endpoint, [
            'headers' => $check->request_headers,
            'body' => $check->request_body,
            'on_stats' => function (TransferStats $stats) use ($check): void {
                $this->handleRequestStats($stats, $check);
            },
        ])->wait(); // TODO: maybe we don't need to wait
    }

    private function handleRequestStats(TransferStats $stats, Check $check): void
    {
        $metadata = [
            'transfer_time' => $stats->getTransferTime(),
            'response_headers' => $stats->getResponse()->getHeaders(),
            'response_code' => $stats->getResponse()->getStatusCode(),
            'response_body' => $stats->getResponse()->getBody()->getContents(),
            'request_body' => $stats->getRequest()->getBody()->getContents(),
            'request_headers' => $stats->getRequest()->getHeaders(),
        ];

        // TODO: needs to be refactored
        foreach ($check->assertions as $assertion) {
            switch ($assertion->sign) {
                case AssertionSign::LESS_THAN:
                    if ($assertion->type === AssertionType::RESPONSE_TIME) {
                        $this->saveHistory(
                            check: $check,
                            metadata: $metadata,
                            rootCause: $assertion->attributesToArray(),
                            type: ($stats->getTransferTime() < $assertion->value) ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
                        );
                    } elseif ($assertion->type === AssertionType::RESPONSE_CODE && $stats->getResponse()->getStatusCode() >= $assertion->value) {
                        $this->saveHistory(
                            check: $check,
                            metadata: $metadata,
                            rootCause: $assertion->attributesToArray(),
                            type: ($stats->getResponse()->getStatusCode() < $assertion->value) ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
                        );
                    }
                    break;

                case AssertionSign::LESS_THAN_OR_EQUAL_TO:
                    if ($assertion->type === AssertionType::RESPONSE_TIME) {
                        $this->saveHistory(
                            check: $check,
                            metadata: $metadata,
                            rootCause: $assertion->attributesToArray(),
                            type: ($stats->getTransferTime() <= $assertion->value) ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
                        );
                    } elseif ($assertion->type === AssertionType::RESPONSE_CODE && $stats->getResponse()->getStatusCode() > $assertion->value) {
                        $this->saveHistory(
                            check: $check,
                            metadata: $metadata,
                            rootCause: $assertion->attributesToArray(),
                            type: ($stats->getResponse()->getStatusCode() <= $assertion->value) ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
                        );
                    }
                    break;

                case AssertionSign::EQUAL:
                    if ($assertion->type === AssertionType::RESPONSE_TIME) {
                        $this->saveHistory(
                            check: $check,
                            metadata: $metadata,
                            rootCause: $assertion->attributesToArray(),
                            type: ($stats->getTransferTime() !== null && (string) $stats->getTransferTime() === $assertion->value) ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
                        );
                    } elseif ($assertion->type === AssertionType::RESPONSE_CODE && (string) $stats->getResponse()->getStatusCode() !== $assertion->value) {
                        $this->saveHistory(
                            check: $check,
                            metadata: $metadata,
                            rootCause: $assertion->attributesToArray(),
                            type: ($stats->getResponse()?->getStatusCode() !== null && (string) $stats->getResponse()->getStatusCode() === $assertion->value) ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
                        );
                    }
                    break;

                case AssertionSign::NOT_EQUAL:
                    if ($assertion->type === AssertionType::RESPONSE_TIME) {
                        $this->saveHistory(
                            check: $check,
                            metadata: $metadata,
                            rootCause: $assertion->attributesToArray(),
                            type: ($stats->getTransferTime() !== null && (string) $stats->getTransferTime() === $assertion->value) ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
                        );
                    } elseif ($assertion->type === AssertionType::RESPONSE_CODE && (string) $stats->getResponse()->getStatusCode() === $assertion->value) {
                        $this->saveHistory(
                            check: $check,
                            metadata: $metadata,
                            rootCause: $assertion->attributesToArray(),
                            type: ($stats->getResponse()?->getStatusCode() !== null && (string) $stats->getResponse()->getStatusCode() === $assertion->value) ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
                        );
                    }
                    break;

                case AssertionSign::GREATER_THAN:
                    if ($assertion->type === AssertionType::RESPONSE_TIME) {
                        $this->saveHistory(
                            check: $check,
                            metadata: $metadata,
                            rootCause: $assertion->attributesToArray(),
                            type: ($stats->getTransferTime() !== null && (string) $stats->getTransferTime() === $assertion->value) ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
                        );
                    } elseif ($assertion->type === AssertionType::RESPONSE_CODE && $stats->getResponse()->getStatusCode() <= $assertion->value) {
                        $this->saveHistory(
                            check: $check,
                            metadata: $metadata,
                            rootCause: $assertion->attributesToArray(),
                            type: ($stats->getResponse()->getStatusCode() > $assertion->value) ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
                        );
                    }
                    break;

                case AssertionSign::GREATER_THAN_OR_EQUAL_TO:
                    if ($assertion->type === AssertionType::RESPONSE_TIME) {
                        $this->saveHistory(
                            check: $check,
                            metadata: $metadata,
                            rootCause: $assertion->attributesToArray(),
                            type: ($stats->getTransferTime() !== null && (string) $stats->getTransferTime() === $assertion->value) ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
                        );
                    } elseif ($assertion->type === AssertionType::RESPONSE_CODE && $stats->getResponse()->getStatusCode() < $assertion->value) {
                        $this->saveHistory(
                            check: $check,
                            metadata: $metadata,
                            rootCause: $assertion->attributesToArray(),
                            type: ($stats->getResponse()->getStatusCode() >= $assertion->value) ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
                        );
                    }
                    break;
                default:
                    $this->saveHistory(
                        check: $check,
                        metadata: $metadata,
                        rootCause: $assertion->attributesToArray(),
                        type: CheckHistoryType::ERROR
                    );
                    break;
            }
        }

        $check->last_run_at = Carbon::now();
        $check->save();
    }

    private function saveHistory(Check $check, array $metadata, array $rootCause, CheckHistoryType $type): void
    {
        // TODO: The history needs to be cleared after some time, maybe a cronjob to clear the history time to time based on the subscription plan (retention time)
        $history = new CheckHistory;
        $history->check_id = $check->id;
        $history->metadata = $metadata;
        $history->root_cause = $rootCause;
        $history->type = $type;
        $history->notified_emails = $check->notify_emails;
        $history->save();

        if (
            $type === CheckHistoryType::ERROR &&
            ! empty($check->notify_emails)
        ) {
            $this->notifyEmails(preg_split("/\r\n|\r|\n/", $check->notify_emails), $history);
        }
    }

    private function notifyEmails(array $emails, CheckHistory $checkHistory): void
    {
        Mail::to($emails)->send(new NotifyCheckIncident($checkHistory));
    }
}
