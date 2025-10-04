<?php

declare(strict_types=1);

return [
    // Dashboard filters
    'filters' => [
        'date_range' => 'Date Range',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'ranges' => [
            '24h' => 'Last 24 Hours',
            '7d' => 'Last 7 Days',
            '30d' => 'Last 30 Days',
            '90d' => 'Last 90 Days',
            'custom' => 'Custom Range',
        ],
    ],

    // Widget headings
    'widgets' => [
        'check_history' => 'Check History',
        'stats_overview' => 'Stats Overview',
        'response_time_trend' => 'Response Time Trend',
    ],

    // Stats
    'stats' => [
        'total_checks' => 'Total Checks',
        'checks' => 'Checks',
        'success_rate' => 'Success Rate',
        'avg_response_time' => 'Avg Response Time',
        'active' => 'active',
        'failed' => 'failed',
        'n_a' => 'N/A',
    ],

    // Range labels
    'range_labels' => [
        'custom' => 'in custom range',
        '24h' => 'in last 24 hours',
        '7d' => 'in last 7 days',
        '30d' => 'in last 30 days',
        '90d' => 'in last 90 days',
    ],

    // Chart labels
    'chart' => [
        'successful_checks' => 'Successful Checks',
        'failed_checks' => 'Failed Checks',
        'avg_response_time' => 'Average',
        'max_response_time' => 'Maximum',
        'response_time_ms' => 'Response Time (ms)',
    ],
];
