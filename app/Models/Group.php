<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    /**
     * Mass assigned properties.
     *
     * @var array
     */
    protected $fillable = [
        'team_id',
        'name',
    ];

    public function checks(): HasMany
    {
        return $this->hasMany(Check::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
