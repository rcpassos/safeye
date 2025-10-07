<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.16
- filament/filament (FILAMENT) - v3
- laravel/framework (LARAVEL) - v11
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v3
- larastan/larastan (LARASTAN) - v3
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- rector/rector (RECTOR) - v2
- tailwindcss (TAILWINDCSS) - v3

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Internationalization (i18n)

- **Never use hardcoded text strings** in PHP files, Blade templates, or any user-facing code.
- **Always use Laravel's translation helpers** for all user-facing text:
    - Use `__('file.key')` in PHP files
    - Use `{{ __('file.key') }}` in Blade templates
    - Use `@lang('file.key')` for Blade directives when appropriate
- **Language file structure:**
    - All translation files must be stored in `lang/en/` directory
    - Organize translations by feature/context (e.g., `dashboard.php`, `checks.php`, `common.php`)
    - Use nested arrays for better organization
    - Check existing language files for similar translations before creating new ones
- **Translation key naming:**
    - Use descriptive, lowercase keys with underscores: `date_range`, `start_date`, `total_checks`
    - Group related translations under parent keys: `filters.date_range`, `stats.total_checks`
    - Keep keys consistent across files
- **When creating new features:**
    1. Create or update the appropriate language file first
    2. Use translation keys throughout the code
    3. Never commit hardcoded text
- **Examples:**

    ```php
    // BAD - Hardcoded text
    Stat::make('Total Checks', $totalChecks)

    // GOOD - Using translations
    Stat::make(__('dashboard.stats.total_checks'), $totalChecks)

    // BAD - Hardcoded in Blade
    <h1>Welcome to Dashboard</h1>

    // GOOD - Using translations in Blade
    <h1>{{ __('dashboard.welcome') }}</h1>
    ```

- **Always check for hardcoded text** before finalizing any code changes.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Testing Requirements

- **Always run the full test suite** using `composer test` after completing any request or making changes to the codebase.
- Ensure all tests pass before considering a task complete.
- If tests fail, fix the issues before finalizing your response.
- Running tests helps catch regressions and ensures code quality.

## Application Structure & Architecture

- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs

- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function \_\_construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments

- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks

- Add useful array shape type definitions for arrays when appropriate.

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== filament/core rules ===

## Filament

- Filament is used by this application, check how and where to follow existing application conventions.
- Filament is a Server-Driven UI (SDUI) framework for Laravel. It allows developers to define user interfaces in PHP using structured configuration objects. It is built on top of Livewire, Alpine.js, and Tailwind CSS.
- You can use the `search-docs` tool to get information from the official Filament documentation when needed. This is very useful for Artisan command arguments, specific code examples, testing functionality, relationship management, and ensuring you're following idiomatic practices.
- Utilize static `make()` methods for consistent component initialization.

### Artisan

- You must use the Filament specific Artisan commands to create new files or components for Filament. You can find these with the `list-artisan-commands` tool, or with `php artisan` and the `--help` option.
- Inspect the required options, always pass `--no-interaction`, and valid arguments for other options when applicable.

### Filament's Core Features

- Actions: Handle doing something within the application, often with a button or link. Actions encapsulate the UI, the interactive modal window, and the logic that should be executed when the modal window is submitted. They can be used anywhere in the UI and are commonly used to perform one-time actions like deleting a record, sending an email, or updating data in the database based on modal form input.
- Forms: Dynamic forms rendered within other features, such as resources, action modals, table filters, and more.
- Infolists: Read-only lists of data.
- Notifications: Flash notifications displayed to users within the application.
- Panels: The top-level container in Filament that can include all other features like pages, resources, forms, tables, notifications, actions, infolists, and widgets.
- Resources: Static classes that are used to build CRUD interfaces for Eloquent models. Typically live in `app/Filament/Resources`.
- Schemas: Represent components that define the structure and behavior of the UI, such as forms, tables, or lists.
- Tables: Interactive tables with filtering, sorting, pagination, and more.
- Widgets: Small component included within dashboards, often used for displaying data in charts, tables, or as a stat.

### Relationships

- Determine if you can use the `relationship()` method on form components when you need `options` for a select, checkbox, repeater, or when building a `Fieldset`:

<code-snippet name="Relationship example for Form Select" lang="php">
Forms\Components\Select::make('user_id')
    ->label('Author')
    ->relationship('author')
    ->required(),
</code-snippet>

## Testing

- It's important to test Filament functionality for user satisfaction.
- Ensure that you are authenticated to access the application within the test.
- Filament uses Livewire, so start assertions with `livewire()` or `Livewire::test()`.

### Example Tests

<code-snippet name="Filament Table Test" lang="php">
    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->searchTable($users->first()->name)
        ->assertCanSeeTableRecords($users->take(1))
        ->assertCanNotSeeTableRecords($users->skip(1))
        ->searchTable($users->last()->email)
        ->assertCanSeeTableRecords($users->take(-1))
        ->assertCanNotSeeTableRecords($users->take($users->count() - 1));
</code-snippet>

<code-snippet name="Filament Create Resource Test" lang="php">
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Howdy',
            'email' => 'howdy@example.com',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(User::class, [
        'name' => 'Howdy',
        'email' => 'howdy@example.com',
    ]);

</code-snippet>

<code-snippet name="Testing Multiple Panels (setup())" lang="php">
    use Filament\Facades\Filament;

    Filament::setCurrentPanel('app');

</code-snippet>

<code-snippet name="Calling an Action in a Test" lang="php">
    livewire(EditInvoice::class, [
        'invoice' => $invoice,
    ])->callAction('send');

    expect($invoice->refresh())->isSent()->toBeTrue();

</code-snippet>

=== filament/v3 rules ===

## Filament 3

## Version 3 Changes To Focus On

- Resources are located in `app/Filament/Resources/` directory.
- Resource pages (List, Create, Edit) are auto-generated within the resource's directory - e.g., `app/Filament/Resources/PostResource/Pages/`.
- Forms use the `Forms\Components` namespace for form fields.
- Tables use the `Tables\Columns` namespace for table columns.
- A new `Filament\Forms\Components\RichEditor` component is available.
- Form and table schemas now use fluent method chaining.
- Added `php artisan filament:optimize` command for production optimization.
- Requires implementing `FilamentUser` contract for production access control.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.
- **Always use `query()` method when building Eloquent queries**. This makes the code more explicit and consistent.
    - **Do:** `$checks = Check::query()->where('active', true)->get();`
    - **Don't:** `$checks = Check::where('active', true)->get();`

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.
- **Always add `@property` annotations** to all Eloquent models for proper PHPStan type checking. This helps PHPStan understand model properties, especially when using casts for enums or dates.

<code-snippet name="Model Property Annotations Example" lang="php">
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property HTTPMethod $http_method
 * @property CheckType $type
 * @property array<string, mixed> $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Check extends Model
{
    // Model implementation
}
</code-snippet>

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== action pattern rules ===

## Action Pattern

- This application uses the **Action Pattern** as the default approach for all business logic.
- Actions are single-responsibility classes that encapsulate business logic, making code more testable, reusable, and maintainable.
- All business logic should be extracted into Actions rather than being placed directly in controllers, commands, Livewire components, or jobs.

### When to Create an Action

**Always create an Action when:**

- Implementing any business logic that performs a specific task or operation
- Logic is used in multiple places (controllers, commands, jobs, Livewire components, etc.)
- A method is complex enough to deserve its own class with proper testing
- You need to coordinate multiple operations or models

**Do NOT create an Action for:**

- Simple Eloquent queries that are only used once (use `Model::query()` directly)
- Simple attribute accessors/mutators on models
- Basic CRUD operations handled by Filament resources without additional logic
- One-line operations or trivial transformations

### Action Naming Conventions

Actions should be named using **verb-noun** format that clearly describes what they do:

**Good Examples:**

- `RunSingleCheck` - Runs a single check
- `SaveCheckHistory` - Saves check history
- `EvaluateAssertion` - Evaluates an assertion
- `NotifyCheckIncident` - Notifies about a check incident
- `ClearOldCheckHistory` - Clears old check history
- `PrepareCheckFormData` - Prepares check form data
- `CreateUser` - Creates a user
- `SendWelcomeEmail` - Sends a welcome email
- `CalculateInvoiceTotal` - Calculates invoice total
- `ProcessPayment` - Processes a payment

