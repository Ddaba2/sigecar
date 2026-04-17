@extends('layouts.admin')

@section('admin-content')
<h2 class="admin-subtitle">Niveaux des Cuves</h2>

<div class="cuves-grid">
    @foreach($cuves as $cuve)
    <div class="cuve-card">
        <div class="cuve-head">
            <strong>{{ $cuve->code }}</strong>
            <small>{{ $cuve->produit ? $cuve->produit->nom : 'Non défini' }}</small>
        </div>
        <div class="cuve-body">
            <div class="gauge" style="--p:{{ $cuve->capacite_totale > 0 ? round(($cuve->niveau_actuel / $cuve->capacite_totale) * 100) : 0 }}%;--c:{{ $cuve->capacite_totale > 0 && ($cuve->niveau_actuel / $cuve->capacite_totale) > 0.9 ? '#ff1010' : (($cuve->capacite_totale > 0 && ($cuve->niveau_actuel / $cuve->capacite_totale) < 0.2) ? '#efb12d' : '#10a9b3') }}">
                {{ $cuve->capacite_totale > 0 ? round(($cuve->niveau_actuel / $cuve->capacite_totale) * 100) : 0 }}%
            </div>
            <div>
                <p>ACTUEL <b>{{ number_format($cuve->niveau_actuel, 0, ',', ' ') }} L</b></p>
                <p>CAPACITE <b>{{ number_format($cuve->capacite_totale, 0, ',', ' ') }} L</b></p>
                <span class="{{ $cuve->capacite_totale > 0 && ($cuve->niveau_actuel / $cuve->capacite_totale) > 0.9 ? 'danger' : (($cuve->capacite_totale > 0 && ($cuve->niveau_actuel / $cuve->capacite_totale) < 0.2) ? 'low' : 'ok') }}">
                    {{ $cuve->capacite_totale > 0 && ($cuve->niveau_actuel / $cuve->capacite_totale) > 0.9 ? 'ALERTE TROP-PLEIN' : (($cuve->capacite_totale > 0 && ($cuve->niveau_actuel / $cuve->capacite_totale) < 0.2) ? 'NIVEAU BAS' : 'OPERATIONNEL') }}
                </span>
            </div>
        </div>
    </div>
    @endforeach
</div>

<h2 class="admin-subtitle" style="margin-top:14px;"><i class="fas fa-history"></i> HISTORIQUES DES DÉPÔTS</h2>

<table class="admin-table">
    <thead><tr><th>DATE & Heure</th><th>SOCIETE</th><th>PRODUIT</th><th>VOLUME</th><th>CUVE</th><th>STATUS</th></tr></thead>
    <tbody>
        @foreach($depotages as $depotage)
        <tr>
            <td>{{ $depotage->date_operation ? $depotage->date_operation->format('d M Y') : 'N/A' }}</td>
            <td>{{ $depotage->fournisseur ?: 'N/A' }}</td>
            <td>{{ $depotage->produit ? $depotage->produit->nom : 'N/A' }}</td>
            <td>{{ number_format($depotage->volume_corrige ?: $depotage->volume_brut, 0, ',', ' ') }} L</td>
            <td>{{ $depotage->cuve ? $depotage->cuve->code : 'N/A' }}</td>
            <td><span class="tag {{ $depotage->status === 'acquitte' ? 'ok' : 'red' }}">{{ ucfirst($depotage->status ?: 'pending') }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
<style>
    .admin-subtitle { font-size: 34px; font-weight: 700; margin-bottom: 12px; }
    .cuves-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; }
    .cuve-card { background:#efeff1; border-radius:10px; padding:10px; }
    .cuve-head strong { display:block; font-family:Georgia,serif; font-size:18px; }
    .cuve-head small { font-family:Georgia,serif; font-size:14px; color:#434343; text-transform: uppercase; letter-spacing: 0.02em; }
    .cuve-body { display:flex; gap:10px; margin-top:10px; align-items:flex-end; }
    .gauge { width:34px; height:120px; border-radius:10px; background:linear-gradient(to top,var(--c) var(--p),#ddd var(--p)); color:#fff; font-size:15px; font-weight:700; display:flex; align-items:flex-end; justify-content:center; padding-bottom:6px; font-family:Georgia,serif; }
    .cuve-body p { margin:0 0 6px; font-family:Georgia,serif; font-size:12px; letter-spacing:0.01em; }
    .cuve-body b { display:block; font-size:18px; }
    .cuve-body span { padding:4px 8px; font-family:Georgia,serif; font-size:11px; border-radius:6px; }
    .ok { background:#d7f0ea; color:#1e6b53; }
    .low { background:#f1e4cd; color:#8b5d16; }
    .danger { background:#f6d3d3; color:#8d1f1f; }
    .tag { padding:4px 10px; color:#fff; font-size:12px; font-family:Georgia,serif; border-radius:4px; }
    .tag.ok { background:#31b952; }
    .tag.red { background:#ff1111; }
</style>
@endsection
