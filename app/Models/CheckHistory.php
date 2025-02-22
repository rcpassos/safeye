<?php

namespace App\Models;

use App\Enums\CheckHistoryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckHistory extends Model
{
    use HasFactory;

    protected $table = 'check_history';

    protected $fillable = [
        'check_id',
        'notified_emails',
        'metadata',
        'root_cause',
        'type',
    ];

    protected $casts = [
        'metadata' => 'array',
        'root_cause' => 'array',
        'type' => CheckHistoryType::class,
    ];

    public function check(): BelongsTo
    {
        return $this->belongsTo(Check::class);
    }
}
