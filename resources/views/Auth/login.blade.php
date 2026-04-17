@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
<div class="login-page">
    <div class="login-card">
        <img src="{{ asset('images/logo 1.png') }}" alt="SIGECAR" class="login-logo">
        <h1 class="login-brand-title">SIGECAR</h1>

        @if($errors->any())
            <div class="login-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label for="username">Nom d'utilisateur</label>
            <div class="login-input-wrap">
                <i class="fas fa-user"></i>
                <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="Entrer votre nom d'utilisateur" required autocomplete="username">
            </div>

            <label for="password">Mot de passe</label>
            <div class="login-input-wrap">
                <i class="fas fa-lock"></i>
                <input id="password" type="password" name="password" placeholder="Entrer votre mot de passe" required autocomplete="current-password">
            </div>

            <button type="submit" class="login-btn">Se connecter</button>
        </form>
    </div>
</div>
<style>
    .login-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f5f5f5;
        padding: 24px;
    }
    .login-card {
        width: 100%;
        max-width: 440px;
        text-align: center;
        font-family: 'Inter', Arial, sans-serif;
    }
    .login-logo {
        width: 100px;
        height: auto;
        margin: 0 auto 12px;
        display: block;
    }
    .login-brand-title {
        margin: 0 0 28px;
        font-size: 1.75rem;
        font-weight: 800;
        color: #0d2d4d;
        letter-spacing: 0.06em;
    }
    .login-card form {
        text-align: left;
    }
    .login-card label {
        display: block;
        font-size: 0.95rem;
        font-weight: 600;
        color: #111;
        margin: 20px 0 8px;
    }
    .login-card label:first-of-type {
        margin-top: 0;
    }
    .login-input-wrap {
        border: 1px solid #1a1a1a;
        border-radius: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: #fff;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }
    .login-input-wrap:focus-within {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
    }
    .login-input-wrap i {
        font-size: 1.15rem;
        width: 24px;
        text-align: center;
        color: #111;
    }
    .login-input-wrap input {
        border: 0;
        outline: 0;
        background: transparent;
        width: 100%;
        font-size: 1rem;
        color: #111;
    }
    .login-input-wrap input::placeholder {
        color: #9ca3af;
    }
    .login-btn {
        margin: 36px auto 0;
        display: block;
        min-width: 220px;
        border: 0;
        border-radius: 14px;
        background: #4caf50;
        color: #fff;
        font-size: 1rem;
        font-weight: 700;
        padding: 14px 24px;
        cursor: pointer;
        transition: background 0.15s ease, filter 0.15s ease;
    }
    .login-btn:hover {
        background: #43a047;
        filter: brightness(0.98);
    }
    .login-error {
        margin: 0 0 16px;
        background: #ffe4e4;
        border: 1px solid #f0aaaa;
        color: #b11212;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 0.9rem;
        text-align: left;
    }
</style>
@endsection
