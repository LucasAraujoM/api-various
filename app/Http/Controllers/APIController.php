<?php

namespace App\Http\Controllers;

use App\Models\BulkJob;
use Auth;
use Illuminate\Http\Request;

class APIController extends Controller
{
    public function cancelBulkJob(int $id)
    {
        $job = BulkJob::where('user_id', auth()->id())->findOrFail($id);

        if (!$job->canCancel()) {
            return response()->json([
                'message' => 'This job cannot be cancelled',
            ], 400);
        }

        $job->cancel();

        return response()->json([
            'message' => 'Job cancelled successfully',
        ]);
    }

    public function bulkValidation(Request $request)
    {
        $jobs = BulkJob::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        $stats = [
            'total' => BulkJob::where('user_id', auth()->id())->count(),
            'completed' => BulkJob::where('user_id', auth()->id())->where('status', 'completed')->count(),
            'processing' => BulkJob::where('user_id', auth()->id())->whereIn('status', ['pending', 'processing'])->count(),
            'failed' => BulkJob::where('user_id', auth()->id())->where('status', 'failed')->count(),
        ];

        return view('pages.bulk-validation', compact('jobs', 'stats'));
    }
}