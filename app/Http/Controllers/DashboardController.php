<?php

namespace App\Http\Controllers;

use App\Models\BulkJob;
use App\Models\Email;
use App\Models\Validation;
use Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $validations = Validation::where('user_id', $user->id)->count();
        $validEmails = Email::whereHas('validations', fn($q) => $q->where('user_id', $user->id)->where('result', 'valid'))->count();
        $invalidEmails = Email::whereHas('validations', fn($q) => $q->where('user_id', $user->id)->where('result', 'invalid'))->count();
        
        $recentValidations = Validation::where('user_id', $user->id)
            ->with('email:id,email')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'email_id', 'result', 'created_at']);

        $recentJobs = BulkJob::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get(['id', 'total', 'processed', 'status', 'created_at']);

        return view('pages.dashboard', [
            'stats' => [
                'credits' => [
                    'remaining' => $user->credits,
                    'used_today' => 0,
                ],
                'validations' => [
                    'total' => $validations,
                ],
                'emails' => [
                    'valid' => $validEmails,
                    'invalid' => $invalidEmails,
                ],
            ],
            'recentActivity' => [
                'validations' => $recentValidations,
                'jobs' => $recentJobs,
            ],
        ]);
    }
}