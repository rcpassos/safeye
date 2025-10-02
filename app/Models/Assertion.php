<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    protected $casts = [
        'type' => AssertionType::class,
        'sign' => AssertionSign::class,
    ];
}
