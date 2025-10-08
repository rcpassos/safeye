<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CheckHistoryType;
use App\Models\Check;
use Exception;
use RuntimeException;
use Symfony\Component\Process\Process;

final readonly class RunPingCheck
{
    public function __construct(
        private SaveCheckHistory $saveCheckHistory
    ) {}

    public function handle(Check $check): void
    {
        $config = $check->config;
        $host = $this->extractHost($check->endpoint);
        $count = $config['count'] ?? 4;
        $timeout = $config['timeout'] ?? 10;

        try {
            $result = $this->executePing($host, $count, $timeout);

            $metadata = [
                'host' => $host,
                'packets_transmitted' => $result['packets_transmitted'],
                'packets_received' => $result['packets_received'],
                'packet_loss' => $result['packet_loss'],
                'min_time' => $result['min_time'] ?? null,
                'avg_time' => $result['avg_time'] ?? null,
                'max_time' => $result['max_time'] ?? null,
                'transfer_time' => $result['avg_time'] ?? null,
                'output' => $result['output'],
            ];

            $type = $result['success'] ? CheckHistoryType::SUCCESS : CheckHistoryType::ERROR;

            $rootCause = [
                'type' => 'Packet Loss',
                'sign' => '',
                'value' => $result['packet_loss'].'%',
            ];

            $this->saveCheckHistory->handle(
                check: $check,
                metadata: $metadata,
                rootCause: $rootCause,
                type: $type
            );
        } catch (Exception $e) {
            $metadata = [
                'error' => $e->getMessage(),
                'host' => $host,
            ];

            $this->saveCheckHistory->handle(
                check: $check,
                metadata: $metadata,
                rootCause: [
                    'type' => 'Ping Error',
                    'sign' => '',
                    'value' => $e->getMessage(),
                ],
                type: CheckHistoryType::ERROR
            );
        }

        $check->last_run_at = now();
        $check->save();
    }

    private function extractHost(string $target): string
    {
        // Remove protocol if present
        $host = preg_replace('#^https?://#i', '', $target);

        // Remove path if present
        $host = explode('/', (string) $host)[0];

        return $host;
    }

    private function findPingCommand(): string
    {
        // Windows typically has ping in System32
        if (mb_stripos(PHP_OS, 'WIN') === 0) {
            return 'ping';
        }

        // Common locations for ping on Unix-like systems
        $possiblePaths = [
            '/sbin/ping',           // Most common on macOS and Linux
            '/bin/ping',            // Alternative location
            '/usr/sbin/ping',       // Some Linux distributions
            '/usr/bin/ping',        // Some systems
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        // If not found in common locations, try to use 'which' command
        $process = new Process(['which', 'ping']);
        $process->run();

        if ($process->isSuccessful()) {
            return trim($process->getOutput());
        }

        // Last resort: try using the command directly and hope it's in PATH
        return 'ping';
    }

    private function executePing(string $host, int $count, int $timeout): array
    {
        // Detect OS to use appropriate ping command
        $isWindows = mb_stripos(PHP_OS, 'WIN') === 0;
        $isMacOS = mb_stripos(PHP_OS, 'Darwin') === 0;

        // Get the full path to ping command
        $pingPath = $this->findPingCommand();

        if ($isWindows) {
            $command = [$pingPath, '-n', (string) $count, '-w', (string) ($timeout * 1000), $host];
        } elseif ($isMacOS) {
            // macOS requires -t for timeout (in seconds)
            $command = [$pingPath, '-c', (string) $count, '-t', (string) $timeout, $host];
        } else {
            // Linux uses -W for timeout
            $command = [$pingPath, '-c', (string) $count, '-W', (string) $timeout, $host];
        }

        $process = new Process($command);
        $process->setTimeout($timeout + 5);

        $exitCode = $process->run();

        // Get both stdout and stderr
        $output = $process->getOutput();
        $errorOutput = $process->getErrorOutput();

        // If there's an error output, it might contain useful information
        if ($errorOutput !== '' && $errorOutput !== '0') {
            // Check for common permission errors
            throw_if(str_contains($errorOutput, 'Operation not permitted') || str_contains($errorOutput, 'Permission denied'), new RuntimeException('Ping requires additional permissions. If running in Docker, ensure the container has CAP_NET_RAW capability. Error: '.$errorOutput));
            // If there's stderr but ping still succeeded (exit code 0), it might just be warnings
            throw_if($exitCode !== 0, new RuntimeException('Ping failed: '.$errorOutput));
        }

        // If both are empty, something went wrong
        if ($output === '' || $output === '0') {
            $errorMsg = 'Ping command produced no output.';
            if ($errorOutput !== '' && $errorOutput !== '0') {
                $errorMsg .= ' Error: '.$errorOutput;
            }
            $errorMsg .= ' Command: '.implode(' ', $command);
            $errorMsg .= ' Exit code: '.$exitCode;

            throw new RuntimeException($errorMsg);
        }

        $result = $this->parsePingOutput($output, $isWindows);
        $result['command'] = implode(' ', $command);
        $result['exit_code'] = $exitCode;

        return $result;
    }

    private function parsePingOutput(string $output, bool $isWindows): array
    {
        $result = [
            'success' => false,
            'packets_transmitted' => 0,
            'packets_received' => 0,
            'packet_loss' => 100.0,
            'output' => $output,
        ];

        if ($isWindows) {
            // Parse Windows ping output
            if (preg_match('/Sent = (\d+), Received = (\d+), Lost = \d+ \((\d+)% loss\)/i', $output, $matches)) {
                $result['packets_transmitted'] = (int) $matches[1];
                $result['packets_received'] = (int) $matches[2];
                $result['packet_loss'] = (float) $matches[3];
                $result['success'] = $result['packets_received'] > 0;
            }

            if (preg_match('/Minimum = (\d+)ms, Maximum = (\d+)ms, Average = (\d+)ms/i', $output, $matches)) {
                $result['min_time'] = (float) $matches[1];
                $result['max_time'] = (float) $matches[2];
                $result['avg_time'] = (float) $matches[3];
            }
        } else {
            // Parse Unix/Linux/macOS ping output
            // Handle both "X packets transmitted, Y received" and "X packets transmitted, Y packets received"
            if (preg_match('/(\d+) packets transmitted,\s*(\d+)(?: packets)? received,\s*([\d.]+)% packet loss/i', $output, $matches)) {
                $result['packets_transmitted'] = (int) $matches[1];
                $result['packets_received'] = (int) $matches[2];
                $result['packet_loss'] = (float) $matches[3];
                $result['success'] = $result['packets_received'] > 0;
            }

            // Handle macOS format: round-trip min/avg/max/stddev = ...
            if (preg_match('/round-trip\s+min\/avg\/max(?:\/stddev)?\s*=\s*([\d.]+)\/([\d.]+)\/([\d.]+)/i', $output, $matches)) {
                $result['min_time'] = (float) $matches[1];
                $result['avg_time'] = (float) $matches[2];
                $result['max_time'] = (float) $matches[3];
            }
            // Handle Linux format: min/avg/max/mdev = ...
            elseif (preg_match('/min\/avg\/max\/mdev\s*=\s*([\d.]+)\/([\d.]+)\/([\d.]+)/i', $output, $matches)) {
                $result['min_time'] = (float) $matches[1];
                $result['avg_time'] = (float) $matches[2];
                $result['max_time'] = (float) $matches[3];
            }
        }

        return $result;
    }
}
