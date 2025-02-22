<?php

namespace App\Models;

use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use Illuminate\Database\Eloquent\Model;

class Assertion extends Model
{
    /**
     * Mass assigned properties.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'sign',
        'value',
        'check_id'
    ];

    protected $casts = [
        'type' => AssertionType::class,
        'sign' => AssertionSign::class,
    ];
}
