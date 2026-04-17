@extends('layouts.admin')

@section('admin-content')
<h1 class="admin-title">Ajouter un utilisateur</h1>

<div class="form-card">
    @if ($errors->any())
        <div class="login-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.store-user') }}">
        @csrf
        <div class="form-grid">
            <div>
                <label>NOM COMPLET</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ex: Oumar Diallo">
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="Entrer votre email professionnelle">
            </div>
            <div>
                <label>Rôle</label>
                <select name="role" required>
                    <option value="">Sélectionner</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    <option value="gestionnaire" @selected(old('role') === 'gestionnaire')>Gestionnaire</option>
                    <option value="marketeur" @selected(old('role') === 'marketeur')>Marketteur</option>
                </select>
            </div>
            <div>
                <label>Numéro de téléphone</label>
                <input type="tel" name="telephone" value="{{ old('telephone') }}" placeholder="EX : (+223) 79 00 978 99">
            </div>
            <div>
                <label>Mot de passe</label>
                <input type="password" name="password" required placeholder="Entrer mot de passe">
            </div>
            <div>
                <label>Confirmer mot de passe</label>
                <input type="password" name="password_confirmation" required placeholder="Entrer mot de passe">
            </div>
        </div>

        <input type="hidden" name="company_name" value="{{ old('company_name') }}">

        <div class="btn-row">
            <a href="{{ route('admin.users') }}" class="cancel-btn"><i class="far fa-times-circle"></i> Annuler</a>
            <button type="submit" class="save-btn">Ajouter</button>
        </div>
    </form>
</div>

<style>
    .admin-title { font-size: 35px !important; margin-bottom: 18px; }
    .form-card { max-width: 860px; width: min(100%, 860px); margin: 0 auto; background:#ececee; border-radius:8px; padding:20px; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px 26px; }
    .form-grid label { display:block; font-family:Georgia,serif; font-size:32px; margin-bottom:6px; }
    .form-grid input, .form-grid select {
        width:100%; border:0; background:#d8ddea; border-radius:8px; padding:10px 12px;
        font-size:33px; font-family:Georgia,serif; outline:none;
    }
    .btn-row { margin-top:170px; display:flex; justify-content:space-between; align-items:center; }
    .cancel-btn {
        background:#ff0606; color:#fff; text-decoration:none; border-radius:10px;
        font-size:40px; font-family:Georgia,serif; padding:8px 18px; display:inline-flex; gap:10px; align-items:center;
    }
    .save-btn {
        border:0; background:#2eb24d; color:#fff; border-radius:10px; cursor:pointer;
        font-size:40px; font-family:Georgia,serif; padding:8px 28px;
    }
    .login-error {
        margin-bottom: 12px; background: #ffe4e4; border: 1px solid #f0aaaa; color: #b11212;
        border-radius: 8px; padding: 8px 10px; font-size: 24px;
    }
</style>
@endsection
