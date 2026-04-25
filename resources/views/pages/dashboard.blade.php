@extends('layout.app')

@section('title', 'Dashboard')

@section('content')
    <div class="dashboard">
        <div class="dashboard-header">
            <div>
                <h1>Dashboard</h1>
                <p>Overview of your email validation activity</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('usage') }}" class="btn btn-outline">View Details</a>
            </div>
        </div>

        <div class="stats-overview">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Credits Remaining</span>
                    <span class="stat-value">{{ number_format($stats['credits']['remaining']) }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Used Today</span>
                    <span class="stat-value">{{ $stats['credits']['used_today'] }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0 1.5 1.5M3.75 12.75h1.5m-1.5 0h16.5m0 0h1.5m-1.5 0v1.5m0-1.5v8.25" />
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Total Validations</span>
                    <span class="stat-value">{{ number_format($stats['validations']['total']) }}</span>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Valid Emails</span>
                    <span class="stat-value">{{ number_format($stats['emails']['valid']) }}</span>
                </div>
            </div>

            <div class="stat-card danger">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Invalid Emails</span>
                    <span class="stat-value">{{ number_format($stats['emails']['invalid']) }}</span>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="activity-card">
                <div class="card-header">
                    <h3>Recent Activity</h3>
                    <a href="{{ route('usage') }}">View All</a>
                </div>
                <div class="activity-list">
                    @forelse($recentActivity['validations'] as $item)
                        <div class="activity-item" onclick="showValidation({{ $item->id }})">
                            <span class="activity-email">{{ $item->email?->email ?? 'Unknown' }}</span>
                            <span class="activity-badge badge-{{ $item->result }}">{{ $item->result }}</span>
                        </div>
                    @empty
                        <p class="empty-text">No activity yet</p>
                    @endforelse
                </div>
            </div>

            <div class="jobs-card">
                <div class="card-header">
                    <h3>Bulk Jobs</h3>
                    <a href="{{ route('bulk-validation') }}">View All</a>
                </div>
                <div class="jobs-list">
                    @forelse($recentActivity['jobs'] as $job)
                        <div class="job-item">
                            <span class="job-id">#{{ $job->id }}</span>
                            <span class="job-status badge-{{ $job->status }}">{{ $job->status }}</span>
                            <span class="job-progress">{{ $job->processed }}/{{ $job->total }}</span>
                        </div>
                    @empty
                        <p class="empty-text">No jobs yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div id="validationModal" class="modal" style="display: none;">
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
                    let html = '<div class="detail-grid">';
                    html += `<div class="detail-item"><span>Email:</span> <strong>${data.validation.email || 'N/A'}</strong></div>`;
                    html += `<div class="detail-item"><span>Result:</span> <strong class="text-${data.validation.result}">${data.validation.result}</strong></div>`;
                    html += `<div class="detail-item"><span>Score:</span> <strong>${data.validation.score}</strong></div>`;
                    html += `<div class="detail-item"><span>Source:</span> <strong>${data.validation.source}</strong></div>`;
                    if (data.email_details) {
                        html += `<div class="detail-item"><span>MX:</span> <strong>${data.email_details.mx ? 'Yes' : 'No'}</strong></div>`;
                        html += `<div class="detail-item"><span>SMTP:</span> <strong>${data.email_details.smtp ? 'Yes' : 'No'}</strong></div>`;
                    }
                    html += '</div>';
                    content.innerHTML = html;
                });
        }

        function closeModal() {
            document.getElementById('validationModal').style.display = 'none';
        }

        document.getElementById('validationModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>

    <style>
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .dashboard-header h1 { font-size: 1.75rem; margin-bottom: 0.25rem; }
        .dashboard-header p { color: var(--text-muted); font-size: 0.875rem; }
        
        .btn-outline { padding: 0.5rem 1rem; border: 1px solid var(--border); border-radius: 8px; color: var(--text-main); text-decoration: none; }
        .btn-outline:hover { border-color: var(--accent-color); color: var(--accent-color); }

        .stats-overview { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; }
        .stat-card.primary { background: linear-gradient(135deg, rgba(88, 166, 255, 0.15) 0%, rgba(88, 166, 255, 0.05) 100%); border-color: rgba(88, 166, 255, 0.3); }
        .stat-card.success { background: linear-gradient(135deg, rgba(46, 160, 67, 0.15) 0%, rgba(46, 160, 67, 0.05) 100%); border-color: rgba(46, 160, 67, 0.3); }
        .stat-card.danger { background: linear-gradient(135deg, rgba(248, 81, 73, 0.15) 0%, rgba(248, 81, 73, 0.05) 100%); border-color: rgba(248, 81, 73, 0.3); }
        .stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(88, 166, 255, 0.1); color: var(--accent-color); }
        .stat-card.success .stat-icon { background: rgba(46, 160, 67, 0.1); color: #2ea043; }
        .stat-card.danger .stat-icon { background: rgba(248, 81, 73, 0.1); color: #f85149; }
        .stat-icon svg { width: 20px; height: 20px; }
        .stat-content { display: flex; flex-direction: column; }
        .stat-label { font-size: 0.75rem; color: var(--text-muted); }
        .stat-value { font-size: 1.5rem; font-weight: 700; }

        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .activity-card, .jobs-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .card-header h3 { font-size: 1rem; font-weight: 600; }
        .card-header a { font-size: 0.75rem; color: var(--accent-color); text-decoration: none; }

        .activity-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border-radius: 8px; cursor: pointer; }
        .activity-item:hover { background: rgba(0,0,0,0.2); }
        .activity-email { font-size: 0.875rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 200px; }
        .activity-badge { padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.7rem; font-weight: 500; text-transform: capitalize; }
        .badge-valid { background: rgba(46, 160, 67, 0.15); color: #2ea043; }
        .badge-invalid { background: rgba(248, 81, 73, 0.15); color: #f85149; }

        .job-item { display: flex; align-items: center; gap: 1rem; padding: 0.75rem; border-bottom: 1px solid var(--border); }
        .job-item:last-child { border-bottom: none; }
        .job-id { font-weight: 600; }
        .job-status { padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.7rem; font-weight: 500; text-transform: capitalize; }
        .badge-completed { background: rgba(46, 160, 67, 0.15); color: #2ea043; }
        .badge-processing, .badge-pending { background: rgba(88, 166, 255, 0.15); color: #58a6ff; }
        .badge-failed { background: rgba(248, 81, 73, 0.15); color: #f85149; }
        .job-progress { margin-left: auto; font-size: 0.875rem; color: var(--text-muted); }

        .empty-text { color: var(--text-muted); text-align: center; padding: 2rem; }

        .modal { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.85); display: flex; align-items: center; justify-content: center; z-index: 10000; }
        .modal-content { background: #161821; border: 1px solid var(--border); border-radius: 12px; width: 90%; max-width: 450px; max-height: 80vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); }
        .modal-header h3 { margin: 0; font-size: 1rem; }
        .modal-close { background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer; }
        .modal-body { padding: 1.25rem; }
        .modal-loading { text-align: center; color: var(--text-muted); padding: 2rem; }
        .detail-grid { display: grid; gap: 0.75rem; }
        .detail-item { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border); }
        .detail-item span { color: var(--text-muted); }
        .text-valid { color: #2ea043; }
        .text-invalid { color: #f85149; }
    </style>
@endsection