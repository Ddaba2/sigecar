@extends('layouts.admin')

@section('admin-content')
<h1 class="admin-title">Tableau de bord administrateur</h1>

<div class="stat-grid">
    <div class="stat-card dark"><div><i class="fas fa-users"></i> TOTAL UTILISATEUR</div><strong>{{ $totalUsers }}</strong></div>
    <div class="stat-card green"><div><i class="fas fa-user-check"></i> ACTIFS</div><strong>{{ $activeUsers }}</strong></div>
    <div class="stat-card red"><div><i class="fas fa-user-clock"></i> INACTIFS</div><strong>{{ $inactiveUsers }}</strong></div>
</div>

<div class="title-row">
    <h2 class="admin-subtitle">Listes des utilisateurs</h2>
    <a href="{{ route('admin.add-user') }}" class="add-user-btn"><i class="far fa-plus-square"></i> Ajouter un utilisateur</a>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>Nom complet</th>
            <th>Rôle</th>
            <th>Email</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ ucfirst($user->role) }}</td>
            <td>{{ $user->email }}</td>
            <td>
                <span class="status-badge {{ $user->status === 'active' ? 'status-active' : 'status-inactive' }}">
                    {{ $user->status === 'active' ? 'Actif' : 'Désactivé' }}
                </span>
            </td>
            <td class="actions-cell">
                <a href="{{ route('admin.edit-user', $user->id) }}" class="action edit"><i class="fas fa-pen"></i></a>
                @if($user->status === 'inactive')
                    <form method="POST" action="{{ route('admin.activate-user', $user->id) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="action activate"><i class="fas fa-check-circle"></i> Activer</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.disable-user', $user->id) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="action disable"><i class="fas fa-user-lock"></i> Désactiver</button>
                    </form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<style>
    .stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:14px; }
    .stat-card { background:#efeff1; border-radius:10px; padding:10px 14px; border-left:7px solid #888; }
    .stat-card div { font-family:Georgia,serif; font-size:18px; margin-bottom:6px; }
    .stat-card strong { font-size:64px; font-family:Georgia,serif; line-height:1; }
    .stat-card.dark { background:#002647; color:#fff; border-left-color:#002647; }
    .stat-card.green { border-left-color:#22b164; }
    .stat-card.blue { border-left-color:#1561f0; color:#1561f0; }
    .stat-card.red { border-left-color:#f00606; color:#d20d0d; }
    .title-row { display:flex; justify-content:space-between; align-items:center; margin-top:8px; margin-bottom: 18px; }
    .admin-subtitle { font-size: 46px !important; font-weight: 700; margin: 0; letter-spacing: 0.02em; color: #111 !important; display: block; }
    .add-user-btn {
        background:#1f84bb; color:#fff; text-decoration:none; border-radius:9px;
        padding:9px 16px; font-size:20px; font-family:Georgia,serif; display:flex; gap:10px; align-items:center;
    }
    .add-user-btn i { font-size:18px; }
    .admin-table { width:100%; table-layout: fixed; }
    .admin-table th,
    .admin-table td {
        vertical-align: middle;
        padding: 14px 18px;
    }
    .actions-cell { white-space: nowrap; display: flex; align-items: center; gap: 10px; }
    .actions-cell form { display: inline-flex; align-items: center; }
    .action { font-size:24px; display: inline-flex; align-items: center; gap: 8px; line-height: 1; }
    .action:not(:last-child) { margin-right: 12px; }
    .action.edit { color:#11a0dd; }
    .action.ban { color:#e11b1b; }
    .actions-cell form,
    .actions-cell a {
        display: inline-flex;
        align-items: center;
    }
    .action.activate { color:#16a34a; border:0; background:transparent; cursor:pointer; padding:0 8px; font-size:14px; min-height: 36px; }
    .action.disable { color:#b91c1c; border:0; background:transparent; cursor:pointer; padding:0 8px; font-size:14px; min-height: 36px; }
    .action.disable i, .action.activate i { font-size:18px; }
    .action.activate:hover { color:#15803d; }
    .action.disable:hover { color:#991b1b; }
    .actions-cell button { line-height: 1; }
    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.95rem;
        font-weight: 600;
        min-width: 80px;
        text-transform: uppercase;
    }
    .status-active {
        color: #065f46;
        background: #d1fae5;
        border: 1px solid #10b981;
    }
    .status-inactive {
        color: #991b1b;
        background: #fcdada;
        border: 1px solid #f87171;
    }
</style>
@endsection
