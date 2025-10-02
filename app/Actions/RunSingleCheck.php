<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use App\Enums\CheckHistoryType;
use App\Mail\NotifyCheckIncident;
use App\Models\Check;
use App\Models\CheckHistory;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Mail;

final class RunSingleCheck
{
    public function execute(Check $check): void
    {
        $client = new Client([
            'timeout' => $check->request_timeout,
        ]);

        try {
            $statsData = null;

            $requestOptions = [
                'headers' => $check->request_headers,
                'on_stats' => function (TransferStats $stats) use (&$statsData): void {
                    $statsData = $stats;
                },
            ];

            // Handle request body - convert array to JSON string if needed
            if (! empty($check->request_body)) {
                if (is_array($check->request_body)) {
                    $requestOptions['json'] = $check->request_body;
                } else {
                    $requestOptions['body'] = $check->request_body;
                }
            }

            $client->requestAsync($check->http_method->value, $check->endpoint, $requestOptions)->wait();

            // Only handle stats if request was successful
            if ($statsData) {
                $this->handleRequestStats($statsData, $check);
            }
        } catch (GuzzleException $e) {
            // Handle request failures
            $metadata = [
                'error' => $e->getMessage(),
                'request_headers' => $check->request_headers,
                'request_body' => $check->request_body,
            ];

            $this->saveHistory(
                check: $check,
                metadata: $metadata,
                rootCause: ['error' => 'Request failed: '.$e->getMessage()],
                type: CheckHistoryType::ERROR
            );
        }

        $check->last_run_at = Carbon::now();
        $check->save();
    }

    private function handleRequestStats(TransferStats $stats, Check $check): void
    {
        $response = $stats->getResponse();
        $requestBody = $stats->getRequest()->getBody()->getContents();

        $metadata = [
            'transfer_time' => $stats->getTransferTime(),
            'response_headers' => $response?->getHeaders() ?? [],
            'response_code' => $response?->getStatusCode() ?? 0,
            'response_body' => $response?->getBody()?->getContents() ?? '',
            'request_body' => $requestBody ?: null,
            'request_headers' => $stats->getRequest()->getHeaders(),
        ];

        // If there are no assertions, create a success record if response is OK
        if ($check->assertions->isEmpty()) {
            $type = ($response && $response->getStatusCode() >= 200 && $response->getStatusCode() < 300)
                ? CheckHistoryType::SUCCESS
                : CheckHistoryType::ERROR;

            $this->saveHistory(
                check: $check,
                metadata: $metadata,
                rootCause: $response ? [] : ['error' => 'No response received'],
                type: $type
            );

            return;
        }

        // Process each assertion
        foreach ($check->assertions as $assertion) {
            $actualValue = $assertion->type === AssertionType::RESPONSE_TIME
                ? $stats->getTransferTime()
                : ($response?->getStatusCode() ?? 0);

            $expectedValue = (float) $assertion->value;
            $success = false;

            switch ($assertion->sign) {
                case AssertionSign::LESS_THAN:
                    $success = $actualValue < $expectedValue;
                    break;
                case AssertionSign::LESS_THAN_OR_EQUAL:
                    $success = $actualValue <= $expectedValue;
                    break;
                case AssertionSign::EQUAL:
                    $success = abs($actualValue - $expectedValue) < 0.001; // Float comparison
                    break;
                case AssertionSign::NOT_EQUAL:
                    $success = abs($actualValue - $expectedValue) >= 0.001; // Float comparison
                    break;
                case AssertionSign::GREATER_THAN:
                    $success = $actualValue > $expectedValue;
                    break;
                case AssertionSign::GREATER_THAN_OR_EQUAL:
                    $success = $actualValue >= $expectedValue;
                    break;
                default:
                    $success = false;
                    break;
            }

            $this->saveHistory(
                check: $check,
                metadata: $metadata,
                rootCause: $assertion->attributesToArray(),
                type: $success ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
            );
        }
    }

    private function saveHistory(Check $check, array $metadata, array $rootCause, CheckHistoryType $type): void
    {
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
