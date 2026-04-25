@extends('layout.app')

@section('title', 'Email Validation API')

@section('content')
    <div class="landing">
        <section class="hero">
            <div class="hero-content">
                <h1>Email Validation API</h1>
                <p>Validate emails in real-time with syntax, MX, and SMTP verification. Keep your lists clean and improve delivery.</p>
                @if(Auth::check())
                    <div class="hero-actions">
                        <a href="{{ route('usage') }}" class="btn btn-primary">Dashboard</a>
                        <a href="{{ route('docs') }}" class="btn btn-secondary">Documentation</a>
                    </div>
                @else
                    <div class="hero-actions">
                        <a href="/login" class="btn btn-primary">Get Started</a>
                        <a href="/register" class="btn btn-secondary">Sign Up Free</a>
                    </div>
                @endif
            </div>
        </section>

        <section class="features">
            <div class="feature">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                </div>
                <h3>Syntax Check</h3>
                <p>Validate email format according to RFC 5322 standards.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <h3>MX Verification</h3>
                <p>Check if domain has valid mail exchange records.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.532-3.91M12 21c-1.658 0-3.167-.68-4.266-1.785M12 21V10.875M12 21a9.004 9.004 0 0 1-8.532-3.91M12 21c1.658 0 3.167.68 4.266 1.785M12 21v-9.125" />
                    </svg>
                </div>
                <h3>SMTP Verification</h3>
                <p>Verify mailbox exists with direct SMTP validation.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25a2.25 2.25 0 0 0-2.25 2.25v17a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25V5.25a2.25 2.25 0 0 0-2.25-2.25H9.568M4.5 9h3.75a.75.75 0 0 1 0 1.5H4.5v2.25h3.75a.75.75 0 0 1 0 1.5H4.5v2.25h3.75a.75.75 0 0 1 0 1.5H4.5V19.5a2.25 2.25 0 0 1 2.25-2.25h2.568" />
                    </svg>
                </div>
                <h3>Bulk Processing</h3>
                <p>Process thousands of emails with async queue jobs.</p>
            </div>
        </section>

        <section class="demo">
            <h2>Try It Now</h2>
            <p>Test the API with these sample emails</p>
            <div class="demo-form">
                <textarea id="demoEmails" class="demo-input" placeholder="Enter emails (one per line)">test@gmail.com
user@yahoo.com
invalid-email-format
nonexistent@domain12345.xyz
admin@protonmail.com</textarea>
                <button onclick="testAPI()" class="btn btn-primary">Validate</button>
            </div>
            <div id="demoResults" class="demo-results"></div>
        </section>

        <section class="docs-link">
            <h2>Ready to Integrate?</h2>
            <p>Check our API documentation for full endpoint details</p>
            <a href="{{ route('docs') }}" class="btn btn-secondary">API Documentation</a>
        </section>
    </div>

    <script>
        function testAPI() {
            const input = document.getElementById('demoEmails').value;
            const emails = input.split('\n').map(e => e.trim()).filter(e => e);
            const resultsDiv = document.getElementById('demoResults');
            
            if (emails.length === 0) {
                resultsDiv.innerHTML = '<p class="text-muted">Please enter some emails to test</p>';
                return;
            }

            resultsDiv.innerHTML = '<p class="loading">Validating...</p>';

            // For demo, we'll do client-side validation
            const freeProviders = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'aol.com', 'icloud.com', 'mail.com', 'zoho.com', 'protonmail.com', 'proton.me', 'yandex.com'];
            
            let resultsHtml = '<table class="results-table"><thead><tr><th>Email</th><th>Result</th><th>Details</th></tr></thead><tbody>';
            
            emails.forEach(email => {
                const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
                const domain = email.split('@')[1]?.toLowerCase() || '';
                const isFree = freeProviders.includes(domain);
                const isInvalidFormat = !isValid;
                
                const result = isInvalidFormat ? 'invalid' : (isFree ? 'valid' : 'unknown');
                const details = [];
                
                if (isInvalidFormat) details.push('Invalid syntax');
                if (isValid && isFree) details.push('Free provider');
                if (!isValid && domain) details.push('Invalid format');
                if (!domain) details.push('Missing domain');
                
                const badgeClass = result === 'valid' ? 'success' : (result === 'invalid' ? 'error' : 'warning');
                
                resultsHtml += `<tr>
                    <td>${email}</td>
                    <td><span class="badge badge-${badgeClass}">${result}</span></td>
                    <td>${details.join(', ') || 'MX check needed'}</td>
                </tr>`;
            });
            
            resultsHtml += '</tbody></table>';
            resultsDiv.innerHTML = resultsHtml;
        }
    </script>

    <style>
        .landing {
            width: 100%;
        }
        .hero {
            text-align: center;
            padding: 3rem 0;
        }
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #58a6ff 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero p {
            color: var(--text-muted);
            font-size: 1.125rem;
            max-width: 600px;
            margin: 0 auto 2rem;
        }
        .hero-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: var(--accent-color);
            color: #0d1117;
        }
        .btn-primary:hover {
            background: #79c0ff;
        }
        .btn-secondary {
            background: rgba(88, 166, 255, 0.1);
            border: 1px solid var(--border);
            color: var(--text-main);
        }
        .btn-secondary:hover {
            background: rgba(88, 166, 255, 0.2);
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            padding: 2rem 0;
        }
        .feature {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }
        .feature-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 1rem;
            color: var(--accent-color);
        }
        .feature-icon svg {
            width: 100%;
            height: 100%;
        }
        .feature h3 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        .feature p {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        .demo {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            margin: 2rem 0;
        }
        .demo h2 {
            margin-bottom: 0.5rem;
        }
        .demo > p {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }
        .demo-form {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .demo-input {
            width: 100%;
            max-width: 400px;
            height: 150px;
            background: rgba(22, 27, 34, 0.8);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
            color: var(--text-main);
            font-family: monospace;
            font-size: 0.875rem;
            resize: none;
        }
        .demo-input:focus {
            outline: none;
            border-color: var(--accent-color);
        }
        .demo-results {
            text-align: left;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
        }
        .results-table th, .results-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .results-table th {
            color: var(--text-muted);
            font-weight: 500;
        }
        .docs-link {
            text-align: center;
            padding: 3rem 0;
        }
        .docs-link h2 {
            margin-bottom: 0.5rem;
        }
        .docs-link p {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }
    </style>
@endsection