<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'domain',
    'mx_valid',
    'catch_all',
    'disposable',
    'provider',
    'reputation_score',
    'last_checked_at',
])]
class Domain extends Model
{
    protected function casts(): array
    {
        return [
            'mx_valid'         => 'boolean',
            'catch_all'        => 'boolean',
            'disposable'       => 'boolean',
            'reputation_score' => 'float',
            'last_checked_at'  => 'datetime',
        ];
    }
}
