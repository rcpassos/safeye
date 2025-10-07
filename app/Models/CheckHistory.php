<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CheckHistoryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $check_id
 * @property string|null $notified_emails
 * @property array<string, mixed>|null $metadata
 * @property array<string, mixed>|null $root_cause
 * @property CheckHistoryType $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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

    /** @return BelongsTo<Check, $this> */
    public function check(): BelongsTo
    {
        return $this->belongsTo(Check::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'metadata' => 'array',
            'root_cause' => 'array',
            'type' => CheckHistoryType::class,
        ];
    }
}
