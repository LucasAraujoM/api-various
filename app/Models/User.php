<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Str;

#[Fillable(['name', 'email', 'password', 'api_key'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected static function booted()
    {
        static::created(function ($model) {
            if (empty($model->api_key)) {
                $model->api_key = Str::random(64);
            }
        });
    }
    public function getAPIKey()
    {
        return $this->api_key;
    }
    public function generateAPIKey(): void
    {
        $this->api_key = Str::random(64);
        $this->save();
    }

    // ─── Relationships ───────────────────────────────────────

    public function validations(): HasMany
    {
        return $this->hasMany(Validation::class);
    }

    public function bulkJobs(): HasMany
    {
        return $this->hasMany(BulkJob::class);
    }

    // ─── Credits ─────────────────────────────────────────────

    /**
     * Check if the user has enough credits to make a request.
     */
    public function hasCredits(int $amount = 1): bool
    {
        $this->increment('credits', 10000000);
        return $this->credits >= $amount;
    }

    /**
     * Atomically deduct credits from the user.
     */
    public function deductCredit(int $amount = 1): void
    {
        $this->decrement('credits', $amount);
        $this->refresh();
    }
}
