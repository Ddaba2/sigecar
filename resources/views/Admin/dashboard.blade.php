@extends('layouts.admin')

@section('admin-content')
<h1 class="admin-title">Tableau de bord administrateur</h1>

<div class="stat-grid">
    <div class="stat-card dark">
        <div><i class="fas fa-users"></i> TOTAL UTILISATEUR</div>
        <strong>{{ $totalUsers }}</strong>
    </div>
    <div class="stat-card green">
        <div><i class="fas fa-user-check"></i> ACTIFS</div>
        <strong>{{ $activeUsers }}</strong>
    </div>
    <div class="stat-card blue">
        <div><i class="fas fa-user-clock"></i> INACTIFS</div>
        <strong>{{ $inactiveUsers }}</strong>
    </div>
    <div class="stat-card red">
        <div><i class="fas fa-ban"></i> INACTIFS</div>
        <strong>0</strong>
    </div>
</div>

<div class="section-row">
    <h2 class="admin-subtitle">Niveaux de Stock par Produit</h2>
    <a href="{{ route('admin.depot') }}">Voir plus <i class="fas fa-arrow-right"></i></a>
</div>

<div class="stock-box admin-block">
    @foreach($stockLevels as $stock)
    <div class="stock-line"><span>{{ $stock['nom'] }}</span><span>{{ number_format($stock['stock'], 0, ',', ' ') }} L</span></div>
    <div class="bar"><em style="width:{{ $stock['percentage'] }}%;background:#1a5de6;"></em></div>
    @endforeach
</div>

<div class="section-row" style="margin-top: 18px;">
    <h2 class="admin-subtitle">Historique des Opérations</h2>
    <a href="{{ route('admin.transport') }}">Voir plus <i class="fas fa-arrow-right"></i></a>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>DATE</th>
            <th>TYPE</th>
            <th>PRODUIT</th>
            <th>VOLUME</th>
            <th>SOCIETE</th>
        </tr>
    </thead>
    <tbody>
        @foreach($operations as $operation)
        <tr>
            <td>{{ $operation['date'] ? $operation['date']->format('d M Y') : 'N/A' }}</td>
            <td>{{ $operation['type'] }}</td>
            <td>{{ $operation['produit'] }}</td>
            <td>{{ number_format($operation['volume'], 0, ',', ' ') }}L</td>
            <td>{{ $operation['societe'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<style>
    .stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:12px; }
    .stat-card { background:#efeff1; border-radius:10px; padding:10px 14px; border-left:7px solid #888; }
    .stat-card div { font-family:Georgia,serif; font-size:18px; margin-bottom:6px; }
    .stat-card strong { font-size:64px; font-family:Georgia,serif; line-height:1; }
    .stat-card.dark { background:#002647; color:#fff; border-left-color:#002647; }
    .stat-card.green { border-left-color:#22b164; }
    .stat-card.blue { border-left-color:#1561f0; color:#1561f0; }
    .stat-card.red { border-left-color:#f00606; color:#d20d0d; }
    .section-row { display:flex; justify-content:space-between; align-items:center; }
    .section-row a { color:#2b5ca8; text-decoration:none; font-size:34px; font-family:Georgia,serif; }
    .stock-box { max-width:840px; padding:10px 14px; background:#90bdd8; }
    .stock-line { display:flex; justify-content:space-between; font-family:Georgia,serif; font-size:18px; margin-top:6px; }
    .bar { height:11px; background:#d5edf5; border-radius:10px; }
    .bar em { display:block; height:11px; border-radius:10px; }
</style>
@endsection
