@extends('layouts.marketeur')

@section('marketeur-content')
@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $maxVol = max($totalDepotages, $totalChargements, 1);
@endphp

<div class="mk-dash-top">
    <div class="mk-hero-card">
        <div class="label">Litre totale disponible</div>
        <div class="value">+{{ $fmt($totalLitresDisponibles) }} <span>L</span></div>
        <div class="meta">Dernier mise à jour : Aujourd'hui, {{ now()->format('H:i') }}</div>
    </div>
    <div class="mk-mini-card">
        <div class="head">
            <div class="icon blue"><i class="fas fa-download"></i></div>
            <div>
                <div class="title">Total Dépotages</div>
                <div class="vol">{{ $fmt($totalDepotages) }} L</div>
            </div>
        </div>
        <div class="mk-bar"><div class="mk-bar-fill teal" style="width:{{ min(100, round($totalDepotages / $maxVol * 100)) }}%;"></div></div>
    </div>
    <div class="mk-mini-card">
        <div class="head">
            <div class="icon teal"><i class="fas fa-upload"></i></div>
            <div>
                <div class="title">Total Chargements</div>
                <div class="vol">{{ $fmt($totalChargements) }} L</div>
            </div>
        </div>
        <div class="mk-bar"><div class="mk-bar-fill dark" style="width:{{ min(100, round($totalChargements / $maxVol * 100)) }}%;"></div></div>
    </div>
</div>

<div class="gv-section-head" style="margin-top:0;">
    <div>
        <div class="gv-section-title">Dernières Opérations</div>
        <div class="gv-section-sub">Flux de carburant en temps réel sur vos dépôt.</div>
    </div>
    <a href="{{ route('marketeur.operations') }}" class="gv-btn-blue gv-btn-outline">
        Voir toutes les opérations <i class="fas fa-arrow-right"></i>
    </a>
</div>

<div class="gv-table-wrap">
    <table class="gv-table">
        <thead>
            <tr>
                <th>DATE</th>
                <th>TYPE</th>
                <th>PRODUIT</th>
                <th>VOLUME</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentOperations as $op)
                <tr>
                    <td>{{ $op['date']?->format('d M Y H:i') ?? '—' }}</td>
                    <td><i class="fas {{ $op['type_icon'] }}" style="color:var(--gv-blue);margin-right:6px;"></i>{{ $op['type'] }}</td>
                    <td><span class="mk-prod-pill">{{ $op['produit'] }}</span></td>
                    <td><strong>{{ $fmt($op['volume']) }}L</strong></td>
                    <td>
                        @if(in_array($op['status'], ['acquitte', 'termine', 'confirmed', 'completed']))
                            <span class="mk-status ok">Terminé</span>
                        @elseif($op['status'] === 'sous_douane')
                            <span class="mk-status warn">Sous douane</span>
                        @else
                            <span class="mk-status pending">{{ ucfirst($op['status'] ?? 'En cours') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#6b7280;">Aucune opération récente.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mk-bottom-grid">
    <div class="mk-stock-card">
        <h3>Répartitions des Stocks</h3>
        @forelse($stockByProduct as $label => $volume)
            @php $pct = min(100, max(5, (int) round($volume / $maxStockProduct * 100))); @endphp
            <div class="mk-stock-row">
                <div class="row-head">
                    <span>{{ strtoupper($label) }}</span>
                    <span>{{ $fmt($volume) }} L</span>
                </div>
                <div class="mk-bar" style="background:rgba(255,255,255,0.5);">
                    <div class="mk-bar-fill {{ $loop->even ? 'dark' : 'teal' }}" style="width:{{ $pct }}%;"></div>
                </div>
            </div>
        @empty
            <p style="font-size:0.9rem;color:#374151;">Aucune donnée de stock disponible.</p>
        @endforelse
    </div>
    <div class="mk-report-card">
        <div>
            <h3 style="font-family:var(--gv-serif);margin:0 0 10px;">Rapports Hebdomadaires</h3>
            <p style="font-size:0.9rem;color:#6b7280;margin:0 0 16px;">Vos rapports de consommation et de stock sont prêts à être téléchargés.</p>
            <a href="{{ route('marketeur.operations') }}" class="gv-btn-blue gv-btn-green"><i class="fas fa-download"></i> Télécharger PDF</a>
        </div>
        <div class="mk-donut">80%</div>
    </div>
</div>
@endsection
