<?php

declare(strict_types=1);

return [
    'checks' => 'Checks',
    'checks_singular' => 'Check',
    'create_check' => 'Create Check',
    'edit_check' => 'Edit Check',
    'view_check' => 'View Check',
    'delete_check' => 'Delete Check',

    // Form sections
    'basic_information' => 'Basic Information',
    'request_details' => 'Request Details',
    'check_configuration' => 'Configuration',
    'http_settings' => 'HTTP Settings',
    'ping_settings' => 'Ping Settings',
    'assertions' => 'Assertions',
    'alert_settings' => 'Notifications',

    // Form fields
    'id' => 'ID',
    'name' => 'Name',
    'group' => 'Group',
    'type' => 'Type',
    'endpoint' => 'Endpoint',
    'http_method' => 'HTTP Method',
    'interval' => 'Interval',
    'request_timeout' => 'Request Timeout',
    'request_headers' => 'Request Headers',
    'request_body' => 'Request Body',
    'ping_count' => 'Packet Count',
    'ping_timeout' => 'Timeout',
    'notify_emails' => 'Notification Emails',
    'slack_webhook_url' => 'Slack Webhook URL',
    'assertions_section' => 'Assertions',
    'active' => 'Active',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    // Suffixes
    'seconds' => 'seconds',

    // Placeholders
    'placeholder_assertion_type' => 'Select assertion type',
    'placeholder_assertion_sign' => 'Select comparison',
    'placeholder_assertion_value' => '200',

    // Buttons
    'add_assertion' => 'Add Assertion',
    'run_check_now' => 'Run Check Now',

    // Helper texts and descriptions
    'helper_interval' => 'How often to run this check (minimum 10 seconds)',
    'helper_active' => 'Enable or disable this check',
    'helper_endpoint_http' => 'The full URL to check (e.g., https://example.com/api/health)',
    'helper_endpoint_ping' => 'The host or URL to ping (protocol will be stripped)',
    'helper_notify_emails' => 'Enter one email address per line to receive notifications when this check fails',
    'helper_slack_webhook_url' => 'Enter your Slack Incoming Webhook URL from https://api.slack.com/messaging/webhooks',
    'helper_ping_count' => 'Number of packets to send (1-10)',
    'helper_ping_timeout' => 'Maximum time to wait for response',
    'helper_request_headers' => 'Add custom HTTP headers to include with the request',
    'helper_request_body' => 'JSON body to send with the request (for POST, PUT, PATCH)',
    'description_http_settings' => 'Configure HTTP request parameters including method, timeout, headers, and body',
    'description_ping_settings' => 'Configure ping-specific parameters including packet count and timeout',
    'description_assertions' => 'Define conditions that must be met for the check to pass. At least one assertion is required.',
    'description_assertions_ping' => 'Assertions are optional for PING checks. You can add assertions to validate packet loss or response times.',
    'description_alert_settings' => 'Configure where to send notifications when this check fails',
    'header_name' => 'Header Name',
    'header_value' => 'Header Value',

    // Widget labels
    'last_check' => 'LAST CHECK',
    'uptime' => 'UPTIME',
    'performance' => 'PERFORMANCE',
    'avg_ping_time' => 'AVG PING TIME',
    'checks_alerts' => 'CHECKS (ALERTS)',

    // View page
    'latest_activity' => 'Latest Activity',
    'details' => 'Details',
    'no_checks_found' => 'No checks found',
    'no_issues_found' => 'No issues found',

    // Status
    'status' => 'Status',

    // Formatting
    'every_seconds' => 'Every :seconds seconds',
    'value_seconds' => ':value seconds',
    'value_packets' => ':value packets',

    // Actions
    'check_executed' => 'Check executed successfully!',
    'check_execution_failed' => 'Failed to execute check: :error',

    // Import/Export
    'export_completed' => 'Your check export has completed and :count :rows exported.',
    'export_failed' => ':count :rows failed to export.',
    'import_completed' => 'Your check import has completed and :count :rows imported.',
    'import_failed' => ':count :rows failed to import.',
];
