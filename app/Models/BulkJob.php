<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'file_path',
    'total',
    'processed',
    'status',
])]
class BulkJob extends Model
{
    protected function casts(): array
    {
        return [
            'total'     => 'integer',
            'processed' => 'integer',
        ];
    }

    // ─── Relationships ───────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
