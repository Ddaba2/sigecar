@extends('layouts.gestionnaire')

@section('gestionnaire-content')
@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
@endphp

<p class="gv-breadcrumb">STOCK &amp; DOUANE / INVENTAIRE COMPLET</p>

<div class="gv-section-head" style="margin-top:0;">
    <div>
        <h1 class="gv-page-title" style="margin-bottom:4px;">Tout le stock — Cuves</h1>
        <p class="gv-page-sub" style="margin:0;">Liste exhaustive de toutes les cuves et de leur état en base de données.</p>
    </div>
    <a href="{{ route('gestionnaire.stocks') }}" class="gv-btn-blue" style="background:#fff;color:var(--gv-blue)!important;border:1px solid var(--gv-blue);">
        <i class="fas fa-arrow-left"></i> Retour supervision
    </a>
</div>

<div class="gv-kpi-grid" style="margin-bottom:24px;">
    <div class="gv-kpi">
        <div class="gv-kpi-label">CAPACITÉ TOTALE</div>
        <div class="gv-kpi-value">{{ $fmt($totalCapacite) }} L</div>
    </div>
    <div class="gv-kpi">
        <div class="gv-kpi-label">VOLUME TOTAL STOCKÉ</div>
        <div class="gv-kpi-value">{{ $fmt($totalStock) }} L</div>
    </div>
    <div class="gv-kpi">
        <div class="gv-kpi-label">SOUS DOUANE (cuves)</div>
        <div class="gv-kpi-value" style="color:#dc3545;">{{ $fmt($sousDouaneVol) }} L</div>
    </div>
    <div class="gv-kpi">
        <div class="gv-kpi-label">ACQUITTÉ (cuves)</div>
        <div class="gv-kpi-value" style="color:#0d9488;">{{ $fmt($acquitteVol) }} L</div>
    </div>
</div>

<div class="gv-table-wrap">
    <table class="gv-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Nom</th>
                <th>Produit</th>
                <th>Niveau (L)</th>
                <th>Capacité (L)</th>
                <th>%</th>
                <th>Type douane</th>
                <th>Statut cuve</th>
                <th>Seuil bas / haut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cuves as $c)
                @php
                    $cap = max(1, (int) $c->capacite_totale);
                    $pct = min(100, round(((int) $c->niveau_actuel / $cap) * 100));
                @endphp
                <tr>
                    <td>{{ $c->code }}</td>
                    <td>{{ $c->nom }}</td>
                    <td style="text-transform:uppercase;">{{ $c->produit->name ?? '—' }}</td>
                    <td>{{ $fmt($c->niveau_actuel) }}</td>
                    <td>{{ $fmt($c->capacite_totale) }}</td>
                    <td>{{ $pct }} %</td>
                    <td>
                        @if($c->type_douane === 'sous_douane')
                            <span class="gv-badge pending">Sous douane</span>
                        @else
                            <span class="gv-badge ok">Acquitté</span>
                        @endif
                    </td>
                    <td>{{ $c->status }}</td>
                    <td>{{ $fmt($c->seuil_alerte_bas) }} / {{ $fmt($c->seuil_alerte_haut) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center;color:#6b7280;">Aucune cuve enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
