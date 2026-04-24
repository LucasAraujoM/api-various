<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AI Platform')</title>

    <!-- Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            /* Premium Dark Mode Palette */
            --bg-color: #0d1117;
            --text-main: #e6edf3;
            --text-muted: #848d97;
            --accent-color: #58a6ff;
            --accent-glow: rgba(88, 166, 255, 0.4);
            --surface: rgba(22, 27, 34, 0.6);
            --border: rgba(48, 54, 61, 0.8);
        }


        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            background-image:
                radial-gradient(ellipse at top left, rgba(28, 33, 40, 0.8), transparent 50%),
                radial-gradient(ellipse at bottom right, rgba(23, 27, 33, 0.8), transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Ambient Micro-Animation */
        .ambient-glow {
            position: fixed;
            top: -150px;
            right: -100px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--accent-glow) 0%, transparent 60%);
            filter: blur(60px);
            opacity: 0.3;
            z-index: -1;
            animation: pulse-glow 8s infinite alternate ease-in-out;
        }

        @keyframes pulse-glow {
            0% {
                transform: scale(1) translate(0, 0);
                opacity: 0.2;
            }

            100% {
                transform: scale(1.1) translate(-20px, 30px);
                opacity: 0.4;
            }
        }

        /* Navigation Header */
        header {
            padding: 1.25rem 2rem;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            background: rgba(13, 17, 23, 0.7);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .brand {
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: -0.5px;
            color: var(--text-main);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand span {
            color: var(--accent-color);
        }

        /* Main Content Layout */
        main {
            flex: 1;
            width: 100%;
            max-width: 1024px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
            animation: fade-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fade-up {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Premium Glassmorphism Card */
        .container {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2);
            transition: border-color 0.3s ease;
        }

        .container:hover {
            border-color: var(--accent-color);
        }

        /* Typography & Utilities */
        h1,
        h2,
        h3 {
            font-weight: 600;
            letter-spacing: -0.02em;
            margin-bottom: 1rem;
        }

        a {
            color: var(--accent-color);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        a:hover {
            color: #79c0ff;
        }

        footer {
            padding: 2rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.875rem;
            border-top: 1px solid var(--border);
        }

        /* Toasts */
        .toast-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            z-index: 1000;
        }

        .toast {
            background: rgba(22, 27, 34, 0.85);
            border-left: 4px solid var(--accent-color);
            color: var(--text-main);
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 280px;
            max-width: 400px;
            animation: slide-in 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            cursor: pointer;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .toast.toast-error {
            border-left-color: #f85149;
        }

        .toast.toast-success {
            border-left-color: #2ea043;
        }

        .toast p {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .toast.fade-out {
            opacity: 0;
            transform: translateX(100%);
        }

        @keyframes slide-in {
            from {
                opacity: 0;
                transform: translateX(100%);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>
    <div class="toast-container" id="toast-container">
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="toast toast-error" onclick="this.classList.add('fade-out'); setTimeout(() => this.remove(), 300)">
                    <p>{{ $error }}</p>
                </div>
            @endforeach
        @endif

        @if (session('success'))
            <div class="toast toast-success" onclick="this.classList.add('fade-out'); setTimeout(() => this.remove(), 300)">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="toast toast-error" onclick="this.classList.add('fade-out'); setTimeout(() => this.remove(), 300)">
                <p>{{ session('error') }}</p>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach((toast, index) => {
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        toast.classList.add('fade-out');
                        setTimeout(() => toast.remove(), 300);
                    }
                }, 5000 + (index * 500)); // Auto-dismiss after 5s, staggered
            });
        });

        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.onclick = function() {
                this.classList.add('fade-out');
                setTimeout(() => this.remove(), 300);
            };
            toast.innerHTML = `<p>${message}</p>`;
            container.appendChild(toast);
            
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    toast.classList.add('fade-out');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        };
    </script>
    <div class="ambient-glow"></div>

    <header style="display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('home') }}" class="brand">
            Admiral<span>APIs</span>
        </a>
        @if (Auth::check())
            <div style="display: flex; gap: 1rem;">
                <a href="{{ route('usage') }}" class="brand">API Usage</a>
                <a href="{{ route('logout') }}" class="brand">
                    Logout
                </a>
            </div>
        @else
            <div style="display: flex; gap: 1rem;">
                <a href="/login" class="brand">
                    Login
                </a>
                <a href="/register" class="brand">
                    Register
                </a>
            </div>
        @endif
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} MinimalAI Platform. Designed with precision.</p>
    </footer>
</body>

</html>