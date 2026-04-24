<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['email_id', 'signal_type', 'value'])]
class EmailSignal extends Model
{
    // ─── Relationships ───────────────────────────────────────
    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }
}
