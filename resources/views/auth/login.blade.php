@extends('layout.app')

@section('title', 'Login')

@section('content')
    <div class="container" style="max-width: 450px; margin: 2rem auto; text-align: center;">
        <h1 style="margin-bottom: 2rem; color: var(--text-main);">Login</h1>
        <form action="{{ route('auth.login') }}" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem; text-align: left;">
            @csrf
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label for="email" style="font-weight: 500; font-size: 0.9rem; color: var(--text-muted);">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required style="padding: 0.8rem 1rem; border-radius: 8px; border: 1px solid var(--border); background: rgba(0,0,0,0.2); color: var(--text-main); font-size: 1rem; outline: none; transition: all 0.3s ease;" onfocus="this.style.borderColor='var(--accent-color)'; this.style.boxShadow='0 0 0 3px var(--accent-glow)'" onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
            </div>
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label for="password" style="font-weight: 500; font-size: 0.9rem; color: var(--text-muted);">Password</label>
                <input type="password" name="password" id="password" required style="padding: 0.8rem 1rem; border-radius: 8px; border: 1px solid var(--border); background: rgba(0,0,0,0.2); color: var(--text-main); font-size: 1rem; outline: none; transition: all 0.3s ease;" onfocus="this.style.borderColor='var(--accent-color)'; this.style.boxShadow='0 0 0 3px var(--accent-glow)'" onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--accent-color);">
                <label for="remember" style="font-size: 0.875rem; color: var(--text-muted);">Remember me</label>
            </div>
            <button type="submit" style="margin-top: 1rem; padding: 0.85rem; border-radius: 8px; border: none; background: var(--accent-color); color: #0d1117; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#79c0ff'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='var(--accent-color)'; this.style.transform='translateY(0)'" onmousedown="this.style.transform='scale(0.98)'">Login</button>
        </form>
    </div>
@endsection