**Bad Examples:**

- `Check` - Not descriptive
- `Handler` - Too generic
- `Service` - Not an action name
- `Helper` - Not an action name

### Action Structure

All Actions must follow these structural rules:

1. **Single Public Method**: One public `handle()` method
2. **Constructor Injection**: Dependencies injected via constructor with property promotion
3. **Private Helper Methods**: Additional private methods allowed as needed
4. **Return Type**: Always declare explicit return types
5. **Final Classes**: Actions should be declared as `final` classes
6. **Strict Types**: Use `declare(strict_types=1);`

<code-snippet name="Action Structure Template" lang="php">
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

final class CreateUserAccount
{
public function \_\_construct(
private readonly SendWelcomeEmail $sendWelcomeEmail,
private readonly AssignDefaultPermissions $assignDefaultPermissions
) {}

    public function handle(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $this->assignDefaultPermissions->handle($user);
        $this->sendWelcomeEmail->handle($user);

        return $user;
    }

    private function validateEmailDomain(string $email): bool
    {
        // Private helper method if needed
        return true;
    }

}
</code-snippet>

### Action Location

- Store all Actions in `app/Actions/` directory
- Use flat structure unless the project grows significantly
- Keep Action files organized alphabetically

### Using Actions

Actions can be injected or resolved from the container:

<code-snippet name="Action Usage in Controllers" lang="php">
final class CheckController extends Controller
{
    public function __construct(
        private readonly RunSingleCheck $runSingleCheck
    ) {}

    public function execute(Check $check): RedirectResponse
    {
        $this->runSingleCheck->handle($check);

        return redirect()->route('checks.show', $check);
    }

}
</code-snippet>

<code-snippet name="Action Usage in Commands" lang="php">
final class RunChecks extends Command
{
    public function handle(RunSingleCheck $runSingleCheck): int
    {
        $checks = Check::where('active', true)->get();
        
        foreach ($checks as $check) {
            $runSingleCheck->handle($check);
        }
        
        return self::SUCCESS;
    }
}
</code-snippet>

<code-snippet name="Action Usage in Livewire" lang="php">
final class CheckManager extends Component
{
    public function runCheck(RunSingleCheck $runSingleCheck, Check $check): void
    {
        $runSingleCheck->handle($check);
        
        $this->dispatch('check-completed');
    }
}
</code-snippet>

<code-snippet name="Action Usage with app() Helper" lang="php">
// When constructor injection is not available
app(RunSingleCheck::class)->handle($check);
</code-snippet>

### Testing Actions

Every Action must have comprehensive tests covering:

- Happy path scenarios
- Edge cases
- Error handling
- All conditional branches

<code-snippet name="Action Test Structure" lang="php">
<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\CreateUserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateUserAccountTest extends TestCase
{
use RefreshDatabase;

    public function test_creates_user_account_successfully(): void
    {
        $action = app(CreateUserAccount::class);

        $user = $action->handle([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $this->assertInstanceOf(User::class, $user);
    }

    public function test_sends_welcome_email_after_creation(): void
    {
        Mail::fake();

        $action = app(CreateUserAccount::class);
        $user = $action->handle([...]);

        Mail::assertSent(WelcomeEmail::class);
    }

    public function test_handles_duplicate_email_gracefully(): void
    {
        // Test error scenarios
    }

}
</code-snippet>

### Action Composition

Actions can and should call other Actions to compose complex workflows:

<code-snippet name="Action Calling Other Actions" lang="php">
final class CompleteCheckout
{
    public function __construct(
        private readonly ProcessPayment $processPayment,
        private readonly CreateOrder $createOrder,
        private readonly SendOrderConfirmation $sendOrderConfirmation,
        private readonly UpdateInventory $updateInventory
    ) {}

    public function handle(Cart $cart, array $paymentData): Order
    {
        $payment = $this->processPayment->handle($paymentData);
        $order = $this->createOrder->handle($cart, $payment);
        $this->updateInventory->handle($cart->items);
        $this->sendOrderConfirmation->handle($order);

        return $order;
    }

}
</code-snippet>

### Best Practices

1. **Keep Actions Focused**: Each Action should do one thing well
2. **Avoid Static Methods**: Always use instance methods with dependency injection
3. **No Facades in Actions**: Inject dependencies instead of using facades (exception: Cache, Log when appropriate)
4. **Return Values**: Actions should return meaningful values (models, collections, primitives, or void)
5. **Validation**: Validate inputs at the entry point (Form Requests, Livewire validation), not in Actions
6. **Database Transactions**: Use transactions within Actions when coordinating multiple database operations
7. **Testing**: Always test Actions in isolation using dependency injection and mocking

=== laravel/v11 rules ===

## Laravel 11

- Use the `search-docs` tool to get version specific documentation.
- Laravel 11 brought a new streamlined file structure which this project now uses.

### Laravel 11 Structure

- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

### New Artisan Commands

- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `php artisan make:enum`
    - `php artisan make:class`
    - `php artisan make:interface`

=== livewire/core rules ===

## Livewire Core

- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices

- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>

## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>

    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>

=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2

- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives

- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine

- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks

- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });

});
</code-snippet>

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest Testing Framework

