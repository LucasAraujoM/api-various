@extends('layout.app')

@section('title', 'API Usage')

@section('content')
    <div class="container">
        <h1>API Usage</h1>
        <p>Here you can see your API calls usage and limits.</p>
        <div class="grid grid-cols-2 gap-4">
            <div class="card">
                <h2>Credits</h2>
                <p>{{ Auth::user()->credits }}</p>
                <p>Last calls</p>
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($calls as $call)
                            <tr>
                                <td>{{ $call->email->email }}</td>
                                <td>{{ $call->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $calls->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection