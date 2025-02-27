<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CheckHistoryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CheckHistory extends Model
{
    use HasFactory;

    protected $table = 'check_history';

    /**
     * Mass assigned properties.
     *
     * @var list<string>
     */
    protected $fillable = [
        'check_id',
        'notified_emails',
        'metadata',
        'root_cause',
        'type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'root_cause' => 'array',
        'type' => CheckHistoryType::class,
    ];

    /** @return BelongsTo<Check, $this> */
    public function check(): BelongsTo
    {
        return $this->belongsTo(Check::class);
    }
}
