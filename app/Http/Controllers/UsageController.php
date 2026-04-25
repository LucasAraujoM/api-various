<?php

namespace App\Http\Controllers;

use App\Models\BulkJob;
use App\Models\Email;
use App\Models\Validation;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UsageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $queries = $this->buildValidationQuery($request, $user);
        
        $stats = $this->calculateStats($queries['base'], $user);
        $calls = $queries['paginated']->orderByDesc('created_at')->paginate(15);
        
        $bulkJobs = BulkJob::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $recentEmails = $this->getMostValidatedEmails($user);

        return view('pages.usage', [
            'user' => $user,
            'calls' => $calls,
            'bulkJobs' => $bulkJobs,
            'recentEmails' => $recentEmails,
            'stats' => $stats,
            'filters' => [
                'status' => $request->get('status', 'all'),
                'source' => $request->get('source', 'all'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ],
        ]);
    }

    public function showValidation(int $id)
    {
        $validation = Validation::where('user_id', auth()->id())
            ->with(['email', 'email.signals'])
            ->findOrFail($id);

        return response()->json([
            'validation' => [
                'id' => $validation->id,
                'email' => $validation->email?->email,
                'result' => $validation->result,
                'score' => $validation->score,
                'source' => $validation->source,
                'cost' => $validation->cost,
                'created_at' => $validation->created_at->toIso8601String(),
            ],
            'email_details' => $validation->email ? [
                'domain' => $validation->email->domain,
                'status' => $validation->email->status,
                'mx' => $validation->email->mx,
                'smtp' => $validation->email->smtp,
                'disposable' => $validation->email->disposable,
                'catch_all' => $validation->email->catch_all,
                'times_checked' => $validation->email->times_checked,
                'last_checked_at' => $validation->email->last_checked_at?->toIso8601String(),
            ] : null,
            'signals' => $validation->email?->signals->map(fn($s) => [
                'type' => $s->signal_type,
                'value' => $s->value,
            ])->toArray() ?? [],
        ]);
    }

    public function export(Request $request)
    {
        $queries = $this->buildValidationQuery($request, auth()->user());
        $validations = $queries['base']->orderByDesc('created_at')->get();

        $filename = 'validations_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($validations) {
            $handle = fopen('php://output', 'w');
            
            fputcsv($handle, [
                'ID',
                'Email',
                'Result',
                'Score',
                'Source',
                'Cost',
                'Domain',
                'MX',
                'SMTP',
                'Disposable',
                'Catch All',
                'Created At',
            ]);

            foreach ($validations as $v) {
                fputcsv($handle, [
                    $v->id,
                    $v->email?->email ?? '',
                    $v->result,
                    $v->score,
                    $v->source,
                    $v->cost,
                    $v->email?->domain ?? '',
                    $v->email?->mx ? 'Yes' : 'No',
                    $v->email?->smtp ? 'Yes' : 'No',
                    $v->email?->disposable ? 'Yes' : 'No',
                    $v->email?->catch_all ? 'Yes' : 'No',
                    $v->created_at->toIso8601String(),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildValidationQuery(Request $request, $user)
    {
        $query = Validation::where('user_id', $user->id)->with('email');

        if ($request->get('status') !== 'all' && $request->get('status')) {
            $query->where('result', $request->get('status'));
        }

        if ($request->get('source') !== 'all' && $request->get('source')) {
            $query->where('source', $request->get('source'));
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        return [
            'base' => $query,
            'paginated' => $query,
        ];
    }

    private function calculateStats($query, $user): array
    {
        return [
            'total_validations' => (clone $query)->count(),
            'total_credits_used' => (clone $query)->sum('cost'),
            'valid_count' => (clone $query)->where('result', 'valid')->count(),
            'invalid_count' => (clone $query)->where('result', 'invalid')->count(),
            'credits_remaining' => $user->credits,
        ];
    }

    private function getMostValidatedEmails($user)
    {
        return Email::whereHas('validations', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderByDesc('times_checked')
            ->limit(10)
            ->get();
    }
}