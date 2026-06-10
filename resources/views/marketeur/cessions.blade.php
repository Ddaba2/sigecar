@extends('layouts.marketeur')

@section('marketeur-content')
@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $fmtShort = fn ($n) => $n >= 1_000 ? round($n / 1_000) . 'K' : $fmt($n);
@endphp

@if(session('success'))
    <div class="gv-alert gv-alert-success">{{ session('success') }}</div>
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
        <a href="#" class="gv-btn-blue gv-btn-green" onclick="window.print();return false;"><i class="fas fa-download"></i> Télécharger PDF</a>
        <a href="{{ route('marketeur.cessions', request()->query()) }}" class="gv-btn-blue"><i class="fas fa-file-excel"></i> Télécharger .xlsx</a>
    </div>
</div>

<form method="GET" action="{{ route('marketeur.cessions') }}" class="mk-filter-bar">
    <div class="mk-filter-field">
        <label>PERIODE</label>
        <select name="periode">
            <option value="">Toutes</option>
            <option value="7" @selected(request('periode') == '7')>7 derniers jours</option>
            <option value="30" @selected(request('periode') == '30')>30 derniers jours</option>
            <option value="90" @selected(request('periode') == '90')>90 derniers jours</option>
        </select>
    </div>
    <div class="mk-filter-field">
        <label>TYPE D'OPERATION</label>
        <select name="type" disabled>
            <option>Transfert</option>
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
                <th>VOLUME</th>
                <th>BÉNÉFICIAIRE</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cessions as $c)
                <tr>
                    <td>{{ $c->date_cession->format('d M Y H:i') }}</td>
                    <td><i class="fas fa-paper-plane" style="color:var(--gv-blue);margin-right:6px;"></i>Transfert</td>
                    <td><span class="mk-prod-pill">{{ $c->produit->name ?? '—' }}</span></td>
                    <td><strong>{{ $fmt($c->volume) }}L</strong></td>
                    <td><strong>{{ $c->beneficiaire->company_name ?? '—' }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#6b7280;">Aucune cession enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:12px;">{{ $cessions->links() }}</div>

<div class="mk-cession-grid">
    <div class="mk-cession-card dark">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <i class="fas fa-paper-plane"></i>
            <span style="font-size:0.8rem;font-weight:700;letter-spacing:0.06em;">TOTAL TRANSFÉRER (MOIS)</span>
        </div>
        <div style="font-size:2.2rem;font-weight:800;">{{ $fmtShort($totalTransfereMois) }} Litres</div>
    </div>
    <div class="mk-cession-card light">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <i class="fas fa-paper-plane"></i>
            <span style="font-size:0.8rem;font-weight:700;letter-spacing:0.06em;">TOTAL REÇUS (MOIS)</span>
        </div>
        <div style="font-size:2.2rem;font-weight:800;">{{ $fmtShort($totalRecuMois) }} Litres</div>
    </div>
</div>
@endsection
