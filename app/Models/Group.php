<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Group extends Model
{
    /**
     * Mass assigned properties.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
    ];

    /** @return HasMany<Check, $this> */
    public function checks(): HasMany
    {
        return $this->hasMany(Check::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
