@extends('layouts.marketeur')

@section('marketeur-content')
@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $fmtShort = fn ($n) => $n >= 1_000 ? round($n / 1_000) . 'K' : $fmt($n);
@endphp

@if(session('success'))
    <div class="gv-alert gv-alert-success">{{ session('success') }}</div>
@endif
@if(session('warning'))
    <div class="gv-alert gv-alert-error" style="background:#fffbeb;border-left-color:#f59e0b;color:#92400e;">
        <i class="fas fa-triangle-exclamation" style="margin-right:6px;"></i>{{ session('warning') }}
    </div>
@endif
@if(session('error'))
    <div class="gv-alert gv-alert-error">{{ session('error') }}</div>
@endif

<div class="gv-section-head" style="margin-top:0;align-items:flex-start;">
    <div>
        <p class="gv-breadcrumb">Gestion des flux</p>
        <h1 class="gv-page-title">Mes Cessions</h1>
    </div>
    <div class="mk-page-actions">
        {{-- Point 3 : label corrigé CSV --}}
        <a href="{{ route('marketeur.cessions.export', request()->query()) }}" class="gv-btn-blue"><i class="fas fa-file-csv"></i> Télécharger CSV</a>
    </div>
</div>

{{-- ── Cessions ÉMISES ── --}}
<h2 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:.06em;">
    <i class="fas fa-paper-plane" style="color:var(--gv-blue);margin-right:6px;"></i>Cessions émises
</h2>

<div class="gv-table-wrap">
    <table class="gv-table">
        <thead>
            <tr>
                <th>DATE &amp; HEURE</th>
                <th>PRODUIT</th>
                <th>VOLUME</th>
                <th>BÉNÉFICIAIRE</th>
                <th>DOCUMENT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cessions as $c)
                <tr>
                    <td>{{ $c->date_cession->format('d M Y H:i') }}</td>
                    {{-- Point 2 : utilisation de ->nom --}}
                    <td><span class="mk-prod-pill">{{ $c->produit->nom ?? '—' }}</span></td>
                    <td><strong>{{ $fmt($c->volume) }} L</strong></td>
                    <td><strong>{{ $c->beneficiaire ? $c->beneficiaire->operatorName() : '—' }}</strong></td>
                    <td>
                        @if($c->document_pdf)
                            <a href="{{ route('marketeur.document.download', ['type' => 'cession', 'id' => $c->id]) }}"
                               class="gv-btn-blue" style="padding:4px 10px;font-size:0.8rem;">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        @else
                            {{-- Point 12 : bouton régénérer si pas de PDF --}}
                            <a href="{{ route('marketeur.document.regenerate', ['type' => 'cession', 'id' => $c->id]) }}"
                               class="gv-btn-blue" style="padding:4px 10px;font-size:0.8rem;background:#6b7280;">
                                <i class="fas fa-rotate"></i> Générer
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#6b7280;">Aucune cession émise.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:12px;">{{ $cessions->links() }}</div>

{{-- ── Cessions REÇUES (point 4) ── --}}
<h2 style="font-size:1rem;font-weight:700;color:#0f172a;margin:28px 0 10px;text-transform:uppercase;letter-spacing:.06em;">
    <i class="fas fa-inbox" style="color:#10b981;margin-right:6px;"></i>Cessions reçues
</h2>

<div class="gv-table-wrap">
    <table class="gv-table">
        <thead>
            <tr>
                <th>DATE &amp; HEURE</th>
                <th>PRODUIT</th>
                <th>VOLUME</th>
                <th>CÉDANT</th>
                <th>DOCUMENT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cessionsRecues as $c)
                <tr>
                    <td>{{ $c->date_cession->format('d M Y H:i') }}</td>
                    <td><span class="mk-prod-pill">{{ $c->produit->nom ?? '—' }}</span></td>
                    <td><strong>{{ $fmt($c->volume) }} L</strong></td>
                    <td><strong>{{ $c->cedant ? $c->cedant->operatorName() : '—' }}</strong></td>
                    <td>
                        @if($c->document_pdf)
                            <a href="{{ route('marketeur.document.download', ['type' => 'cession', 'id' => $c->id]) }}"
                               class="gv-btn-blue" style="padding:4px 10px;font-size:0.8rem;">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        @else
                            <a href="{{ route('marketeur.document.regenerate', ['type' => 'cession', 'id' => $c->id]) }}"
                               class="gv-btn-blue" style="padding:4px 10px;font-size:0.8rem;background:#6b7280;">
                                <i class="fas fa-rotate"></i> Générer
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#6b7280;">Aucune cession reçue.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:12px;">{{ $cessionsRecues->links() }}</div>

{{-- ── Cartes récapitulatives ── --}}
<div class="mk-cession-grid" style="margin-top:28px;">
    <div class="mk-cession-card dark">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <i class="fas fa-paper-plane"></i>
            <span style="font-size:0.8rem;font-weight:700;letter-spacing:0.06em;">TOTAL TRANSFÉRÉ (MOIS)</span>
        </div>
        <div style="font-size:2.2rem;font-weight:800;">{{ $fmtShort($totalTransfereMois) }} Litres</div>
    </div>
    <div class="mk-cession-card light">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <i class="fas fa-inbox"></i>
            <span style="font-size:0.8rem;font-weight:700;letter-spacing:0.06em;">TOTAL REÇUS (MOIS)</span>
        </div>
        <div style="font-size:2.2rem;font-weight:800;">{{ $fmtShort($totalRecuMois) }} Litres</div>
    </div>
</div>
@endsection
