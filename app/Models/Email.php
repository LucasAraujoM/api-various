<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'email',
    'domain',
    'status',
    'score',
    'mx',
    'smtp',
    'disposable',
    'role',
    'catch_all',
    'confidence',
    'times_checked',
])]
class Email extends Model
{

    protected function casts(): array
    {
        return [
            'score'      => 'float',
            'mx'         => 'boolean',
            'smtp'       => 'boolean',
            'disposable' => 'boolean',
            'role'       => 'boolean',
            'catch_all'  => 'boolean',
            'confidence' => 'float',
        ];
    }

    // ─── Relationships ───────────────────────────────────────

    public function validations(): HasMany
    {
        return $this->hasMany(Validation::class);
    }

    public function signals(): HasMany
    {
        return $this->hasMany(EmailSignal::class);
    }
}
