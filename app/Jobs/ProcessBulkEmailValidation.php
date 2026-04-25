<?php

namespace App\Jobs;

use App\Models\BulkJob;
use App\Models\Domain;
use App\Models\Email;
use App\Models\EmailSignal;
use App\Models\Validation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessBulkEmailValidation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 3600;
    public int $backoff = 30;

    public function __construct(
        public BulkJob $bulkJob,
        public array $emails,
        public int $userId
    ) {}

    public function handle(): void
    {
        // Check if job was cancelled before starting
        if ($this->bulkJob->isCancelled()) {
            Log::info('Bulk validation cancelled', ['job_id' => $this->bulkJob->id]);
            return;
        }
        
        $total = count($this->emails);
        
        // Ensure job starts fresh if restarting
        $this->bulkJob->update(['status' => 'processing']);

        $results = [];
        $service = new \App\Http\Services\EmailValidationService();
        $smtpAvailable = $service->isSmtpAvailable();

        Log::info('Bulk validation started', [
            'job_id' => $this->bulkJob->id,
            'total' => $total,
            'smtp_available' => $smtpAvailable,
        ]);

        foreach ($this->emails as $index => $emailAddress) {
            // Check cancellation periodically
            if ($index % 10 === 0) {
                $this->bulkJob->refresh();
                if ($this->bulkJob->isCancelled()) {
                    Log::info('Bulk validation cancelled mid-process', ['job_id' => $this->bulkJob->id]);
                    return;
                }
            }
            
            $emailAddress = trim($emailAddress);

            if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                $results[] = [
                    'email'   => $emailAddress,
                    'valid'   => false,
                    'score'   => 0,
                    'error'   => 'invalid_syntax',
                ];
                $this->bulkJob->increment('processed');
                continue;
            }

            try {
                $result = $service->validate($emailAddress);

                DB::transaction(function () use ($emailAddress, $result) {
                    $domainName = explode('@', $emailAddress, 2)[1] ?? '';

                    Domain::updateOrCreate(
                        ['domain' => strtolower($domainName)],
                        [
                            'mx_valid'         => $result['mx'],
                            'catch_all'        => $result['is_catch_all'],
                            'disposable'       => $result['is_spam_trap'],
                            'reputation_score' => $result['score'],
                            'last_checked_at'  => now(),
                        ]
                    );

                    $status = $result['valid'] ? 'valid' : 'invalid';

                    $email = Email::updateOrCreate(
                        ['email' => strtolower($emailAddress)],
                        [
                            'domain'     => strtolower($domainName),
                            'status'     => $status,
                            'score'      => $result['score'],
                            'mx'         => $result['mx'],
                            'smtp'       => $result['smtp'],
                            'disposable' => $result['is_spam_trap'],
                            'catch_all'  => $result['is_catch_all'],
                            'confidence' => $result['score'],
                        ]
                    );

                    $email->increment('times_checked');

                    $signals = [
                        'syntax'        => $result['syntax'] ? 'pass' : 'fail',
                        'mx'            => $result['mx'] ? 'pass' : 'fail',
                        'smtp'          => $result['smtp'] ? 'pass' : 'fail',
                        'is_alias'      => $result['is_alias'] ? 'yes' : 'no',
                        'is_catch_all'  => $result['is_catch_all'] ? 'yes' : 'no',
                        'is_disabled'   => $result['is_disabled'] ? 'yes' : 'no',
                        'is_spam_trap'  => $result['is_spam_trap'] ? 'yes' : 'no',
                        'mailbox_level' => $result['mailbox_level'],
                        'free'          => $result['free'] ? 'yes' : 'no',
                        'source'        => $result['source'] ?? 'internal',
                    ];

                    if ($result['domain_age_days'] !== null) {
                        $signals['domain_age_days'] = (string) $result['domain_age_days'];
                    }

                    if ($result['did_you_mean'] !== null) {
                        $signals['did_you_mean'] = $result['did_you_mean'];
                    }

                    foreach ($signals as $type => $value) {
                        EmailSignal::updateOrCreate(
                            ['email_id' => $email->id, 'signal_type' => $type],
                            ['value' => $value]
                        );
                    }

                    Validation::create([
                        'user_id'  => $this->userId,
                        'email_id' => $email->id,
                        'result'   => $status,
                        'score'    => $result['score'],
                        'cost'     => 1,
                        'source'   => 'bulk',
                    ]);
                });

                $results[] = [
                    'email'        => $emailAddress,
                    'valid'        => $result['valid'],
                    'score'       => $result['score'],
                    'mx'          => $result['mx'],
                    'smtp'        => $result['smtp'],
                    'free'        => $result['free'],
                    'catch_all'   => $result['is_catch_all'],
                    'source'      => $result['source'] ?? 'internal',
                ];
            } catch (\Exception $e) {
                Log::error('Bulk email validation error', [
                    'email' => $emailAddress,
                    'error' => $e->getMessage(),
                ]);

                $results[] = [
                    'email'   => $emailAddress,
                    'valid'  => false,
                    'score'  => 0,
                    'error'  => 'validation_error',
                ];
            }

            $this->bulkJob->increment('processed');
        }

        $validCount = count(array_filter($results, fn($r) => $r['valid'] ?? false));
        
        $this->bulkJob->update([
            'status'  => 'completed',
            'results' => $results,
        ]);

        Log::info('Bulk validation completed', [
            'job_id'     => $this->bulkJob->id,
            'total'     => $total,
            'valid'    => $validCount,
            'invalid'  => $total - $validCount,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk validation job failed permanently', [
            'job_id' => $this->bulkJob->id,
            'error' => $exception->getMessage(),
        ]);

        $this->bulkJob->update([
            'status'  => 'failed',
            'results' => [['error' => 'Job failed: ' . $exception->getMessage()]],
        ]);
    }
}