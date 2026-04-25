@extends('layout.app')

@section('title', 'API Usage')

@section('content')
    <div class="usage-page">
        <div class="page-header">
            <h1 class="page-title">API Usage</h1>
            <a href="{{ route('usage.export', request()->query()) }}" class="export-btn-small">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                CSV
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-label">Credits Remaining</p>
                <p class="stat-value text-blue-400">{{ $stats['credits_remaining'] }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Total Validations</p>
                <p class="stat-value">{{ $stats['total_validations'] }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Valid Emails</p>
                <p class="stat-value text-green-400">{{ $stats['valid_count'] }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Invalid Emails</p>
                <p class="stat-value text-red-400">{{ $stats['invalid_count'] }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Credits Used</p>
                <p class="stat-value text-yellow-400">{{ $stats['total_credits_used'] }}</p>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">Filters</h2>
            <form method="get" class="filter-form">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select name="status" class="filter-select">
                            <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>All</option>
                            <option value="valid" {{ $filters['status'] === 'valid' ? 'selected' : '' }}>Valid</option>
                            <option value="invalid" {{ $filters['status'] === 'invalid' ? 'selected' : '' }}>Invalid</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Source</label>
                        <select name="source" class="filter-select">
                            <option value="all" {{ $filters['source'] === 'all' ? 'selected' : '' }}>All</option>
                            <option value="api" {{ $filters['source'] === 'api' ? 'selected' : '' }}>API</option>
                            <option value="bulk" {{ $filters['source'] === 'bulk' ? 'selected' : '' }}>Bulk</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Date From</label>
                        <input type="date" name="date_from" class="filter-input" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Date To</label>
                        <input type="date" name="date_to" class="filter-input" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="filter-btn">Apply Filters</button>
                    <a href="{{ route('usage') }}" class="filter-clear">Clear</a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card card-full">
                <div class="card-header">
                    <h2 class="card-title">Recent Validations</h2>
                </div>
                <div class="table-wrapper">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Result</th>
                                <th>Score</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($calls as $call)
                                <tr class="clickable-row" onclick="showValidation({{ $call->id }})">
                                    <td class="cell-email">{{ $call->email?->email ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $call->result === 'valid' ? 'badge-success' : 'badge-error' }}">
                                            {{ $call->result }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($call->score, 2) }}</td>
                                    <td class="text-muted">{{ $call->created_at->format('m/d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No validations yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($calls->hasPages())
                    <div class="pagination-wrapper">
                        {{ $calls->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>

            <div class="card card-full">
                <div class="card-header">
                    <h2 class="card-title">Bulk Jobs</h2>
                </div>
                <div class="table-wrapper">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Total</th>
                                <th>Processed</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bulkJobs as $job)
                                <tr>
                                    <td>#{{ $job->id }}</td>
                                    <td>{{ $job->total }}</td>
                                    <td>{{ $job->processed }}/{{ $job->total }}</td>
                                    <td>
                                        <span class="badge badge-{{ $job->status === 'completed' ? 'success' : ($job->status === 'pending' || $job->status === 'processing' ? 'warning' : 'error') }}">
                                            {{ $job->status }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ $job->created_at->format('m/d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No bulk jobs yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($bulkJobs->hasPages())
                    <div class="pagination-wrapper">
                        {{ $bulkJobs->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">Most Validated Emails</h2>
            <div class="table-container">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Domain</th>
                            <th>Times Checked</th>
                            <th>Status</th>
                            <th>Last Checked</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentEmails as $email)
                            <tr>
                                <td class="truncate max-w-[200px]">{{ $email->email }}</td>
                                <td class="text-muted">{{ $email->domain }}</td>
                                <td>{{ $email->times_checked }}</td>
                                <td>
                                    <span class="badge {{ $email->status === 'valid' ? 'badge-success' : 'badge-error' }}">
                                        {{ $email->status }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $email->last_checked_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No emails validated yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="validationModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Validation Details</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalContent">
                <div class="modal-loading">Loading...</div>
            </div>
        </div>
    </div>

    <script>
        function showValidation(id) {
            const modal = document.getElementById('validationModal');
            const content = document.getElementById('modalContent');
            
            modal.style.display = 'flex';
            content.innerHTML = '<div class="modal-loading">Loading...</div>';
            
            fetch(`/usage/validation/${id}`)
                .then(res => res.json())
                .then(data => {
                    let html = `
                        <div class="detail-section">
                            <h4>Validation</h4>
                            <div class="detail-grid">
                                <div class="detail-item"><span>Email:</span> <strong>${data.validation.email || 'N/A'}</strong></div>
                                <div class="detail-item"><span>Result:</span> <span class="badge ${data.validation.result === 'valid' ? 'badge-success' : 'badge-error'}">${data.validation.result}</span></div>
                                <div class="detail-item"><span>Score:</span> <strong>${data.validation.score}</strong></div>
                                <div class="detail-item"><span>Source:</span> <strong>${data.validation.source}</strong></div>
                                <div class="detail-item"><span>Cost:</span> <strong>${data.validation.cost}</strong></div>
                                <div class="detail-item"><span>Date:</span> <strong>${new Date(data.validation.created_at).toLocaleString()}</strong></div>
                            </div>
                        </div>
                    `;
                    
                    if (data.email_details) {
                        html += `
                            <div class="detail-section">
                                <h4>Email Details</h4>
                                <div class="detail-grid">
                                    <div class="detail-item"><span>Domain:</span> <strong>${data.email_details.domain}</strong></div>
                                    <div class="detail-item"><span>MX:</span> <strong>${data.email_details.mx ? '✓' : '✗'}</strong></div>
                                    <div class="detail-item"><span>SMTP:</span> <strong>${data.email_details.smtp ? '✓' : '✗'}</strong></div>
                                    <div class="detail-item"><span>Disposable:</span> <strong>${data.email_details.disposable ? 'Yes' : 'No'}</strong></div>
                                    <div class="detail-item"><span>Catch All:</span> <strong>${data.email_details.catch_all ? 'Yes' : 'No'}</strong></div>
                                    <div class="detail-item"><span>Times Checked:</span> <strong>${data.email_details.times_checked}</strong></div>
                                </div>
                            </div>
                        `;
                    }
                    
                    if (data.signals && data.signals.length > 0) {
                        html += `<div class="detail-section"><h4>Signals</h4><div class="signals-list">`;
                        data.signals.forEach(signal => {
                            html += `<span class="signal-tag"><span class="signal-type">${signal.type}:</span> ${signal.value}</span>`;
                        });
                        html += `</div></div>`;
                    }
                    
                    content.innerHTML = html;
                })
                .catch(err => {
                    content.innerHTML = '<div class="text-red-400">Failed to load details</div>';
                });
        }

        function closeModal() {
            document.getElementById('validationModal').style.display = 'none';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });

        document.getElementById('validationModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>

    <style>
        .export-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .export-btn:hover {
            background: #79c0ff;
            color: white;
        }
        .usage-page {
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
        .export-btn-small {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--accent-color);
            color: #0d1117;
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .export-btn-small:hover {
            background: #79c0ff;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            padding: 1rem;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .stat-card:hover {
            border-color: var(--accent-color);
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 500;
        }
        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
        }
        
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .card-full {
            min-height: 400px;
        }
        .card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            background: rgba(0,0,0,0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
        }
        .table-wrapper {
            flex: 1;
            padding: 0.75rem;
            overflow-x: auto;
        }
        .table-wrapper table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-wrapper th {
            text-align: left;
            padding: 0.75rem;
            font-weight: 500;
            color: var(--text-muted);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: rgba(0,0,0,0.25);
            position: sticky;
            top: 0;
        }
        .table-wrapper td {
            padding: 0.75rem;
            font-size: 0.8rem;
            border-bottom: 1px solid var(--border);
        }
        .clickable-row:hover {
            background: rgba(88, 166, 255, 0.05);
        }
        .table-container {
            border-color: var(--accent-color);
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
        }
        .card-full {
            min-height: 400px;
        }
        .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
        }
.table-wrapper {
            padding: 1rem;
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #30363d var(--surface);
        }
        .table-wrapper::-webkit-scrollbar {
            height: 6px;
        }
        .table-wrapper::-webkit-scrollbar-track {
            background: var(--surface);
        }
        .table-wrapper::-webkit-scrollbar-thumb {
            background: #30363d;
            border-radius: 3px;
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
            padding: 1rem;
            background: rgba(0,0,0,0.2);
            border-radius: 8px;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            flex: 1;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .filter-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        .filter-input, .filter-select {
            background: rgba(22, 27, 34, 0.9);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.75rem;
            color: var(--text-main);
            font-size: 0.875rem;
            width: 100%;
            box-sizing: border-box;
        }
        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: var(--accent-color);
        }
        .filter-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .filter-btn {
            background: var(--accent-color);
            color: #0d1117;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .filter-btn:hover {
            background: #79c0ff;
        }
        .filter-clear {
            color: var(--text-muted);
            font-size: 0.875rem;
            padding: 0.75rem;
            text-decoration: none;
        }
        .filter-clear:hover {
            color: var(--text-main);
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 0.75rem 0.5rem;
            font-size: 0.875rem;
            border-bottom: 1px solid var(--border);
        }
        th {
            color: var(--text-muted);
            font-weight: 500;
        }
        .clickable-row {
            cursor: pointer;
            transition: background 0.2s;
        }
        .clickable-row:hover {
            background: rgba(88, 166, 255, 0.05);
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        .badge-success { background: rgba(46, 160, 67, 0.15); color: #2ea043; }
        .badge-error { background: rgba(248, 81, 73, 0.15); color: #f85149; }
        .badge-warning { background: rgba(210, 153, 34, 0.15); color: #d29922; }
        .text-muted { color: var(--text-muted); }
        .cell-email {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pagination-wrapper {
            padding: 1rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: center;
            background: rgba(0,0,0,0.2);
        }
        .pagination-wrapper .pagination {
            display: flex;
            gap: 0.25rem;
        }
        .pagination-wrapper .page-item {
            display: inline-block;
        }
        .pagination-wrapper .page-link {
            display: block;
            padding: 0.5rem 0.85rem;
            border-radius: 6px;
            font-size: 0.875rem;
            color: var(--text-main);
            background: rgba(88, 166, 255, 0.1);
            border: 1px solid var(--border);
            text-decoration: none;
            transition: all 0.2s;
        }
        .pagination-wrapper .page-link:hover {
            background: var(--accent-color);
            color: #0d1117;
        }
        .pagination-wrapper .page-item.active .page-link {
            background: var(--accent-color);
            color: #0d1117;
            font-weight: 600;
        }
        .pagination-wrapper .page-item.disabled .page-link {
            color: var(--text-muted);
            opacity: 0.5;
            pointer-events: none;
        }

.modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
        }
.modal-content {
            background: #161821;
            border: 1px solid var(--border);
            border-radius: 12px;
            width: 95%;
            max-width: 600px;
            max-height: 85vh;
            overflow-y: auto;
            margin: auto;
            scrollbar-width: thin;
            scrollbar-color: #30363d #161821;
        }
        .modal-content::-webkit-scrollbar {
            width: 6px;
        }
        .modal-content::-webkit-scrollbar-track {
            background: #161821;
        }
        .modal-content::-webkit-scrollbar-thumb {
            background: #30363d;
            border-radius: 3px;
        }
        .modal-content::-webkit-scrollbar-thumb:hover {
            background: #484f58;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            background: #161821;
            z-index: 10;
        }
        .modal-header h3 { margin: 0; font-size: 1.125rem; }
        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.25rem;
            line-height: 1;
        }
        .modal-body {
            padding: 1.5rem;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            background: #161821;
            z-index: 10;
        }
        .modal-header h3 { margin: 0; font-size: 1.125rem; }
        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.25rem;
            line-height: 1;
        }
        .modal-close:hover {
            color: var(--text-main);
        }
        .modal-body {
            padding: 1.5rem;
        }
        .modal-loading {
            text-align: center;
            color: var(--text-muted);
            padding: 2rem;
        }

        .detail-section { margin-bottom: 1.5rem; }
        .detail-section:last-child { margin-bottom: 0; }
        .detail-section h4 {
            font-size: 0.8rem;
            color: var(--accent-color);
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.5rem;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding: 0.5rem;
            background: rgba(0,0,0,0.2);
            border-radius: 6px;
        }
        .detail-item span { 
            color: var(--text-muted); 
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .detail-item strong { 
            font-weight: 600; 
            font-size: 0.9rem;
            word-break: break-all;
        }

        .signals-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .signal-tag {
            background: rgba(88, 166, 255, 0.1);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .signal-type {
            color: var(--accent-color);
            font-weight: 500;
        }
    </style>
@endsection