- This application uses Pest for testing. All tests must be written using Pest syntax. Use `php artisan make:test --pest <name>` to create a new test.
- Pest is a delightful testing framework with a focus on simplicity. Tests are written using functions rather than classes.
- Every time a test has been updated, run that singular test using `vendor/bin/pest --filter <testname>`.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Writing Pest Tests

- Use the `test()` function for writing tests with descriptive names.
- Use `beforeEach()` for test setup instead of `setUp()` methods.
- Use `expect()` for assertions instead of `$this->assert*()` methods.
- Tests automatically use `RefreshDatabase` trait when configured in `Pest.php`.

### Running Tests

- Run all tests: `vendor/bin/pest` or `composer test:unit`.
- Run all tests in a file: `vendor/bin/pest tests/Feature/ExampleTest.php`.
- Filter on a particular test name: `vendor/bin/pest --filter="test name"` (recommended after making a change to a related file).
- Run with coverage: `vendor/bin/pest --coverage`.

### Pest Expectations

- Use `expect($value)->toBe()`, `->toBeTrue()`, `->toBeFalse()`, `->toBeNull()`, `->toBeEmpty()`, etc.
- Use `expect($collection)->toHaveCount()` instead of `assertCount()`.
- Use `expect($value)->toBeInstanceOf(Class::class)` instead of `assertInstanceOf()`.
- Laravel assertions like `$this->assertDatabaseHas()` and `$this->actingAs()` still work in Pest.

### Example Pest Test

<code-snippet name="Pest Test Example" lang="php">
use App\Models\User;

test('user can be created', function () {
$user = User::factory()->create([
'name' => 'John Doe',
]);

    expect($user->name)->toBe('John Doe');
    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
    ]);

});

test('user can login', function () {
$user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertStatus(200);

});
</code-snippet>

<code-snippet name="Pest Test with beforeEach" lang="php">
use App\Models\User;
use App\Models\Post;

beforeEach(function () {
$this->user = User::factory()->create();
$this->post = Post::factory()->create(['user_id' => $this->user->id]);
});

test('user can view their own post', function () {
$this->actingAs($this->user);

    $response = $this->get("/posts/{$this->post->id}");

    $response->assertStatus(200);

});
</code-snippet>

=== pest/v4 rules ===

## Pest 4

- Pest 4 comes with built-in architecture testing, mutation testing, and profiling capabilities.
- Use `arch()` for architecture tests to ensure code follows expected patterns.
- All Pest configuration is in `tests/Pest.php` file.

=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing

- When listing items, use gap utilities for spacing, don't use margins.

              <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
                  <div class="flex gap-8">
                      <div>Superior</div>
                      <div>Michigan</div>
                      <div>Erie</div>
                  </div>
              </code-snippet>

### Dark Mode

- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v3 rules ===

## Tailwind 3

- Always use Tailwind CSS v3 - verify you're using only classes supported by this version.
  </laravel-boost-guidelines>
