@extends('layouts.marketeur')

@section('marketeur-content')
@php $fmt = fn ($n) => number_format((float) $n, 0, ',', ' '); @endphp

<p class="gv-breadcrumb">Gestion des flux</p>
<div class="gv-section-head" style="margin-top:0;">
    <h1 class="gv-page-title">Détail de la cession</h1>
    <a href="{{ route('marketeur.cessions') }}" class="gv-btn-blue gv-btn-outline"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<div style="background:#fff;border-radius:14px;padding:28px 32px;box-shadow:0 2px 12px rgba(0,27,51,0.06);max-width:900px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
        <div>
            <div style="font-size:0.72rem;font-weight:700;color:#6b7280;letter-spacing:0.08em;">N° CESSION</div>
            <div style="font-size:1.2rem;font-weight:700;margin-top:6px;">{{ $cession->numero_cession ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:0.72rem;font-weight:700;color:#6b7280;letter-spacing:0.08em;">DATE</div>
            <div style="margin-top:6px;">{{ $cession->date_cession->format('d M Y H:i') }}</div>
        </div>
        <div>
            <div style="font-size:0.72rem;font-weight:700;color:#6b7280;letter-spacing:0.08em;">CÉDANT</div>
            <div style="margin-top:6px;">{{ $cession->cedant->company_name ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:0.72rem;font-weight:700;color:#6b7280;letter-spacing:0.08em;">BÉNÉFICIAIRE</div>
            <div style="margin-top:6px;font-weight:700;">{{ $cession->beneficiaire->company_name ?? '—' }}</div>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <div style="background:#eef2ff;border-radius:10px;padding:16px;">
            <div style="font-size:0.72rem;font-weight:700;color:#6b7280;">PRODUIT</div>
            <div style="margin-top:8px;"><span class="mk-prod-pill">{{ $cession->produit->name ?? '—' }}</span></div>
        </div>
        <div style="background:#eef2ff;border-radius:10px;padding:16px;">
            <div style="font-size:0.72rem;font-weight:700;color:#6b7280;">VOLUME</div>
            <div style="margin-top:8px;font-weight:700;">{{ $fmt($cession->volume) }} L</div>
        </div>
        <div style="background:#eef2ff;border-radius:10px;padding:16px;">
            <div style="font-size:0.72rem;font-weight:700;color:#6b7280;">CUVE</div>
            <div style="margin-top:8px;">{{ $cession->cuve->nom ?? $cession->cuve->code ?? '—' }}</div>
        </div>
    </div>
</div>
@endsection
