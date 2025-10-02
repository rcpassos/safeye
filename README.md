# SafeEye - Monitoring System Documentation

**SafeEye** is a Laravel 11 application designed as a monitoring system specifically for freelancer projects. It provides comprehensive HTTP endpoint monitoring with configurable assertions, email notifications, and historical tracking.

## Table of Contents

-   [Overview](#overview)
-   [Key Features](#key-features)
-   [Technology Stack](#technology-stack)
-   [Architecture](#architecture)
-   [Database Schema](#database-schema)
-   [Models](#models)
-   [Installation](#installation)
-   [Usage](#usage)
-   [Development Status](#development-status)

## Overview

SafeEye allows freelancers and developers to monitor their web applications, APIs, and services with automated checks and instant notifications when issues are detected. The system supports multi-user environments with role-based access control and project organization through groups.

## Key Features

### 🔍 HTTP Monitoring

-   **Endpoint Monitoring**: Monitor any HTTP endpoint with customizable intervals
-   **HTTP Methods**: Support for GET, POST, PUT, DELETE, PATCH, HEAD, OPTIONS
-   **Request Configuration**:
    -   Custom headers and request body
    -   Configurable timeouts
    -   Flexible intervals for check frequency
-   **Active/Inactive States**: Enable or disable checks as needed

### 📊 Assertions & Validation

The system validates responses through configurable assertions:

**Currently Implemented:**

-   ✅ **Response Time**: Validate endpoint response time
-   ✅ **HTTP Status Code**: Ensure correct status codes are returned

**Planned Features:**

-   🚧 **Response Body**: Content validation in response body
-   🚧 **JSON Response**: Validate JSON structure and values
-   🚧 **Response Headers**: Validate specific headers
-   🚧 **SSL Certificate**: Monitor SSL certificate expiration

### 📧 Email Notifications

-   **Incident Alerts**: Automatic email notifications when checks fail
-   **Configurable Recipients**: Set different notification emails per check
-   **Historical Tracking**: Track which emails were notified for each incident

### 👥 Multi-User Support

-   **User Authentication**: Secure login system
-   **Admin Role**: Admin users can manage the entire system
-   **User Isolation**: Users can only access their own checks and groups
-   **Group Organization**: Organize checks by project or category

### 📈 Historical Data & Analytics

-   **Check History**: Complete log of all check executions
-   **Error Tracking**: Detailed error information and root cause analysis
-   **Recent Issues**: Quick view of issues in the last 24 hours
-   **Metadata Storage**: Store additional context for each check execution

## Technology Stack

### Backend Framework

-   **Laravel**: v11.44.0
-   **PHP**: v8.3.16
-   **Database**: SQLite (development), easily configurable for production

### Frontend & UI

-   **Filament**: v3.2.142 (Admin panel framework)
-   **Livewire**: v3.5.20 (Dynamic interfaces)
-   **TailwindCSS**: v3.4.3 (Styling)

### Development & Quality Tools

-   **Laravel Sail**: v1.41.0 (Docker development environment)
-   **Laravel Pint**: v1.21.0 (Code formatting)
-   **Larastan**: v3.1.0 (Static analysis)
-   **PHPUnit**: v11.5.9 (Testing)
-   **Rector**: v2.0.9 (Code modernization)

### Additional Packages

-   **Guzzle HTTP**: HTTP client for making requests
-   **Filament JSON Field**: Enhanced JSON input components

## Open Source Libraries

SafeEye is built on top of amazing open source projects. We're grateful to the following libraries and their maintainers:

### Core Framework & Backend

-   **[Laravel Framework](https://laravel.com/)** (v11.44.0) - The PHP framework for web artisans
-   **[Guzzle HTTP](https://docs.guzzlephp.org/)** (v7.8+) - PHP HTTP client for making HTTP requests
-   **[Laravel Tinker](https://github.com/laravel/tinker)** (v2.9+) - Powerful REPL for Laravel

### Admin Panel & UI

-   **[Filament](https://filamentphp.com/)** (v3.2.142) - Elegant admin panel builder for Laravel
-   **[Livewire](https://livewire.laravel.com/)** (v3.5.20) - Full-stack framework for dynamic interfaces
-   **[Filament JSON Field](https://github.com/codebar-ag/filament-json-field)** (v1.4+) - Enhanced JSON input components for Filament

### Frontend

-   **[TailwindCSS](https://tailwindcss.com/)** (v3.4.3) - Utility-first CSS framework
-   **[Flowbite](https://flowbite.com/)** (v2.3.0) - Tailwind CSS component library
-   **[Alpine.js](https://alpinejs.dev/)** - Lightweight JavaScript framework (included with Livewire)
-   **[Vite](https://vitejs.dev/)** (v5.0+) - Next generation frontend tooling
-   **[PostCSS](https://postcss.org/)** (v8.4.38+) - Tool for transforming CSS with JavaScript
-   **[Autoprefixer](https://github.com/postcss/autoprefixer)** (v10.4.19+) - PostCSS plugin to parse CSS and add vendor prefixes
-   **[Axios](https://axios-http.com/)** (v1.6.4+) - Promise-based HTTP client for the browser

### Development & Quality Tools

-   **[Laravel Sail](https://laravel.com/docs/sail)** (v1.41.0) - Docker development environment for Laravel
-   **[Laravel Pint](https://laravel.com/docs/pint)** (v1.21.0) - Opinionated PHP code style fixer
-   **[Larastan](https://github.com/larastan/larastan)** (v3.1.0) - Laravel wrapper for PHPStan
-   **[PHPStan](https://phpstan.org/)** - Static analysis tool for PHP
-   **[Rector](https://getrector.com/)** (v2.0.9) - Automated code refactoring and upgrades
-   **[PHPUnit](https://phpunit.de/)** (v11.5.9) - Testing framework for PHP
-   **[Laravel Boost](https://github.com/laravel/boost)** (v1.1+) - Development productivity tools for Laravel
-   **[Mockery](https://github.com/mockery/mockery)** (v1.6+) - Simple PHP mocking framework
-   **[Collision](https://github.com/nunomaduro/collision)** (v8.0+) - Beautiful error reporting for console applications
-   **[Faker](https://github.com/FakerPHP/Faker)** (v1.23+) - PHP library for generating fake data
-   **[Spatie Laravel Ignition](https://github.com/spatie/laravel-ignition)** (v2.4+) - Beautiful error page for Laravel

### Build Tools

-   **[Laravel Vite Plugin](https://github.com/laravel/vite-plugin)** (v1.0+) - Laravel plugin for Vite
-   **[Composer](https://getcomposer.org/)** - Dependency manager for PHP
-   **[NPM](https://www.npmjs.com/)** - Package manager for JavaScript

## Architecture

### Core Models Relationship

```
User (1) ─── (many) Group (1) ─── (many) Check (1) ─── (many) Assertion
   │                                 │
   └── (many) Check                  └── (many) CheckHistory
```

### Check Execution Flow

1. **Scheduled Execution**: Checks run based on configured intervals
2. **HTTP Request**: System makes HTTP request to configured endpoint
3. **Assertion Validation**: Response is validated against configured assertions
4. **History Recording**: Results are stored in check_history table
5. **Notification**: If check fails, email notifications are sent

## Database Schema

### Core Tables

#### `users`

Stores user accounts and authentication information.

| Column            | Type     | Description                  |
| ----------------- | -------- | ---------------------------- |
| id                | integer  | Primary key                  |
| name              | varchar  | User's full name             |
| email             | varchar  | Email address (unique)       |
| email_verified_at | datetime | Email verification timestamp |
| password          | varchar  | Hashed password              |
| is_admin          | tinyint  | Admin flag (0/1)             |
| remember_token    | varchar  | Remember token for sessions  |
| created_at        | datetime | Account creation timestamp   |
| updated_at        | datetime | Last update timestamp        |

#### `groups`

Organizational containers for checks, allowing users to group related monitoring tasks.

| Column     | Type     | Description                |
| ---------- | -------- | -------------------------- |
| id         | integer  | Primary key                |
| name       | varchar  | Group name                 |
| user_id    | integer  | Foreign key to users table |
| created_at | datetime | Creation timestamp         |
| updated_at | datetime | Last update timestamp      |

**Foreign Keys:**

-   `user_id` → `users.id`

#### `checks`

Core monitoring configuration defining what to monitor and how.

| Column          | Type     | Description                         |
| --------------- | -------- | ----------------------------------- |
| id              | integer  | Primary key                         |
| group_id        | integer  | Foreign key to groups table         |
| user_id         | integer  | Foreign key to users table          |
| name            | varchar  | Check name/description              |
| type            | varchar  | Check type (currently 'http')       |
| endpoint        | varchar  | URL to monitor                      |
| http_method     | varchar  | HTTP method (GET, POST, etc.)       |
| interval        | integer  | Check interval in minutes           |
| request_timeout | integer  | Request timeout in seconds          |
| request_headers | text     | JSON array of headers               |
| request_body    | text     | JSON request body                   |
| notify_emails   | text     | Comma-separated notification emails |
| active          | tinyint  | Active status (0/1)                 |
| last_run_at     | datetime | Last execution timestamp            |
| created_at      | datetime | Creation timestamp                  |
| updated_at      | datetime | Last update timestamp               |

**Foreign Keys:**

-   `user_id` → `users.id` (CASCADE delete)
-   `group_id` → `groups.id`

#### `assertions`

Validation rules applied to check responses.

| Column     | Type     | Description                                         |
| ---------- | -------- | --------------------------------------------------- |
| id         | integer  | Primary key                                         |
| type       | varchar  | Assertion type (response.time, response.code, etc.) |
| sign       | varchar  | Comparison operator (eq, gt, lt, gte, lte, neq)     |
| value      | text     | Expected value to compare against                   |
| check_id   | integer  | Foreign key to checks table                         |
| created_at | datetime | Creation timestamp                                  |
| updated_at | datetime | Last update timestamp                               |

**Foreign Keys:**

-   `check_id` → `checks.id`

**Assertion Types:**

-   `response.time` - Response time validation
-   `response.code` - HTTP status code validation
-   `response.body` - Response body content (planned)
-   `response.json` - JSON response validation (planned)
-   `response.header` - Header validation (planned)
-   `ssl_certificate.expires_in` - SSL certificate expiration (planned)

#### `check_history`

Historical record of all check executions and their results.

| Column          | Type     | Description                             |
| --------------- | -------- | --------------------------------------- |
| id              | integer  | Primary key                             |
| check_id        | integer  | Foreign key to checks table             |
| notified_emails | text     | JSON array of notified email addresses  |
| metadata        | text     | JSON metadata about the check execution |
| root_cause      | text     | Error details if check failed           |
| type            | varchar  | Result type (SUCCESS, ERROR)            |
| created_at      | datetime | Execution timestamp                     |
| updated_at      | datetime | Last update timestamp                   |

**Foreign Keys:**

-   `check_id` → `checks.id`

### System Tables

#### `cache` & `cache_locks`

Laravel's cache system tables for performance optimization.

#### `jobs`, `job_batches`, `failed_jobs`

Queue system tables for background job processing.

#### `sessions`

User session management.

#### `password_reset_tokens`

Password reset functionality.

#### `migrations`

Laravel migration tracking.

## Models

### User Model

```php
final class User extends Model
{
    // User authentication and management
    // Relationships: hasMany(Group), hasMany(Check)
    // Implements: FilamentUser for admin access
}
```

### Group Model

```php
final class Group extends Model
{
    // Project/category organization
    // Relationships: belongsTo(User), hasMany(Check)
}
```

### Check Model

```php
final class Check extends Model
{
    // Core monitoring configuration
    // Relationships: belongsTo(User), belongsTo(Group),
    //               hasMany(Assertion), hasMany(CheckHistory)
    // Casts: http_method → HTTPMethod enum
    //        type → CheckType enum
    //        active → boolean
    //        request_headers, request_body → array
}
```

### Assertion Model

```php
final class Assertion extends Model
{
    // Validation rules for checks
    // Relationships: belongsTo(Check)
    // Casts: type → AssertionType enum
    //        sign → AssertionSign enum
}
```

### CheckHistory Model

```php
final class CheckHistory extends Model
{
    // Execution history and results
    // Relationships: belongsTo(Check)
    // Stores execution results, errors, and notification tracking
}
```

## Installation

### Prerequisites

-   PHP 8.3+
-   Composer
-   Node.js 20+
-   Docker Desktop (for Sail)

### Development Setup with Laravel Sail

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd safeye
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Environment setup**

    ```bash
    cp .env.example .env
    # Edit .env file, set APP_PORT=8000 to avoid port conflicts
    ```

4. **Start with Sail**

    ```bash
    ./vendor/bin/sail up -d
    ./vendor/bin/sail artisan migrate
    ./vendor/bin/sail artisan db:seed
    ./vendor/bin/sail npm install
    ./vendor/bin/sail npm run dev
    ```

5. **Access the application**
    - Web interface: `http://safeye.test:8000/`
    - Admin panel: `http://safeye.test:8000/app`

### Development Setup with Valet

1. **Use correct Node version**

    ```bash
    nvm use 20
    ```

2. **Install and setup**
    ```bash
    composer install
    valet use
    php artisan migrate
    php artisan db:seed
    npm install
    npm run dev
    ```

## Usage

### Creating Checks

1. **Access Admin Panel**: Navigate to `/app` and login
2. **Create Group** (optional): Organize checks by project
3. **Add Check**: Configure endpoint, method, headers, and timeout
4. **Set Assertions**: Define validation rules (response time, status code)
5. **Configure Notifications**: Set email addresses for alerts
6. **Activate Check**: Enable monitoring

### Managing Assertions

Assertions define what constitutes a successful check:

-   **Response Time**: `response.time` with operators like `lt` (less than) 5000ms
-   **Status Code**: `response.code` with `eq` (equals) 200
-   **Custom Values**: Define expected values based on assertion type

### Monitoring Results

-   **Dashboard**: View all checks and their current status
-   **History**: Review detailed execution history
-   **Recent Issues**: Quick access to problems in last 24 hours
-   **Email Alerts**: Automatic notifications when checks fail

### Check History Cleanup

SafeEye includes an automated cleanup system to manage check history records:

#### Configuration

Configure history retention in your `.env` file:

```env
# Number of days to keep check history (default: 30)
CHECK_HISTORY_RETENTION_DAYS=30

# Set to 0 or negative to disable automatic cleanup
CHECK_HISTORY_RETENTION_DAYS=0
```

#### Automatic Cleanup

-   **Scheduled Task**: Runs daily at midnight to clean old records
-   **Configurable Retention**: Set retention period via environment variable
-   **Safe Operation**: Only deletes records older than the specified period
-   **Logging**: Command provides feedback on cleanup operations

#### Manual Cleanup

You can manually run the cleanup command:

```bash
php artisan app:clear-old-check-history
```

The command will:

-   Delete records older than the configured retention period
-   Provide feedback on the number of records deleted
-   Skip cleanup if retention is disabled (≤ 0 days)

#### Setup Cron Job (Production)

For production environments, ensure the Laravel scheduler is running:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Development Status

### ✅ Implemented Features

-   HTTP endpoint monitoring
-   Basic assertions (response time, status code)
-   User authentication and authorization
-   Group-based organization
-   Email notification system
-   Historical tracking and reporting
-   Filament admin interface
-   Multi-user support

### 🚧 Planned Features

-   Response body content validation
-   JSON response structure validation
-   HTTP header validation
-   SSL certificate expiration monitoring
-   Dashboard widgets and analytics
-   API endpoints for external integration
-   Advanced notification channels (Slack, Teams)
-   Check scheduling improvements
-   Performance metrics and trending

### 🔧 Technical Improvements

-   Background job processing for checks
-   Rate limiting and performance optimization
-   Enhanced error handling and logging
-   Test coverage expansion
-   Docker production configuration
-   CI/CD pipeline setup

---

**Last Updated**: September 2025  
**Version**: Development  
**Laravel Version**: 11.44.0  
**PHP Version**: 8.3.16
