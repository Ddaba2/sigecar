@extends('layouts.app')

@section('title', 'Gestionnaire - ' . ($title ?? 'SIGECAR'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --gv-navy: #001b33;
        --gv-navy-deep: #001c30;
        --gv-blue: #007bff;
        --gv-teal: #20b2aa;
        --gv-bg: #f4f7f9;
        --gv-topbar: #e8eaed;
        --gv-red: #dc3545;
        --gv-red-dark: #b30000;
        --gv-input: #e9ecef;
        --gv-serif: 'Merriweather', Georgia, serif;
        --gv-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
    }

    .gv-shell {
        display: flex;
        min-height: 100vh;
        background: var(--gv-bg);
        font-family: var(--gv-sans);
    }

    .gv-sidebar {
        width: 280px;
        min-height: 100vh;
        background: linear-gradient(180deg, var(--gv-navy-deep) 0%, var(--gv-navy) 100%);
        color: #fff;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        padding: 20px 14px 20px;
        position: sticky;
        top: 0;
        align-self: flex-start;
        height: 100vh;
    }

    .gv-sidebar-brand {
        text-align: center;
        padding: 8px 0 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    }

    .gv-sidebar-brand-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 12px;
        border: 2px solid rgba(255, 255, 255, 0.85);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: #fff;
    }

    .gv-sidebar-brand h2 {
        margin: 0;
        font-family: var(--gv-serif);
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .gv-nav {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 20px 0;
        overflow-y: auto;
    }

    .gv-nav a {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        border-radius: 10px;
        color: #fff;
        text-decoration: none;
        font-family: var(--gv-serif);
        font-size: 1rem;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .gv-nav a i {
        width: 22px;
        text-align: center;
        font-size: 1rem;
        opacity: 0.95;
    }

    .gv-nav a:hover {
        background: rgba(255, 255, 255, 0.08);
    }

    .gv-nav a.gv-nav-active {
        background: var(--gv-blue);
        box-shadow: 0 4px 14px rgba(0, 123, 255, 0.35);
    }

    .gv-sidebar-foot {
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        padding-top: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .gv-settings-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 10px;
        background: var(--gv-blue);
        color: #fff !important;
        text-decoration: none;
        font-family: var(--gv-serif);
        font-size: 1rem;
        font-weight: 600;
    }

    .gv-settings-link:not(.gv-nav-active) {
        background: transparent;
        color: #fff !important;
    }

    .gv-settings-link.gv-nav-active {
        background: var(--gv-blue);
    }

    .gv-logout-form button {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 12px 16px;
        border: 0;
        border-radius: 10px;
        background: var(--gv-red-dark);
        color: #fff;
        font-family: var(--gv-serif);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: filter 0.15s ease;
    }

    .gv-logout-form button:hover {
        filter: brightness(1.08);
    }

    .gv-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .gv-topbar {
        height: 72px;
        background: var(--gv-topbar);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
        flex-shrink: 0;
        border-bottom: 1px solid #d8dce0;
    }

    .gv-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.75rem;
        font-weight: 800;
        color: #0d2d4d;
        letter-spacing: 0.04em;
    }

    .gv-brand img {
        height: 52px;
        width: auto;
    }

    .gv-topbar-actions {
        display: flex;
        align-items: center;
        gap: 22px;
    }

    .gv-topbar-actions .gv-bell {
        font-size: 1.35rem;
        color: var(--gv-blue);
        cursor: pointer;
    }

    .gv-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #b0b8c0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #555;
        font-size: 1.1rem;
    }

    .gv-content {
        flex: 1;
        padding: 28px 32px 40px;
        overflow-x: auto;
    }

    .gv-breadcrumb {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        color: var(--gv-teal);
        text-transform: uppercase;
        margin: 0 0 8px;
    }

    .gv-page-title {
        font-family: var(--gv-serif);
        font-size: 1.85rem;
        font-weight: 700;
        color: #111;
        margin: 0 0 8px;
    }

    .gv-page-sub {
        color: #333;
        font-size: 0.95rem;
        margin: 0 0 24px;
        max-width: 720px;
        line-height: 1.5;
    }

    .gv-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        margin-bottom: 28px;
    }

    @media (max-width: 1200px) {
        .gv-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 640px) {
        .gv-kpi-grid { grid-template-columns: 1fr; }
        .gv-sidebar { width: 240px; }
    }

    .gv-kpi {
        background: #fff;
        border-radius: 12px;
        padding: 20px 22px;
        box-shadow: 0 2px 12px rgba(0, 27, 51, 0.06);
    }

    .gv-kpi.gv-kpi-dark {
        background: var(--gv-navy);
        color: #fff;
    }

    .gv-kpi-label {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .gv-kpi-dark .gv-kpi-label {
        color: rgba(255, 255, 255, 0.75);
    }

    .gv-kpi-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111;
    }

    .gv-kpi-dark .gv-kpi-value {
        color: #fff;
    }

    .gv-kpi-meta {
        margin-top: 10px;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6b7280;
    }

    .gv-kpi-meta.teal { color: #0d9488; }
    .gv-kpi-meta.red { color: var(--gv-red); }

    .gv-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin: 28px 0 18px;
    }

    .gv-section-title {
        font-size: 1.15rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #111;
    }

    .gv-btn-blue {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: var(--gv-blue);
        color: #fff !important;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        border: 0;
        cursor: pointer;
    }

    .gv-btn-blue:hover { filter: brightness(1.05); }

    .gv-tank-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }

    .gv-tank-card {
        background: #fff;
        border-radius: 12px;
        padding: 18px;
        box-shadow: 0 2px 12px rgba(0, 27, 51, 0.06);
    }

    .gv-tank-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .gv-tank-id { font-weight: 700; font-size: 1rem; }
    .gv-tank-prod { font-size: 0.72rem; color: #6b7280; text-transform: uppercase; margin-top: 4px; }

    .gv-tank-body {
        display: flex;
        gap: 16px;
        align-items: stretch;
    }

    .gv-gauge {
        width: 36px;
        border-radius: 18px;
        background: #e5e7eb;
        position: relative;
        overflow: hidden;
        min-height: 120px;
    }

    .gv-gauge-fill {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        border-radius: 0 0 18px 18px;
        transition: height 0.3s ease;
    }

    .gv-gauge.teal .gv-gauge-fill { background: linear-gradient(180deg, #2dd4bf, #0d9488); }
    .gv-gauge.amber .gv-gauge-fill { background: linear-gradient(180deg, #fcd34d, #d97706); }
    .gv-gauge.red .gv-gauge-fill { background: linear-gradient(180deg, #fca5a5, #dc2626); }

    .gv-tank-pct {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1;
        align-self: center;
    }

    .gv-tank-stats {
        flex: 1;
        font-size: 0.78rem;
        color: #4b5563;
    }

    .gv-tank-stats div { margin-bottom: 6px; }
    .gv-tank-stats strong { color: #111; }

    .gv-pill {
        display: inline-block;
        margin-top: 12px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    .gv-pill.ok { background: rgba(13, 148, 136, 0.15); color: #0f766e; }
    .gv-pill.warn { background: rgba(217, 119, 6, 0.15); color: #b45309; }
    .gv-pill.danger { background: rgba(220, 38, 38, 0.15); color: #b91c1c; }

    .gv-table-wrap {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 27, 51, 0.06);
    }

    .gv-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .gv-table th {
        background: #e8eef5;
        color: #374151;
        font-weight: 700;
        text-align: left;
        padding: 14px 16px;
        border-bottom: 1px solid #d1d5db;
    }

    .gv-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .gv-table tbody tr:hover { background: #fafafa; }

    .gv-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        border: 0;
        cursor: default;
    }

    .gv-badge.ok { background: #28a745; color: #fff; }
    .gv-badge.pending { background: var(--gv-red); color: #fff; }

    .gv-card {
        background: #fff;
        border-radius: 12px;
        padding: 22px 24px;
        box-shadow: 0 2px 12px rgba(0, 27, 51, 0.06);
        margin-bottom: 20px;
    }

    .gv-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 0.95rem;
        margin-bottom: 18px;
        color: var(--gv-blue);
    }

    .gv-card-header i { font-size: 1.1rem; }

    .gv-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px 22px;
    }

    @media (max-width: 900px) {
        .gv-form-grid { grid-template-columns: 1fr; }
    }

    .gv-field label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: #6b7280;
        margin-bottom: 6px;
        text-transform: uppercase;
    }

    .gv-field input,
    .gv-field select,
    .gv-field textarea {
        width: 100%;
        padding: 12px 14px;
        border: 0;
        border-radius: 8px;
        background: var(--gv-input);
        font-size: 0.95rem;
        font-family: var(--gv-sans);
    }

    .gv-field input:focus,
    .gv-field select:focus {
        outline: 2px solid rgba(0, 123, 255, 0.35);
        outline-offset: 0;
    }

    .gv-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 14px;
        margin-top: 28px;
        flex-wrap: wrap;
    }

    .gv-btn-red {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 22px;
        background: var(--gv-red);
        color: #fff !important;
        border-radius: 8px;
        font-weight: 600;
        border: 0;
        cursor: pointer;
        text-decoration: none;
    }

    .gv-card-navy {
        background: var(--gv-navy);
        color: #fff;
        border-radius: 12px;
        padding: 22px 24px;
        margin-bottom: 20px;
    }

    .gv-card-navy .gv-card-header { color: #fff; margin-bottom: 16px; }
    .gv-card-navy .gv-card-header span { border-bottom: 1px solid rgba(255,255,255,0.4); padding-bottom: 6px; }

    .gv-card-navy label { color: var(--gv-teal); }

    .gv-card-navy input {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
    }

    .gv-card-navy input::placeholder { color: rgba(255, 255, 255, 0.45); }

    .gv-vol-result {
        font-family: var(--gv-serif);
        font-size: 1.65rem;
        font-weight: 700;
        margin-top: 12px;
        letter-spacing: 0.02em;
    }

    .gv-alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 18px;
        font-size: 0.9rem;
    }

    .gv-alert-success { background: #d1fae5; color: #065f46; }
    .gv-alert-error { background: #fee2e2; color: #991b1b; }

    .gv-settings-box {
        background: #e8eaed;
        border-radius: 12px;
        padding: 8px 0;
        max-width: 560px;
    }

    .gv-settings-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-bottom: 1px solid #d1d5db;
    }

    .gv-settings-row:last-child { border-bottom: 0; }

    .gv-settings-row i { font-size: 1.25rem; width: 28px; color: #111; }

    .gv-footer-app {
        text-align: center;
        margin-top: 40px;
        font-size: 0.8rem;
        color: #6b7280;
    }
</style>
@endpush

@section('content')
<div class="gv-shell">
    <aside class="gv-sidebar">
        <div class="gv-sidebar-brand">
            <div class="gv-sidebar-brand-icon" aria-hidden="true">
                <i class="fas fa-user-shield"></i>
            </div>
            <h2>Gestionnaire</h2>
        </div>

        <nav class="gv-nav">
            <a href="{{ route('gestionnaire.chargement.create') }}" class="@if(request()->routeIs('gestionnaire.chargement.create')) gv-nav-active @endif">
                <i class="fas fa-truck"></i>
                Chargement
            </a>
            <a href="{{ route('gestionnaire.depotage.create') }}" class="@if(request()->routeIs('gestionnaire.depotage.create')) gv-nav-active @endif">
                <i class="fas fa-angles-down"></i>
                Dépotage
            </a>
            <a href="{{ route('gestionnaire.stocks') }}" class="@if(request()->routeIs('gestionnaire.stocks') || request()->routeIs('gestionnaire.stocks.tous') || request()->routeIs('gestionnaire.dashboard')) gv-nav-active @endif">
                <i class="fas fa-gas-pump"></i>
                Stock &amp; Douane
            </a>
            <a href="{{ route('gestionnaire.cession.create') }}" class="@if(request()->routeIs('gestionnaire.cession.create')) gv-nav-active @endif">
                <i class="fas fa-right-left"></i>
                Cession
            </a>
            <a href="{{ route('gestionnaire.rapports') }}" class="@if(request()->routeIs('gestionnaire.rapports')) gv-nav-active @endif">
                <i class="fas fa-chart-column"></i>
                Rapport
            </a>
            <a href="{{ route('gestionnaire.operations') }}" class="@if(request()->routeIs('gestionnaire.operations')) gv-nav-active @endif">
                <i class="fas fa-clock-rotate-left"></i>
                Opérations
            </a>
        </nav>

        <div class="gv-sidebar-foot">
            <a href="{{ route('gestionnaire.settings') }}" class="gv-settings-link @if(request()->routeIs('gestionnaire.settings')) gv-nav-active @endif">
                <i class="fas fa-gear"></i>
                Paramètres
            </a>
            <form method="POST" action="{{ route('logout') }}" class="gv-logout-form">
                @csrf
                <button type="submit">
                    <i class="fas fa-right-from-bracket"></i>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    <div class="gv-main">
        <header class="gv-topbar">
            <div class="gv-brand">
                <img src="{{ asset('images/logo.png') }}" alt="">
            </div>
            <div class="gv-topbar-actions">
                <span class="gv-bell" title="Notifications"><i class="fas fa-bell"></i></span>
                <div class="gv-avatar" title="{{ Auth::user()->name ?? '' }}"><i class="fas fa-user"></i></div>
            </div>
        </header>

        <div class="gv-content">
            @yield('gestionnaire-content')
        </div>
    </div>
</div>
@endsection
