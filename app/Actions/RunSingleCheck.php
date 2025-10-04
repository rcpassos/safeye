<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AssertionType;
use App\Enums\CheckHistoryType;
use App\Models\Check;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;

final readonly class RunSingleCheck
{
    public function __construct(
        private SaveCheckHistory $saveCheckHistory,
        private EvaluateAssertion $evaluateAssertion
    ) {}

    public function handle(Check $check): void
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
            if ($statsData instanceof TransferStats) {
                $this->handleRequestStats($statsData, $check);
            }
        } catch (GuzzleException $e) {
            // Handle request failures
            $metadata = [
                'error' => $e->getMessage(),
                'request_headers' => $check->request_headers,
                'request_body' => $check->request_body,
            ];

            $this->saveCheckHistory->handle(
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
            'request_body' => $requestBody !== '' && $requestBody !== '0' ? $requestBody : null,
            'request_headers' => $stats->getRequest()->getHeaders(),
        ];

        // If there are no assertions, create a success record if response is OK
        if ($check->assertions->isEmpty()) {
            $type = ($response && $response->getStatusCode() >= 200 && $response->getStatusCode() < 300)
                ? CheckHistoryType::SUCCESS
                : CheckHistoryType::ERROR;

            $this->saveCheckHistory->handle(
                check: $check,
                metadata: $metadata,
                rootCause: $response instanceof ResponseInterface ? [] : ['error' => 'No response received'],
                type: $type
            );

            return;
        }

        // Process each assertion
        foreach ($check->assertions as $assertion) {
            $actualValue = $assertion->type === AssertionType::RESPONSE_TIME
                ? $stats->getTransferTime()
                : ($response?->getStatusCode() ?? 0);

            $success = $this->evaluateAssertion->handle($assertion, $actualValue);

            $this->saveCheckHistory->handle(
                check: $check,
                metadata: $metadata,
                rootCause: $assertion->attributesToArray(),
                type: $success ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR
            );
        }
    }
}
