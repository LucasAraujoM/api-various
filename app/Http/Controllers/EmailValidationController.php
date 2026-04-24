<?php

namespace App\Http\Controllers;

use App\Http\Services\EmailValidationService;
use App\Models\Domain;
use App\Models\Email;
use App\Models\EmailSignal;
use App\Models\Validation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmailValidationController extends Controller
{
    public function index(Request $request, EmailValidationService $emailValidationService)
    {
        // ── 1. Validate input ────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:rfc,dns,spoof', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $user = $request->user();

        // ── 2. Credit check ──────────────────────────────────────
        if (!$user->hasCredits()) {
            return response()->json([
                'message' => 'Insufficient credits. Please upgrade your plan.',
            ], 402);
        }

        try {
            return DB::transaction(function () use ($request, $user, $emailValidationService) {
                $emailAddress = $request->input('email');
                [, $domainName] = explode('@', $emailAddress, 2);

                // ── 3. Run validation service ────────────────────
                $result = $emailValidationService->validate($emailAddress);

                // ── 4. Persist / update the Domain record ────────
                $domain = Domain::updateOrCreate(
                    ['domain' => strtolower($domainName)],
                    [
                        'mx_valid'         => $result['mx'],
                        'catch_all'        => $result['is_catch_all'],
                        'disposable'       => $result['is_spam_trap'],
                        'reputation_score' => $result['score'],
                        'last_checked_at'  => now(),
                    ]
                );

                // ── 5. Persist / update the Email record ─────────
                $status = $result['valid'] ? 'valid' : 'invalid';

                $email = Email::updateOrCreate(
                    ['email' => strtolower($emailAddress)],
                    [
                        'domain'        => strtolower($domainName),
                        'status'        => $status,
                        'score'         => $result['score'],
                        'mx'            => $result['mx'],
                        'smtp'          => $result['smtp'],
                        'disposable'    => $result['is_spam_trap'],
                        'catch_all'     => $result['is_catch_all'],
                        'confidence'    => $result['score'],
                    ]
                );

                $email->increment('times_checked');

                // ── 6. Store granular signals ─────────────────────
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
                ];

                if ($result['domain_age_days'] !== null) {
                    $signals['domain_age_days'] = (string) $result['domain_age_days'];
                }

                if ($result['did_you_mean'] !== null) {
                    $signals['did_you_mean'] = $result['did_you_mean'];
                }

                foreach ($signals as $type => $value) {
                    EmailSignal::create([
                        'email_id'    => $email->id,
                        'signal_type' => $type,
                        'value'       => $value,
                    ]);
                }

                // ── 7. Create Validation audit record ────────────
                Validation::create([
                    'user_id'  => $user->id,
                    'email_id' => $email->id,
                    'result'   => $status,
                    'score'    => $result['score'],
                    'cost'     => 1,
                    'source'   => 'api',
                ]);

                // ── 8. Deduct credit ─────────────────────────────
                $user->deductCredit();

                // ── 9. Return response ───────────────────────────
                return response()->json($result);
            });
        } catch (Exception $e) {
            Log::error('Email validation failed', [
                'user_id' => $user->id,
                'email'   => $request->input('email'),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An error occurred while validating the email.',
            ], 500);
        }
    }
    public function bulk(Request $request)
    {
        $csv = $request->file('csv');
        
    }
}
