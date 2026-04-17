@extends('layouts.gestionnaire')

@section('gestionnaire-content')
@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    try {
        $dateRapport = $day->locale('fr')->isoFormat('dddd D MMMM YYYY');
    } catch (\Throwable $e) {
        $dateRapport = $day->format('Y-m-d');
    }
@endphp

<h1 class="gv-page-title" style="text-transform:uppercase;letter-spacing:0.04em;">Rapport journalier d'activité</h1>
<p class="gv-page-sub" style="text-transform:uppercase;font-size:0.85rem;color:#6b7280;">{{ $dateRapport }}</p>

<form method="get" action="{{ route('gestionnaire.rapports') }}" class="gv-card" style="padding:14px 18px;margin-bottom:20px;display:flex;align-items:flex-end;gap:16px;flex-wrap:wrap;">
    <div class="gv-field" style="margin:0;">
        <label>Date du rapport</label>
        <input type="date" name="date" value="{{ $day->format('Y-m-d') }}">
    </div>
    <button type="submit" class="gv-btn-blue">Afficher</button>
</form>

<div class="gv-kpi-grid" style="grid-template-columns:repeat(3,1fr);">
    <div class="gv-kpi">
        <div class="gv-kpi-label">VOLUME DÉPOTÉ (jour)</div>
        <div class="gv-kpi-value">{{ $fmt($volDepotJour) }} L</div>
        @if($pctDepotVsHier !== null)
            <span style="display:inline-block;margin-top:8px;padding:4px 10px;border-radius:6px;font-size:0.72rem;font-weight:700;{{ $pctDepotVsHier >= 0 ? 'background:#d1fae5;color:#065f46;' : 'background:#fee2e2;color:#991b1b;' }}">
                {{ $pctDepotVsHier >= 0 ? '↑' : '↓' }} {{ abs($pctDepotVsHier) }}% vs veille ({{ $fmt($volDepotHier) }} L)
            </span>
        @else
            <span style="display:inline-block;margin-top:8px;padding:4px 10px;border-radius:6px;background:#e5e7eb;color:#374151;font-size:0.72rem;font-weight:700;">Veille : {{ $fmt($volDepotHier) }} L</span>
        @endif
    </div>
    <div class="gv-kpi">
        <div class="gv-kpi-label">VOLUME CHARGÉ (jour)</div>
        <div class="gv-kpi-value">{{ $fmt($volChargeJour) }} L</div>
        @if($pctChargeVsHier !== null)
            <span style="display:inline-block;margin-top:8px;padding:4px 10px;border-radius:6px;font-size:0.72rem;font-weight:700;{{ $pctChargeVsHier >= 0 ? 'background:#fee2e2;color:#991b1b;' : 'background:#d1fae5;color:#065f46;' }}">
                {{ $pctChargeVsHier >= 0 ? '↑' : '↓' }} {{ abs($pctChargeVsHier) }}% vs veille ({{ $fmt($volChargeHier) }} L)
            </span>
        @else
            <span style="display:inline-block;margin-top:8px;padding:4px 10px;border-radius:6px;background:#e5e7eb;color:#374151;font-size:0.72rem;font-weight:700;">Veille : {{ $fmt($volChargeHier) }} L</span>
        @endif
    </div>
    <div class="gv-kpi">
        <div class="gv-kpi-label">CESSIONS DU JOUR</div>
        <div class="gv-kpi-value">{{ str_pad((string) $cessionsCount, 2, '0', STR_PAD_LEFT) }} / {{ $fmt($volCessionJour) }} L</div>
        <span style="display:inline-block;margin-top:8px;padding:4px 10px;border-radius:6px;background:#e0f2fe;color:#0369a1;font-size:0.72rem;font-weight:700;">
            Δ vs veille : {{ $cessionsDelta >= 0 ? '+' : '' }}{{ $cessionsDelta }} — En attente (tous statuts) : {{ $cessionsPending }}
        </span>
    </div>
</div>

