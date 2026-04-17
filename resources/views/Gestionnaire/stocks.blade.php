@extends('layouts.gestionnaire')

@section('gestionnaire-content')
@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $pctFmt = fn ($n) => round($n) . '%';
@endphp

<div class="gv-section-head" style="margin-top:0;">
    <div>
        <h1 class="gv-page-title" style="margin-bottom:4px;">{{ $pageTitle }}</h1>
        <p class="gv-page-sub" style="margin:0;">Supervision des stocks, douanes et bacs de stockage.</p>
    </div>
</div>

<div class="gv-kpi-grid">
    <div class="gv-kpi">
        <div class="gv-kpi-label">CAPACITÉ TOTALE</div>
        <div class="gv-kpi-value">{{ $fmt($totalCapacite) }} L</div>
    </div>
    <div class="gv-kpi">
        <div class="gv-kpi-label">STOCK ACQUITTÉ</div>
        <div class="gv-kpi-value" style="color:#0d9488;">{{ $fmt($acquitte) }} L</div>
        <div class="gv-kpi-meta teal"><i class="fas fa-circle-check"></i> Libre circulation</div>
    </div>
    <div class="gv-kpi">
        <div class="gv-kpi-label">SOUS DOUANE</div>
        <div class="gv-kpi-value" style="color:#dc3545;">{{ $fmt($sousDouaneVol) }} L</div>
        <div class="gv-kpi-meta red"><i class="fas fa-lock"></i> En attente de dédouanement</div>
    </div>
    <div class="gv-kpi gv-kpi-dark">
        <div class="gv-kpi-label">ALERTES ACTIVES</div>
        <div class="gv-kpi-value">{{ str_pad((string) $alertes->count(), 2, '0', STR_PAD_LEFT) }}</div>
        <div class="gv-kpi-meta" style="color:rgba(255,255,255,0.85);">
            <i class="fas fa-triangle-exclamation"></i>
            @if($alertes->isEmpty())
                Aucune alerte
            @else
                {{ $alertes->first()->nom }} — niveau critique
            @endif
        </div>
    </div>
</div>

<div class="gv-section-head">
    <div class="gv-section-title">
        <i class="fas fa-database"></i>
        Supervision des Bacs
    </div>
    <a href="{{ route('gestionnaire.stocks.tous') }}" class="gv-btn-blue">VUE D'ENSEMBLE</a>
</div>

<div class="gv-tank-grid">
    @foreach($cuves as $cuve)
        @php
            $pct = min(100, max(0, (int) $cuve->pourcentage_remplissage));
            $st = $cuve->statut_alerte;
            $gClass = $st === 'bas' ? 'amber' : ($st === 'haut' ? 'red' : 'teal');
            $pillClass = $st === 'bas' ? 'warn' : ($st === 'haut' ? 'danger' : 'ok');
            $pillText = $st === 'bas' ? 'NIVEAU BAS' : ($st === 'haut' ? 'ALERTE TROP-PLEIN' : ($pct >= 40 && $pct <= 85 ? 'NORMAL' : 'OPÉRATIONNEL'));
        @endphp
        <div class="gv-tank-card">
            <div class="gv-tank-head">
                <div>
                    <div class="gv-tank-id">{{ $cuve->nom ?? $cuve->code }}</div>
                    <div class="gv-tank-prod">{{ strtoupper($cuve->produit->name ?? '—') }}</div>
                </div>
                <span style="color:#9ca3af;"><i class="fas fa-ellipsis-vertical"></i></span>
            </div>
            <div class="gv-tank-body">
                <div class="gv-gauge {{ $gClass }}">
                    <div class="gv-gauge-fill" style="height: {{ $pct }}%;"></div>
                </div>
                <div class="gv-tank-pct">{{ $pctFmt($pct) }}</div>
                <div class="gv-tank-stats">
                    <div><strong>ACTUEL</strong><br>{{ $fmt($cuve->niveau_actuel) }} L</div>
                    <div><strong>CAPACITÉ</strong><br>{{ $fmt($cuve->capacite_totale) }} L</div>
                </div>
            </div>
            <span class="gv-pill {{ $pillClass }}">{{ $pillText }}</span>
        </div>
    @endforeach
</div>

<div class="gv-section-head">
    <div class="gv-section-title">Mouvements récents</div>
    <a href="{{ route('gestionnaire.stocks.tous') }}" class="gv-btn-blue" style="background:#fff;color:var(--gv-blue)!important;border:1px solid var(--gv-blue);">Voir tout</a>
</div>

<div class="gv-table-wrap">
    <table class="gv-table">
        <thead>
            <tr>
                <th>DATE &amp; HEURE</th>
                <th>SOCIÉTÉ</th>
                <th>PRODUIT</th>
                <th>VOLUME</th>
                <th>CUVE</th>
                <th>STATUT</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentDepotages as $row)
                <tr>
                    <td>{{ $row->date_operation->format('d M Y — H:i') }}</td>
                    <td>{{ $row->fournisseur }}</td>
                    <td style="text-transform:uppercase;">{{ $row->produit->name ?? '—' }}</td>
                    <td>{{ $fmt($row->volume_brut) }} L</td>
                    <td>{{ $row->cuve->nom ?? $row->cuve->code ?? '—' }}</td>
                    <td>
                        @if($row->status === 'acquitte')
                            <span class="gv-badge ok">Acquitté</span>
                        @else
                            <span class="gv-badge pending">Sous douane</span>
                        @endif
                    </td>
                    <td>
                        @if($row->status === 'sous_douane')
                            <form method="POST" action="{{ route('gestionnaire.depotage.acquitter', $row) }}" style="display:inline;" onsubmit="return confirm('Marquer ce dépotage comme acquitté ?');">
                                @csrf
                                <button type="submit" class="gv-btn-blue" style="padding:6px 12px;font-size:0.78rem;">Acquitter</button>
                            </form>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:#6b7280;">Aucun dépotage enregistré.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
