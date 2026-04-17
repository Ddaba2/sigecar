@extends('layouts.app')

@section('title', 'Admin - ' . ($title ?? 'Dashboard'))

@section('content')
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-user">
            <i class="fas fa-user-cog"></i>
            <h3>Administrateur</h3>
        </div>

        <nav class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                <i class="fas fa-th-large"></i> Tableau de bord
            </a>
            <a href="{{ route('admin.users') }}" class="admin-nav-link @if(request()->routeIs('admin.users*')) active @endif">
                <i class="fas fa-users"></i> Gestion d’utilisateur
            </a>
            <a href="{{ route('admin.depot') }}" class="admin-nav-link @if(request()->routeIs('admin.depot')) active @endif">
                <i class="fas fa-warehouse"></i> Gestion de dépôt
            </a>
            <a href="{{ route('admin.transport') }}" class="admin-nav-link @if(request()->routeIs('admin.transport')) active @endif">
                <i class="fas fa-truck"></i> Gestion du transport
            </a>
            <a href="{{ route('admin.cessions') }}" class="admin-nav-link @if(request()->routeIs('admin.cessions')) active @endif">
                <i class="fas fa-exchange-alt"></i> Gestion des cessions
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <a href="#" class="admin-settings-btn">
                <i class="fas fa-cog"></i> Paramètres
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="admin-logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </button>
            </form>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="admin-brand">
                <img src="{{ asset('images/logo.png') }}" alt="SIGECAR">
            </div>
            <div class="admin-topbar-icons">
                <i class="fas fa-bell"></i>
                <i class="fas fa-user-cog"></i>
            </div>
        </header>

        <div class="admin-content">
            @yield('admin-content')
        </div>
    </main>
</div>

<style>
    .admin-shell {
        display: flex;
        min-height: 100vh;
        background: #e7e7ea;
    }
    .admin-sidebar {
        width: 285px;
        background: #001a33;
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 18px 12px 16px;
    }
    .admin-user {
        text-align: center;
        margin: 12px 0 20px;
    }
    .admin-user i {
        font-size: 110px;
    }
    .admin-user h3 {
        margin: 10px 0 0;
        font-size: 30px;
        line-height: 1.1;
        font-weight: 700;
        font-family: Georgia, serif;
    }
    .admin-nav {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .admin-nav-link {
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 20px;
        font-family: Georgia, serif;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .admin-nav-link i {
        font-size: 18px;
        width: 20px;
        text-align: center;
    }
    .admin-nav-link.active,
    .admin-nav-link:hover {
        background: #0a8fdd;
    }
    .admin-sidebar-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.3);
        padding-top: 14px;
        display: grid;
        gap: 10px;
    }
    .admin-settings-btn {
        font-size: 22px;
        color: #d7dfeb;
        text-decoration: none;
        font-family: Georgia, serif;
        padding: 10px 14px;
        display: inline-flex;
        gap: 12px;
        align-items: center;
    }
    .admin-settings-btn i {
        font-size: 22px;
        width: 24px;
        text-align: center;
    }
    .admin-logout-btn {
        border: 0;
        background: #d80303;
        color: #fff;
        border-radius: 8px;
        padding: 8px 12px;
        font-family: Georgia, serif;
        font-size: 24px;
        display: inline-flex;
        gap: 10px;
        align-items: center;
        cursor: pointer;
    }
    .admin-main {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .admin-topbar {
        height: 80px;
        background: #d3d3d6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 18px;
    }
    .admin-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 26px;
        font-weight: 700;
        color: #184774;
        letter-spacing: 1px;
        font-family: Arial, sans-serif;
    }
    .admin-brand img {
        height: 56px;
        width: auto;
        object-fit: cover;
    }
    .admin-topbar-icons {
        display: flex;
        align-items: center;
        gap: 24px;
        padding-right: 14px;
    }
    .admin-topbar-icons i {
        font-size: 40px;
        color: #0a4de6;
    }
    .admin-topbar-icons i:last-child {
        color: #636363;
    }
    .admin-content {
        padding: 10px 8px;
    }
    .admin-block {
        background: #ececee;
        border-radius: 8px;
        border: 1px solid #d9d9db;
    }
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        background: #ececee;
    }
    .admin-table th,
    .admin-table td {
        border-bottom: 1px solid #a3a3a3;
        padding: 10px 16px;
        text-align: left;
        font-family: Georgia, serif;
        font-size: 16px;
    }
    .admin-table th {
        background: #c4c4c6;
        font-weight: 500;
    }
    .admin-title {
        margin: 0 0 14px;
        font-size: 18px;
        font-family: Georgia, serif;
    }
    .admin-subtitle {
        font-size: 12px;
        font-family: Georgia, serif;
        margin: 0 0 16px;
    }
</style>
@endsection
