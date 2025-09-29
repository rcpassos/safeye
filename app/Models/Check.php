<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CheckHistoryType;
use App\Enums\CheckType;
use App\Enums\HTTPMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'http_method',
        'interval',
        'request_timeout',
        'request_headers',
        'request_body',
        'notify_emails',
        'active',
        'last_run_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'http_method' => HTTPMethod::class,
        'type' => CheckType::class,
        'active' => 'boolean',
        'request_headers' => 'array',
        'request_body' => 'array',
        'last_run_at' => 'datetime',
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
}
