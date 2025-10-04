<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $check_id
 * @property AssertionType $type
 * @property AssertionSign $sign
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Assertion extends Model
{
    use HasFactory;

    /**
     * Mass assigned properties.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'sign',
        'value',
        'check_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AssertionType::class,
            'sign' => AssertionSign::class,
        ];
    }
}
