@extends('layouts.marketeur')

@section('marketeur-content')
<h1 class="gv-page-title" style="font-size:1.5rem;margin-bottom:8px;">Paramètres</h1>
<div style="font-weight:700;font-size:1rem;margin-bottom:14px;color:#111;">Préférences</div>

<div class="gv-settings-panel">
    <div class="gv-settings-row">
        <i class="fas fa-globe" style="font-size:1.25rem;width:28px;color:#111;"></i>
        <div style="flex:1;font-weight:600;">Langue</div>
        <div style="padding:8px 14px;border-radius:8px;background:#fff;border:1px solid #bbb;font-size:0.9rem;">Français</div>
    </div>
    <div class="gv-settings-row">
        <i class="fas fa-bell" style="font-size:1.25rem;width:28px;color:#111;"></i>
        <div style="flex:1;font-weight:600;">Notification</div>
        <div style="position:relative;width:48px;height:26px;border-radius:999px;background:#111;flex-shrink:0;">
            <span style="position:absolute;top:3px;left:25px;width:20px;height:20px;border-radius:50%;background:#fff;"></span>
        </div>
    </div>
    <div class="gv-settings-row">
        <i class="fas fa-moon" style="font-size:1.25rem;width:28px;color:#111;"></i>
        <div style="flex:1;font-weight:600;">Mode sombre</div>
        <div style="position:relative;width:48px;height:26px;border-radius:999px;background:#111;flex-shrink:0;">
            <span style="position:absolute;top:3px;left:25px;width:20px;height:20px;border-radius:50%;background:#fff;"></span>
        </div>
    </div>
</div>

<div class="gv-footer-app">
    SIGECAR v1.0.0<br>
    © 2026 SIGECAR. Tous droits réservés.
</div>
@endsection
