<?php

namespace App\Models;

use App\Enums\CheckHistoryType;
use App\Enums\CheckType;
use App\Enums\HTTPMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Check extends Model
{
    /**
     * Mass assigned properties.
     *
     * @var array
     */
    protected $fillable = [
        'group_id',
        'team_id',
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

    protected $casts = [
        'http_method' => HTTPMethod::class,
        'type' => CheckType::class,
        'active' => 'boolean',
        'request_headers' => 'array',
        'request_body' => 'array',
        'last_run_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function assertions(): HasMany
    {
        return $this->hasMany(Assertion::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(CheckHistory::class);
    }

    public function latestIssues(): HasMany
    {
        return $this->hasMany(CheckHistory::class)
            ->where('type', CheckHistoryType::ERROR)
            ->where('created_at', '<=', now())
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'desc');
    }

    public function latestChecks(): HasMany
    {
        return $this->hasMany(CheckHistory::class)
            ->where('created_at', '<=', now())
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'desc');
    }
}
