@extends('layout.app')

@section('title', 'Home')

@section('content')
    <div class="container">
    @if (Auth::user())
        <h1>API Key</h1>
        @if (Auth::user()->api_key)
            <div style="
                display: flex;
                align-items: center;
                gap: 0.75rem;
                background: rgba(22, 27, 34, 0.8);
                border: 1px solid rgba(48, 54, 61, 0.8);
                border-radius: 10px;
                padding: 0.75rem 1rem;
                margin-top: 0.5rem;
            ">
                {{-- API Key Display --}}
                <code style="
                    flex: 1;
                    font-family: 'SF Mono', 'Fira Code', 'Cascadia Code', monospace;
                    font-size: 0.9rem;
                    color: #58a6ff;
                    letter-spacing: 0.5px;
                    word-break: break-all;
                    user-select: all;
                ">{{ Auth::user()->getAPIKey() }}</code>

                {{-- Copy Button --}}
                <button onclick="copyAPIKey()" title="Copy API Key" style="
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 2rem;
                    height: 2rem;
                    border: none;
                    border-radius: 6px;
                    background: transparent;
                    color: #848d97;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    flex-shrink: 0;
                " onmouseenter="this.style.background='rgba(88,166,255,0.12)'; this.style.color='#58a6ff'"
                    onmouseleave="this.style.background='transparent'; this.style.color='#848d97'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        style="width: 1.15rem; height: 1.15rem;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                    </svg>
                </button>

                {{-- Regenerate Button --}}
                <button onclick="generateAPIKey()" title="Regenerar API Key" style="
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 2rem;
                    height: 2rem;
                    border: none;
                    border-radius: 6px;
                    background: transparent;
                    color: #848d97;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    flex-shrink: 0;
                " onmouseenter="this.style.background='rgba(88,166,255,0.12)'; this.style.color='#58a6ff'"
                    onmouseleave="this.style.background='transparent'; this.style.color='#848d97'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        style="width: 1.15rem; height: 1.15rem;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
            </div>

        @else
            <button onclick="generateAPIKey()" style="
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.65rem 1.5rem;
                font-family: 'Inter', sans-serif;
                font-size: 0.9rem;
                font-weight: 500;
                color: #fff;
                background: linear-gradient(135deg, #58a6ff 0%, #3b82f6 100%);
                border: 1px solid rgba(88, 166, 255, 0.4);
                border-radius: 8px;
                cursor: pointer;
                box-shadow: 0 0 12px rgba(88, 166, 255, 0.15), 0 2px 8px rgba(0, 0, 0, 0.3);
                transition: all 0.25s ease;
                margin-top: 1.25rem;
            "
                onmouseenter="this.style.boxShadow='0 0 20px rgba(88,166,255,0.35), 0 4px 16px rgba(0,0,0,0.4)'; this.style.transform='translateY(-1px)'"
                onmouseleave="this.style.boxShadow='0 0 12px rgba(88,166,255,0.15), 0 2px 8px rgba(0,0,0,0.3)'; this.style.transform='translateY(0)'"
                onmousedown="this.style.transform='scale(0.97)'" onmouseup="this.style.transform='translateY(-1px)'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                    style="width: 1.1rem; height: 1.1rem;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                </svg>
                Generar API Key
            </button>
        @endif
        <script>
            function copyAPIKey() {
                navigator.clipboard.writeText('{{ Auth::user()->getAPIKey() }}');
                window.showToast('API Key copied to clipboard!', 'success');
            }

            function generateAPIKey() {
                fetch('/api/generate-api-key', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        _token: '{{ csrf_token() }}',
                    }),
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        </script>
    @endif
    </div>
    <div class="container mt-8">
        <h2 class="">Documentación</h2>
        <p class="">Lorem ipsum dolor sit amet consectetur adipisicing elit. Amet, voluptates.</p>
        @include('pages.docs.email')
    </div>
@endsection