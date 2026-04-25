@extends('layout.app')

@section('title', 'Bulk Email Validation')

@section('content')
    <div class="bulk-page">
        <div class="page-header">
            <h1 class="page-title">Bulk Validation</h1>
            <a href="{{ route('docs') }}" class="link-btn-small">Docs</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-label">Total Jobs</p>
                <p class="stat-value">{{ $stats['total'] }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Completed</p>
                <p class="stat-value text-green-400">{{ $stats['completed'] }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">In Progress</p>
                <p class="stat-value text-yellow-400">{{ $stats['processing'] }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Failed</p>
                <p class="stat-value text-red-400">{{ $stats['failed'] }}</p>
            </div>
        </div>

        @if($jobs->isEmpty())
            <div class="empty-card">
                <p class="text-muted">No bulk validation jobs yet.</p>
                <p class="text-muted text-sm">Use the API or documentation to submit bulk email validation requests.</p>
            </div>
        @else
            <div class="jobs-list">
                @foreach($jobs as $job)
                    <div class="job-card">
                        <div class="job-header">
                            <div class="job-info">
                                <h3 class="job-title">Job #{{ $job->id }}</h3>
                                <p class="job-date">{{ $job->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                            <div class="job-status">
                                <span class="badge badge-{{ $job->status }}">{{ $job->status }}</span>
                                @if($job->canCancel())
                                    <button class="cancel-btn" onclick="cancelJob({{ $job->id }})">Cancel</button>
                                @endif
                            </div>
                        </div>

                        <div class="job-progress">
                            <div class="progress-label">
                                <span>Progress</span>
                                <span>{{ $job->processed }} / {{ $job->total }}</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ $job->total > 0 ? ($job->processed / $job->total) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        @if($job->status === 'completed' && $job->results)
                            <div class="job-results">
                                <h4 class="results-title">Results</h4>
                                <div class="results-table-wrapper">
                                    <table class="results-table">
                                        <thead>
                                            <tr>
                                                <th>Email</th>
                                                <th>Valid</th>
                                                <th>Score</th>
                                                <th>MX</th>
                                                <th>SMTP</th>
                                                <th>Free</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($job->results as $result)
                                                <tr>
                                                    <td class="truncate max-w-[200px]">{{ $result['email'] ?? 'N/A' }}</td>
                                                    <td>
                                                        @if(isset($result['valid']))
                                                            <span class="badge-{{ $result['valid'] ? 'success' : 'error' }}">
                                                                {{ $result['valid'] ? 'Yes' : 'No' }}
                                                            </span>
                                                        @else
                                                            <span class="badge badge-error">{{ $result['error'] ?? 'Error' }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($result['score'] ?? 0, 2) }}</td>
                                                    <td>{{ ($result['mx'] ?? false) ? '✓' : '✗' }}</td>
                                                    <td>{{ ($result['smtp'] ?? false) ? '✓' : '✗' }}</td>
                                                    <td>{{ ($result['free'] ?? false) ? '✓' : '✗' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            
            @if ($jobs->hasPages())
                <div class="pagination">
                    {{ $jobs->links() }}
                </div>
            @endif
        @endif
    </div>

    <script>
        function cancelJob(jobId) {
            if (!confirm('Are you sure you want to cancel this job?')) return;
            
            fetch(`/bulk-jobs/${jobId}/cancel`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.message) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                }
            })
            .catch(err => {
                showToast('Failed to cancel job', 'error');
            });
        }
    </script>

    <style>
        .link-btn {
            color: var(--accent-color);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: rgba(88, 166, 255, 0.1);
            transition: background 0.2s;
        }
        .link-btn:hover {
            background: rgba(88, 166, 255, 0.2);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .empty-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
        }
        .jobs-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .job-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem;
            transition: border-color 0.3s;
        }
        .job-card:hover {
            border-color: var(--accent-color);
        }
        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .job-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .job-title {
            font-size: 1.125rem;
            font-weight: 600;
        }
        .job-date {
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        .job-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        .badge-completed {
            background: rgba(46, 160, 67, 0.2);
            color: #2ea043;
        }
        .badge-processing {
            background: rgba(88, 166, 255, 0.2);
            color: #58a6ff;
        }
        .badge-pending {
            background: rgba(210, 153, 34, 0.2);
            color: #d29922;
        }
        .badge-failed {
            background: rgba(248, 81, 73, 0.2);
            color: #f85149;
        }
        .badge-cancelled {
            background: rgba(139, 148, 151, 0.2);
            color: #8b949e;
        }
        .cancel-btn {
            background: rgba(248, 81, 73, 0.15);
            color: #f85149;
            border: 1px solid rgba(248, 81, 73, 0.3);
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .cancel-btn:hover {
            background: rgba(248, 81, 73, 0.25);
        }
        .job-progress {
            margin-top: 1rem;
        }
        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }
        .progress-bar {
            background: var(--border);
            border-radius: 4px;
            height: 8px;
            overflow: hidden;
        }
        .progress-fill {
            background: var(--accent-color);
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .bulk-page {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        .link-btn-small {
            padding: 0.4rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-main);
            font-size: 0.75rem;
            text-decoration: none;
        }
        .link-btn-small:hover {
            border-color: var(--accent-color);
            color: var(--accent-color);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            padding: 1rem;
        }
        .job-results {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }
        .results-title {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .results-table-wrapper {
            max-height: 400px;
            overflow-y: auto;
            border-radius: 8px;
            border: 1px solid var(--border);
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .results-table th {
            background: rgba(22, 27, 34, 0.8);
            padding: 0.75rem;
            text-align: left;
            font-weight: 500;
            color: var(--text-muted);
            position: sticky;
            top: 0;
        }
        .results-table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border);
        }
        .badge-success {
            color: #2ea043;
        }
        .badge-error {
            color: #f85149;
        }
    </style>
@endsection