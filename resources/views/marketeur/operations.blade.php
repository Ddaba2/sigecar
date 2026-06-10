@extends('layouts.marketeur')

@section('marketeur-content')
@php $fmt = fn ($n) => number_format((float) $n, 0, ',', ' '); @endphp

<div class="gv-section-head" style="margin-top:0;align-items:flex-start;">
    <div>
        <p class="gv-breadcrumb">Gestion des flux</p>
        <h1 class="gv-page-title">Mes Opérations</h1>
    </div>
    <div class="mk-page-actions">
        <a href="#" class="gv-btn-blue gv-btn-green" onclick="window.print();return false;"><i class="fas fa-download"></i> Télécharger PDF</a>
        <a href="{{ route('marketeur.operations', request()->query()) }}" class="gv-btn-blue"><i class="fas fa-file-excel"></i> Télécharger .xlsx</a>
    </div>
</div>

<form method="GET" action="{{ route('marketeur.operations') }}" class="mk-filter-bar">
    <div class="mk-filter-field">
        <label>PERIODE</label>
        <select name="periode">
            <option value="">Toutes</option>
            <option value="7" @selected(request('periode') == '7')>7 derniers jours</option>
            <option value="30" @selected(request('periode', '30') == '30')>Derniers 30 jours</option>
            <option value="90" @selected(request('periode') == '90')>90 derniers jours</option>
        </select>
    </div>
    <div class="mk-filter-field">
        <label>TYPE D'OPERATION</label>
        <select name="type">
            <option value="tous" @selected(request('type', 'tous') == 'tous')>Tous les types</option>
            <option value="depotage" @selected(request('type') == 'depotage')>Dépotage</option>
            <option value="chargement" @selected(request('type') == 'chargement')>Chargement</option>
        </select>
    </div>
    <div class="mk-filter-field">
        <label>PRODUIT</label>
        <select name="produit_id">
            <option value="">Tous les produits</option>
            @foreach($produits as $p)
                <option value="{{ $p->id }}" @selected(request('produit_id') == $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="gv-btn-blue" style="margin-left:auto;"><i class="fas fa-magnifying-glass"></i> Filtrer les résultats</button>
</form>

<div class="gv-table-wrap">
    <table class="gv-table">
        <thead>
            <tr>
                <th>DATE &amp; HEURE</th>
                <th>TYPE</th>
                <th>PRODUIT</th>
                <th>VOLUME BRUT</th>
                <th>VOL. CORRIGE 15°c</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($operations as $op)
                <tr>
                    <td>{{ $op['date']?->format('d M Y H:i') ?? '—' }}</td>
                    <td><i class="fas {{ $op['type_icon'] }}" style="color:var(--gv-blue);margin-right:6px;"></i>{{ $op['type'] }}</td>
                    <td><span class="mk-prod-pill">{{ $op['produit'] }}</span></td>
                    <td><strong>{{ $fmt($op['volume_brut']) }}L</strong></td>
                    <td>{{ $fmt($op['volume_corrige']) }}L</td>
                    <td>
                        @if(in_array($op['status'], ['acquitte', 'termine', 'confirmed', 'completed']))
                            <span class="mk-status ok">Acquitté</span>
                        @elseif($op['status'] === 'sous_douane')
                            <span class="mk-status warn">Sous douane</span>
                        @else
                            <span class="mk-status pending">{{ ucfirst($op['status'] ?? 'En cours') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#6b7280;">Aucune opération trouvée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@php
    $fmtShort = fn ($n) => $n >= 1_000_000 ? round($n / 1_000_000, 1) . 'M' : ($n >= 1_000 ? round($n / 1_000) . 'K' : $fmt($n));
    $sousPct = min(100, max(5, $sousDouaneActuel > 0 ? 35 : 5));
@endphp

<div class="mk-summary-grid">
    <div class="mk-summary-card navy">
        <i class="fas fa-download corner-icon"></i>
        <div class="label">TOTAL DEPOTAGES (MOIS)</div>
        <div class="value">{{ $fmtShort($totalDepotagesMois) }} Litres</div>
    </div>
    <div class="mk-summary-card light">
        <i class="fas fa-upload corner-icon"></i>
        <div class="label">TOTAL CHARGEMENTS (MOIS)</div>
        <div class="value">{{ $fmtShort($totalChargementsMois) }} Litres</div>
    </div>
    <div class="mk-summary-card white">
        <i class="fas fa-ban corner-icon" style="color:var(--gv-red);opacity:1;"></i>
        <div class="label">SOUS DOUANE ACTUEL</div>
        <div class="value">{{ $fmtShort($sousDouaneActuel) }} Litres</div>
        <div class="progress"><div class="progress-fill" style="width:{{ $sousPct }}%;"></div></div>
    </div>
</div>
@endsection
