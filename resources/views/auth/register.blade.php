@extends('layout.app')

@section('title', 'Register')

@section('content')
    <div class="container" style="max-width: 450px; margin: 2rem auto; text-align: center;">
        <h1 style="margin-bottom: 2rem; color: var(--text-main);">Register</h1>
        <form action="{{ route('auth.register') }}" method="POST"
            style="display: flex; flex-direction: column; gap: 1.5rem; text-align: left;">
            @csrf
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label for="name" style="font-weight: 500; font-size: 0.9rem; color: var(--text-muted);">Name</label>
                <input type="text" name="name" id="name" required
                    style="padding: 0.8rem 1rem; border-radius: 8px; border: 1px solid var(--border); background: rgba(0,0,0,0.2); color: var(--text-main); font-size: 1rem; outline: none; transition: all 0.3s ease;"
                    onfocus="this.style.borderColor='var(--accent-color)'; this.style.boxShadow='0 0 0 3px var(--accent-glow)'"
                    onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
            </div>
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label for="email" style="font-weight: 500; font-size: 0.9rem; color: var(--text-muted);">Email</label>
                <input type="email" name="email" id="email" required
                    style="padding: 0.8rem 1rem; border-radius: 8px; border: 1px solid var(--border); background: rgba(0,0,0,0.2); color: var(--text-main); font-size: 1rem; outline: none; transition: all 0.3s ease;"
                    onfocus="this.style.borderColor='var(--accent-color)'; this.style.boxShadow='0 0 0 3px var(--accent-glow)'"
                    onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
            </div>
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label for="password"
                    style="font-weight: 500; font-size: 0.9rem; color: var(--text-muted);">Password</label>
                <input type="password" name="password" id="password" required minlength="8"
                    style="padding: 0.8rem 1rem; border-radius: 8px; border: 1px solid var(--border); background: rgba(0,0,0,0.2); color: var(--text-main); font-size: 1rem; outline: none; transition: all 0.3s ease;"
                    onfocus="this.style.borderColor='var(--accent-color)'; this.style.boxShadow='0 0 0 3px var(--accent-glow)'"
                    onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
            </div>
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label for="password_confirmation"
                    style="font-weight: 500; font-size: 0.9rem; color: var(--text-muted);">Password Confirmation</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                    style="padding: 0.8rem 1rem; border-radius: 8px; border: 1px solid var(--border); background: rgba(0,0,0,0.2); color: var(--text-main); font-size: 1rem; outline: none; transition: all 0.3s ease;"
                    onfocus="this.style.borderColor='var(--accent-color)'; this.style.boxShadow='0 0 0 3px var(--accent-glow)'"
                    onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
            </div>
            <button type="submit"
                style="margin-top: 1rem; padding: 0.85rem; border-radius: 8px; border: none; background: var(--accent-color); color: #0d1117; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.2s ease;"
                onmouseover="this.style.background='#79c0ff'; this.style.transform='translateY(-2px)'"
                onmouseout="this.style.background='var(--accent-color)'; this.style.transform='translateY(0)'"
                onmousedown="this.style.transform='scale(0.98)'">Register</button>
        </form>
    </div>
@endsection