<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CheckHistoryType;
use App\Enums\CheckType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $name
 * @property CheckType $type
 * @property string $endpoint
 * @property int $interval
 * @property array<string, mixed> $config
 * @property string $notify_emails
 * @property string|null $slack_webhook_url
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $last_run_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Check extends Model
{
    use HasFactory;

    /**
     * Mass assigned properties.
     *
     * @var list<string>
     */
    protected $fillable = [
        'group_id',
        'user_id',
        'name',
        'type',
        'endpoint',
        'interval',
        'config',
        'notify_emails',
        'slack_webhook_url',
        'active',
        'last_run_at',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Group, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /** @return HasMany<Assertion, $this> */
    public function assertions(): HasMany
    {
        return $this->hasMany(Assertion::class);
    }

    /** @return HasMany<CheckHistory, $this> */
    public function history(): HasMany
    {
        return $this->hasMany(CheckHistory::class);
    }

    /** @return HasMany<CheckHistory, $this> */
    public function latestIssues(): HasMany
    {
        return $this->hasMany(CheckHistory::class)
            ->where('type', CheckHistoryType::ERROR)
            ->where('created_at', '<=', now())
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'desc');
    }

    /** @return HasMany<CheckHistory, $this> */
    public function latestChecks(): HasMany
    {
        return $this->hasMany(CheckHistory::class)
            ->where('created_at', '<=', now())
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'type' => CheckType::class,
            'active' => 'boolean',
            'config' => 'array',
            'last_run_at' => 'datetime',
        ];
    }
}
