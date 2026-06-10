@extends('layouts.app')

@section('title', 'Opérateur - ' . ($title ?? 'SIGECAR'))

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
        --gv-input: #eef2ff;
        --gv-serif: 'Merriweather', Georgia, serif;
        --gv-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
        --gv-light-blue: #a8d4f0;
        --gv-green: #28a745;
    }

    .gv-shell { display: flex; min-height: 100vh; background: var(--gv-bg); font-family: var(--gv-sans); }
    .gv-sidebar {
        width: 280px; min-height: 100vh; background: linear-gradient(180deg, var(--gv-navy-deep) 0%, var(--gv-navy) 100%);
        color: #fff; display: flex; flex-direction: column; flex-shrink: 0; padding: 20px 14px 20px;
        position: sticky; top: 0; align-self: flex-start; height: 100vh;
    }
    .gv-sidebar-brand { text-align: center; padding: 8px 0 24px; border-bottom: 1px solid rgba(255,255,255,0.15); }
    .gv-sidebar-brand-icon {
        width: 72px; height: 72px; margin: 0 auto 12px; border: 2px solid rgba(255,255,255,0.85);
        border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px;
    }
    .gv-sidebar-brand h2 { margin: 0; font-family: var(--gv-serif); font-size: 1.35rem; font-weight: 700; }
    .gv-nav { flex: 1; display: flex; flex-direction: column; gap: 6px; padding: 20px 0; overflow-y: auto; }
    .gv-nav a {
        display: flex; align-items: center; gap: 14px; padding: 12px 16px; border-radius: 10px;
        color: #fff; text-decoration: none; font-family: var(--gv-serif); font-size: 1rem;
        transition: background 0.15s ease;
    }
    .gv-nav a:hover { background: rgba(255,255,255,0.08); }
    .gv-nav a.gv-nav-active { background: var(--gv-blue); box-shadow: 0 4px 14px rgba(0,123,255,0.35); }
    .gv-nav a i { width: 22px; text-align: center; }
    .gv-sidebar-foot { border-top: 1px solid rgba(255,255,255,0.2); padding-top: 16px; display: flex; flex-direction: column; gap: 10px; }
    .gv-sidebar-btn {
        display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px 16px;
        border-radius: 10px; background: var(--gv-blue); color: #fff !important; text-decoration: none;
        font-family: var(--gv-serif); font-size: 1rem; font-weight: 600;
    }
    .gv-settings-link {
        display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px;
        background: var(--gv-blue); color: #fff !important; text-decoration: none;
        font-family: var(--gv-serif); font-size: 1rem; font-weight: 600;
    }
    .gv-settings-link:not(.gv-nav-active) { background: transparent; }
    .gv-settings-link.gv-nav-active { background: var(--gv-blue); }
    .gv-logout-form button {
        width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px;
        padding: 12px 16px; border: 0; border-radius: 10px; background: var(--gv-red-dark);
        color: #fff; font-family: var(--gv-serif); font-size: 1rem; font-weight: 600; cursor: pointer;
    }
    .gv-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .gv-topbar {
        height: 72px; background: var(--gv-topbar); display: flex; align-items: center;
        justify-content: space-between; padding: 0 24px; border-bottom: 1px solid #d8dce0;
    }
    .gv-brand img { height: 52px; width: auto; }
    .gv-topbar-actions { display: flex; align-items: center; gap: 22px; }
    .gv-bell { font-size: 1.35rem; color: var(--gv-blue); cursor: pointer; }
    .gv-company-badge {
        padding: 6px 14px; border-radius: 8px; background: #fff; border: 1px solid #d1d5db;
        font-weight: 800; font-size: 0.85rem; color: #e30613; letter-spacing: 0.04em;
    }
    .gv-content { flex: 1; padding: 28px 32px 40px; overflow-x: auto; }
    .gv-breadcrumb { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.12em; color: var(--gv-teal); text-transform: uppercase; margin: 0 0 8px; }
    .gv-page-title { font-family: var(--gv-serif); font-size: 1.85rem; font-weight: 700; color: #111; margin: 0 0 8px; }
    .gv-page-sub { color: #333; font-size: 0.95rem; margin: 0 0 24px; line-height: 1.5; }
    .gv-page-title-serif { font-family: var(--gv-serif); }

    .mk-dash-top { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 18px; margin-bottom: 28px; }
    @media (max-width: 1100px) { .mk-dash-top { grid-template-columns: 1fr; } }
    .mk-hero-card {
        background: var(--gv-navy); color: #fff; border-radius: 14px; padding: 28px 30px;
        box-shadow: 0 4px 20px rgba(0,27,51,0.15);
    }
    .mk-hero-card .label { font-size: 0.95rem; opacity: 0.9; margin-bottom: 12px; }
    .mk-hero-card .value { font-size: 2.4rem; font-weight: 800; }
    .mk-hero-card .value span { color: var(--gv-light-blue); }
    .mk-hero-card .meta { margin-top: 16px; font-size: 0.8rem; opacity: 0.75; }
    .mk-mini-card {
        background: #fff; border-radius: 14px; padding: 22px 24px;
        box-shadow: 0 2px 12px rgba(0,27,51,0.06);
    }
    .mk-mini-card .head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .mk-mini-card .icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .mk-mini-card .icon.blue { background: #e8f4fd; color: var(--gv-blue); }
    .mk-mini-card .icon.teal { background: #e0f5f3; color: var(--gv-teal); }
    .mk-mini-card .title { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.06em; color: #6b7280; text-transform: uppercase; }
    .mk-mini-card .vol { font-size: 1.35rem; font-weight: 700; margin-top: 4px; }
    .mk-bar { height: 8px; border-radius: 4px; background: #e5e7eb; margin-top: 14px; overflow: hidden; }
    .mk-bar-fill { height: 100%; border-radius: 4px; }
    .mk-bar-fill.teal { background: var(--gv-teal); }
    .mk-bar-fill.dark { background: #111; }

    .gv-section-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin: 28px 0 18px; }
    .gv-section-title { font-size: 1.15rem; font-weight: 700; color: #111; }
    .gv-section-sub { font-size: 0.85rem; color: #6b7280; margin-top: 4px; }
    .gv-btn-blue {
        display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px;
        background: var(--gv-blue); color: #fff !important; border-radius: 8px;
        font-size: 0.85rem; font-weight: 600; text-decoration: none; border: 0; cursor: pointer;
    }
    .gv-btn-green { background: var(--gv-green); color: #fff !important; }
    .gv-btn-outline {
        background: #fff; color: var(--gv-blue) !important; border: 1px solid var(--gv-blue);
    }

    .gv-table-wrap { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,27,51,0.06); }
    .gv-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
    .gv-table th { background: #e8eef5; color: #374151; font-weight: 700; text-align: left; padding: 14px 16px; border-bottom: 1px solid #d1d5db; font-size: 0.75rem; letter-spacing: 0.04em; }
    .gv-table td { padding: 14px 16px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
    .gv-table tbody tr:hover { background: #fafafa; }

    .mk-prod-pill {
        display: inline-block; padding: 5px 14px; border-radius: 999px;
        background: var(--gv-light-blue); color: #0d2d4d; font-size: 0.8rem; font-weight: 600;
    }
    .mk-status {
        display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px;
        border-radius: 999px; font-size: 0.75rem; font-weight: 700;
    }
    .mk-status.ok { background: #d1fae5; color: #065f46; }
    .mk-status.ok::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #10b981; }
    .mk-status.warn { background: #fee2e2; color: #991b1b; }
    .mk-status.warn::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #ef4444; }
    .mk-status.pending { background: #fef3c7; color: #92400e; }
    .mk-status.pending::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #f59e0b; }

    .mk-filter-bar {
        display: flex; flex-wrap: wrap; align-items: flex-end; gap: 16px;
        background: #dce8f3; border-radius: 12px; padding: 18px 20px; margin-bottom: 24px;
    }
    .mk-filter-field label { display: block; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.08em; color: #4b5563; margin-bottom: 6px; }
    .mk-filter-field select {
        min-width: 160px; padding: 10px 12px; border: 0; border-radius: 8px;
        background: #fff; font-size: 0.9rem; font-family: var(--gv-sans);
    }

    .mk-bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 28px; }
    @media (max-width: 900px) { .mk-bottom-grid { grid-template-columns: 1fr; } }
    .mk-stock-card { background: var(--gv-light-blue); border-radius: 14px; padding: 24px; }
    .mk-stock-card h3 { font-family: var(--gv-serif); font-size: 1.2rem; margin: 0 0 20px; }
    .mk-stock-row { margin-bottom: 18px; }
    .mk-stock-row .row-head { display: flex; justify-content: space-between; font-weight: 700; font-size: 0.85rem; margin-bottom: 8px; }
    .mk-report-card {
        background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 12px rgba(0,27,51,0.06);
        display: flex; align-items: center; justify-content: space-between; gap: 20px;
    }
    .mk-donut {
        width: 90px; height: 90px; border-radius: 50%; border: 10px solid var(--gv-light-blue);
        border-top-color: var(--gv-blue); display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 1.1rem; flex-shrink: 0;
    }

    .mk-summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 28px; }
    @media (max-width: 900px) { .mk-summary-grid { grid-template-columns: 1fr; } }
    .mk-summary-card { border-radius: 14px; padding: 24px; position: relative; overflow: hidden; }
    .mk-summary-card.navy { background: var(--gv-navy); color: #fff; }
    .mk-summary-card.light { background: var(--gv-light-blue); color: #111; }
    .mk-summary-card.white { background: #fff; border: 1px solid #e5e7eb; }
    .mk-summary-card .label { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.06em; opacity: 0.85; }
    .mk-summary-card .value { font-size: 2rem; font-weight: 800; margin-top: 12px; }
    .mk-summary-card .corner-icon { position: absolute; top: 16px; right: 16px; font-size: 1.5rem; opacity: 0.5; }
    .mk-summary-card .progress { height: 6px; background: #e5e7eb; border-radius: 3px; margin-top: 16px; overflow: hidden; }
    .mk-summary-card .progress-fill { height: 100%; background: var(--gv-blue); border-radius: 3px; }

    .mk-cession-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 28px; }
    @media (max-width: 700px) { .mk-cession-grid { grid-template-columns: 1fr; } }
    .mk-cession-card { border-radius: 14px; padding: 24px; }
    .mk-cession-card.dark { background: var(--gv-navy); color: #fff; }
    .mk-cession-card.light { background: var(--gv-light-blue); color: #111; }

    .gv-alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 0.9rem; }
    .gv-alert-success { background: #d1fae5; color: #065f46; }
    .gv-alert-error { background: #fee2e2; color: #991b1b; }

    .gv-settings-panel { background: #d9d9d9; border-radius: 12px; padding: 8px 0; max-width: 560px; }
    .gv-settings-row { display: flex; align-items: center; gap: 16px; padding: 16px 20px; border-bottom: 1px solid #c4c4c4; }
    .gv-settings-row:last-child { border-bottom: 0; }
    .gv-footer-app { text-align: center; margin-top: 48px; font-size: 0.8rem; color: #6b7280; }

    .mk-page-actions { display: flex; gap: 10px; flex-wrap: wrap; }
</style>
@endpush

@section('content')
<div class="gv-shell">
    <aside class="gv-sidebar">
        <div class="gv-sidebar-brand">
            <div class="gv-sidebar-brand-icon"><i class="fas fa-gas-pump"></i></div>
            <h2>Opérateurs</h2>
        </div>

        <nav class="gv-nav">
            <a href="{{ route('marketeur.dashboard') }}" class="@if(request()->routeIs('marketeur.dashboard')) gv-nav-active @endif">
                <i class="fas fa-th-large"></i> Tableau de bord
            </a>
            <a href="{{ route('marketeur.operations') }}" class="@if(request()->routeIs('marketeur.operations')) gv-nav-active @endif">
                <i class="fas fa-truck"></i> Mes opérations
            </a>
            <a href="{{ route('marketeur.cessions') }}" class="@if(request()->routeIs('marketeur.cessions') || request()->routeIs('marketeur.cession.*')) gv-nav-active @endif">
                <i class="fas fa-right-left"></i> Cession
            </a>
        </nav>

        <div class="gv-sidebar-foot">
            <a href="{{ route('marketeur.cession.create') }}" class="gv-sidebar-btn">
                <i class="fas fa-plus-circle"></i> Nouveau cession
            </a>
            <a href="{{ route('marketeur.settings') }}" class="gv-settings-link @if(request()->routeIs('marketeur.settings')) gv-nav-active @endif">
                <i class="fas fa-gear"></i> Paramètres
            </a>
            <form method="POST" action="{{ route('logout') }}" class="gv-logout-form">
                @csrf
                <button type="submit"><i class="fas fa-right-from-bracket"></i> Déconnexion</button>
            </form>
        </div>
    </aside>

    <div class="gv-main">
        <header class="gv-topbar">
            <div class="gv-brand">
                <img src="{{ asset('images/logo.png') }}" alt="SIGECAR">
            </div>
            <div class="gv-topbar-actions">
                <span class="gv-bell" title="Notifications"><i class="fas fa-bell"></i></span>
                <span class="gv-company-badge">{{ strtoupper(Auth::user()->marketeur->company_name ?? Auth::user()->company_name ?? 'TOTAL') }}</span>
            </div>
        </header>

        <div class="gv-content">
            @yield('marketeur-content')
        </div>
    </div>
</div>
@endsection