<div class="gv-section-title" style="margin:8px 0 12px;">Dépotages (entrées)</div>
<div class="gv-table-wrap" style="margin-bottom:24px;">
    <table class="gv-table">
        <thead>
            <tr>
                <th>Heure</th>
                <th>Marqueteur / Fournisseur</th>
                <th>Produit</th>
                <th>Volume (L)</th>
                <th>Statut douanier</th>
            </tr>
        </thead>
        <tbody>
            @forelse($depotagesJour as $d)
                <tr>
                    <td>{{ $d->date_operation->format('H:i') }}</td>
                    <td>{{ $d->fournisseur }}</td>
                    <td style="text-transform:uppercase;">{{ $d->produit->name ?? '—' }}</td>
                    <td>{{ $fmt($d->volume_brut) }}</td>
                    <td>
                        @if($d->status === 'acquitte')
                            <span class="gv-badge ok">VALIDÉ</span>
                        @else
                            <span class="gv-badge" style="background:#0d6efd;color:#fff;">EN ATTENTE</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#6b7280;">Aucun dépotage à cette date.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="gv-section-title" style="margin:8px 0 12px;">Chargements (sorties)</div>
<div class="gv-table-wrap" style="margin-bottom:24px;">
    <table class="gv-table">
        <thead>
            <tr>
                <th>Heure</th>
                <th>Client</th>
                <th>Produit</th>
                <th>Volume (L)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($chargementsJour as $c)
                <tr>
                    <td>{{ $c->date_operation->format('H:i') }}</td>
                    <td>{{ $c->client_nom }}</td>
                    <td style="text-transform:uppercase;">{{ $c->produit->name ?? '—' }}</td>
                    <td>{{ $fmt($c->volume_brut) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;color:#6b7280;">Aucun chargement à cette date.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="gv-section-title" style="margin:8px 0 12px;">Cessions (transferts)</div>
<div class="gv-table-wrap" style="margin-bottom:28px;">
    <table class="gv-table">
        <thead>
            <tr>
                <th>Heure</th>
                <th>Bénéficiaire</th>
                <th>Produit</th>
                <th>Volume (L)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cessionsJour as $c)
                <tr>
                    <td>{{ $c->date_cession->format('H:i') }}</td>
                    <td>{{ $c->beneficiaire->company_name ?? '—' }}</td>
                    <td style="text-transform:uppercase;">{{ $c->produit->name ?? '—' }}</td>
                    <td>{{ $fmt($c->volume) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;color:#6b7280;">Aucune cession à cette date.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="gv-section-title" style="margin:8px 0 16px;">Stocks de fin de journée (cuves + flux du jour en volumes corrigés)</div>
<div class="gv-tank-grid">
    @foreach($famillesRapport as $f)
        <div class="gv-tank-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                <strong>{{ $f['title'] }}</strong>
                <span style="font-size:0.65rem;background:#e0f2fe;color:#0369a1;padding:4px 8px;border-radius:6px;font-weight:700;">{{ $f['badge'] }}</span>
            </div>
            <div style="font-size:0.85rem;color:#4b5563;line-height:1.6;">
                <div>Stock actuel (somme des cuves, type en base)</div>
                <div style="color:#0d6efd;">Entrées (jour, L corrigés) : +{{ $fmt($f['entrees_jour']) }}</div>
                <div style="color:#dc3545;">Sorties (jour, L corrigés) : −{{ $fmt($f['sorties_jour']) }}</div>
            </div>
            <div style="font-size:1.35rem;font-weight:800;color:#0d6efd;margin:12px 0;">{{ $fmt($f['stock_cuves']) }} L</div>
            <div style="height:10px;background:#e5e7eb;border-radius:5px;overflow:hidden;">
                <div style="height:100%;width:{{ $f['pct_remplissage'] }}%;background:#0d6efd;border-radius:5px;"></div>
            </div>
            <div style="font-size:0.75rem;color:#6b7280;margin-top:6px;">Remplissage : {{ $f['pct_remplissage'] }} % — Capacité totale cuves : {{ $fmt($f['capacite_totale']) }} L</div>
        </div>
    @endforeach
</div>

<div class="gv-form-actions" style="margin-top:8px;">
    <a href="{{ route('gestionnaire.rapports.export.csv', ['date' => $day->format('Y-m-d')]) }}" class="gv-btn-blue" style="background:#fff;color:#333!important;border:1px solid #ccc;text-decoration:none;">
        <i class="fas fa-file-excel" style="color:#1d6f42;"></i> Exporter Excel (CSV)
    </a>
    <a href="{{ route('gestionnaire.rapports.export.pdf', ['date' => $day->format('Y-m-d')]) }}" class="gv-btn-blue" style="background:#fff;color:#333!important;border:1px solid #ccc;text-decoration:none;">
        <i class="fas fa-file-pdf" style="color:#c00;"></i> Exporter PDF
    </a>
</div>
@endsection
