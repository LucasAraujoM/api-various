@extends('layout.app')

@section('title', 'API Documentation')

@section('content')
    <div class="docs-page">
        <div class="docs-header">
            <h1>API Documentation</h1>
            <p>Integrate email validation into your application</p>
        </div>

        @if(Auth::check())
        <div class="api-key-section">
            <h2>Your API Key</h2>
            @if(Auth::user()->api_key)
                <div class="api-key-display">
                    <code id="apiKeyCode">{{ Auth::user()->getAPIKey() }}</code>
                    <button onclick="copyApiKey()" class="btn-icon" title="Copy">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                    </button>
                    <button onclick="regenerateApiKey()" class="btn-icon" title="Regenerate">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 2v6h-6"></path>
                            <path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path>
                            <path d="M3 22v-6h6"></path>
                            <path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path>
                        </svg>
                    </button>
                </div>
                <p class="api-key-note">Keep this key secret. It provides full access to your account.</p>
            @else
                <button onclick="generateApiKey()" class="btn btn-primary">Generate API Key</button>
            @endif
        </div>
        @endif

        <div class="docs-section">
            <h2>Authentication</h2>
            <p>Include your API key in the request headers:</p>
            <pre class="code-block">Authorization: Bearer YOUR_API_KEY</pre>
        </div>

        <div class="docs-section">
            <h2>Single Email Validation</h2>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="url">{{ config('app.url') }}/api/validate-email</span>
            </div>
            <h3>Request Body</h3>
            <pre class="code-block">{
    "email": "user@example.com"
}</pre>
            <h3>Response</h3>
            <pre class="code-block">{
    "valid": true,
    "email": "user@example.com",
    "score": 0.95,
    "mx": true,
    "smtp": true,
    "free": false,
    "disposable": false,
    "catch_all": false,
    "syntax": true
}</pre>
        </div>

        <div class="docs-section">
            <h2>Bulk Email Validation</h2>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="url">{{ config('app.url') }}/api/bulk-validate-email</span>
            </div>
            <h3>Options</h3>
            <p>Upload a CSV/Excel file or send JSON array:</p>
            <pre class="code-block">// Option 1: JSON array
{
    "emails": ["a@test.com", "b@test.com"]
}

// Option 2: File upload (multipart/form-data)
// Field name: "file"
// Accepted formats: .csv, .xlsx, .xls</pre>
            <h3>Response</h3>
            <pre class="code-block">{
    "job_id": 1,
    "total": 2,
    "status": "pending",
    "message": "Bulk validation job queued successfully."
}</pre>
            <h3>Check Job Status</h3>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="url">{{ config('app.url') }}/api/bulk-jobs/{id}</span>
            </div>
        </div>

        <div class="docs-section">
            <h2>Response Codes</h2>
            <table class="docs-table">
                <thead>
                    <tr><th>Code</th><th>Description</th></tr>
                </thead>
                <tbody>
                    <tr><td>200</td><td>Success</td></tr>
                    <tr><td>202</td><td>Job queued (bulk)</td></tr>
                    <tr><td>400</td><td>Bad request / Invalid email</td></tr>
                    <tr><td>401</td><td>Unauthorized (invalid API key)</td></tr>
                    <tr><td>402</td><td>Insufficient credits</td></tr>
                    <tr><td>500</td><td>Server error</td></tr>
                </tbody>
            </table>
        </div>

        <div class="docs-section">
            <h2>Example Code</h2>
            <h3>cURL</h3>
            <pre class="code-block">curl -X POST "{{ config('app.url') }}/api/validate-email" \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -H "Content-Type: application/json" \\
  -d '{"email": "test@example.com"}'</pre>
            
            <h3>JavaScript</h3>
            <pre class="code-block">const response = await fetch('{{ config('app.url') }}/api/validate-email', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer YOUR_API_KEY',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({ email: 'test@example.com' })
});
const data = await response.json();</pre>
            
            <h3>Python</h3>
            <pre class="code-block">import requests

response = requests.post('{{ config('app.url') }}/api/validate-email', 
    headers={
        'Authorization': 'Bearer YOUR_API_KEY',
        'Content-Type': 'application/json'
    },
    json={'email': 'test@example.com'}
)
print(response.json())</pre>
        </div>
    </div>

    <script>
        function copyApiKey() {
            const key = document.getElementById('apiKeyCode').textContent.trim();
            navigator.clipboard.writeText(key).then(() => {
                window.showToast('API Key copied!', 'success');
            });
        }

        function regenerateApiKey() {
            if (!confirm('Are you sure? Your old key will stop working.')) return;
            generateApiKey();
        }

        function generateApiKey() {
            fetch('/api/generate-api-key', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ _token: '{{ csrf_token() }}' })
            })
            .then(res => res.json())
            .then(data => {
                window.showToast('API Key regenerated!', 'success');
                location.reload();
            })
            .catch(err => {
                window.showToast('Error generating key', 'error');
            });
        }
    </script>

    <style>
        .docs-page {
            max-width: 800px;
            margin: 0 auto;
        }
        .docs-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }
        .docs-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        .docs-header p {
            color: var(--text-muted);
        }
        .api-key-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .api-key-section h2 {
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        .api-key-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.5rem;
        }
        .api-key-display code {
            flex: 1;
            font-family: monospace;
            font-size: 0.85rem;
            color: var(--accent-color);
            word-break: break-all;
        }
        .btn-icon {
            background: none;
            border: none;
            color: var(--text-muted);
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .btn-icon:hover {
            background: rgba(88,166,255,0.1);
            color: var(--accent-color);
        }
        .api-key-note {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
        }
        .docs-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .docs-section h2 {
            font-size: 1.125rem;
            margin-bottom: 1rem;
        }
        .docs-section h3 {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin: 1.5rem 0 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .docs-section h3:first-of-type {
            margin-top: 0;
        }
        .docs-section > p {
            color: var(--text-muted);
            margin-bottom: 1rem;
        }
        .endpoint {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(0,0,0,0.3);
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }
        .method {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .method.post { background: #2ea043; color: white; }
        .method.get { background: #58a6ff; color: white; }
        .url {
            font-family: monospace;
            font-size: 0.875rem;
        }
        .code-block {
            background: rgba(0,0,0,0.4);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
            font-family: monospace;
            font-size: 0.8rem;
            overflow-x: auto;
            white-space: pre;
            line-height: 1.5;
            color: #e6edf3;
        }
        .docs-table {
            width: 100%;
            border-collapse: collapse;
        }
        .docs-table th, .docs-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .docs-table th {
            color: var(--text-muted);
            font-weight: 500;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: var(--accent-color);
            color: #0d1117;
        }
    </style>
@endsection