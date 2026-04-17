@extends('layouts.gestionnaire')

@section('gestionnaire-content')
<h1 class="gv-page-title">Paramètres</h1>

<div class="gv-card" style="max-width:640px;">
    <div style="font-family:var(--gv-serif);font-weight:700;font-size:1rem;margin-bottom:16px;">Préférences</div>

    <div class="gv-settings-box">
        <div class="gv-settings-row">
            <i class="fas fa-globe"></i>
            <div style="flex:1;">
                <div style="font-weight:600;">Langue</div>
            </div>
            <select style="padding:8px 12px;border-radius:8px;border:1px solid #ccc;background:#fff;">
                <option>Français</option>
            </select>
        </div>
        <div class="gv-settings-row">
            <i class="fas fa-bell"></i>
            <div style="flex:1;font-weight:600;">Notification</div>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" checked style="width:44px;height:24px;">
            </label>
        </div>
        <div class="gv-settings-row">
            <i class="fas fa-moon"></i>
            <div style="flex:1;font-weight:600;">Mode sombre</div>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" checked style="width:44px;height:24px;">
            </label>
        </div>
    </div>
</div>

<div class="gv-footer-app">
    SIGECAR v1.0.0<br>
    © 2026 SIGECAR. Tous droits réservés.
</div>
@endsection
