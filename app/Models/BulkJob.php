<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'total',
    'processed',
    'status',
    'results',
])]
class BulkJob extends Model
{
    protected function casts(): array
    {
        return [
            'total'     => 'integer',
            'processed' => 'integer',
            'results'   => 'array',
        ];
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    // ─── Relationships ───────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
