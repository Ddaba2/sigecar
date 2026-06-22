@extends('layouts.marketeur')

@section('marketeur-content')
@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    use App\Enums\OperationStatus;
@endphp

<div class="gv-section-head" style="margin-top:0;align-items:flex-start;">
    <div>
        <p class="gv-breadcrumb">Gestion des flux</p>
        <h1 class="gv-page-title">Mes Opérations</h1>
    </div>
    <div class="mk-page-actions">
        {{-- Point 3 : label corrigé CSV --}}
        <a href="{{ route('marketeur.operations.export', request()->query()) }}" class="gv-btn-blue"><i class="fas fa-file-csv"></i> Télécharger CSV</a>
    </div>
</div>

{{-- Point 5 : formulaire de filtres --}}
<form method="GET" action="{{ route('marketeur.operations') }}"
      style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 18px;margin-bottom:18px;">
    <div style="display:flex;flex-direction:column;gap:4px;">
        <label style="font-size:0.78rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Période</label>
        <select name="periode" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:0.9rem;background:#fff;">
            <option value="">Toutes</option>
            <option value="7"  @selected(request('periode') == '7')>7 derniers jours</option>
            <option value="30" @selected(request('periode') == '30')>30 derniers jours</option>
            <option value="90" @selected(request('periode') == '90')>90 derniers jours</option>
        </select>
    </div>
    <div style="display:flex;flex-direction:column;gap:4px;">
        <label style="font-size:0.78rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Produit</label>
        <select name="produit_id" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:0.9rem;background:#fff;">
            <option value="">Tous</option>
            @foreach($produits as $p)
                <option value="{{ $p->id }}" @selected(request('produit_id') == $p->id)>{{ $p->nom }}</option>
            @endforeach
        </select>
    </div>
    <div style="display:flex;flex-direction:column;gap:4px;">
        <label style="font-size:0.78rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Type</label>
        <select name="type" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:0.9rem;background:#fff;">
            <option value="tous" @selected(request('type', 'tous') == 'tous')>Tous les types</option>
            <option value="depotage"   @selected(request('type') == 'depotage')>Dépotages</option>
            <option value="chargement" @selected(request('type') == 'chargement')>Chargements</option>
            <option value="cession"    @selected(request('type') == 'cession')>Cessions</option>
        </select>
    </div>
    <button type="submit" class="gv-btn-blue" style="padding:9px 18px;height:fit-content;align-self:flex-end;">
        <i class="fas fa-filter"></i> Filtrer
    </button>
    @if(request()->hasAny(['periode','produit_id','type']))
        <a href="{{ route('marketeur.operations') }}" class="gv-btn-blue"
           style="padding:9px 18px;height:fit-content;align-self:flex-end;background:#6b7280;">
            <i class="fas fa-xmark"></i> Réinitialiser
        </a>
    @endif
</form>

<div class="gv-table-wrap">
    <table class="gv-table">
        <thead>
            <tr>
                <th>DATE &amp; HEURE</th>
                <th>TYPE</th>
                <th>PRODUIT</th>
                <th>VOLUME BRUT</th>
                <th>VOL. CORRIGÉ 15°C</th>
                <th>STATUT</th>
                <th>DOCUMENT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($operations as $op)
                <tr>
                    <td>{{ $op['date']?->format('d M Y H:i') ?? '—' }}</td>
                    <td><i class="fas {{ $op['type_icon'] }}" style="color:var(--gv-blue);margin-right:6px;"></i>{{ $op['type'] }}</td>
                    <td><span class="mk-prod-pill">{{ $op['produit'] }}</span></td>
                    <td><strong>{{ $fmt($op['volume_brut']) }} L</strong></td>
                    <td>{{ $fmt($op['volume_corrige']) }} L</td>
                    <td>
                        {{-- Point 7 : utilisation de l'enum pour les comparaisons --}}
                        @php
                            $statusEnum = \App\Enums\OperationStatus::tryFrom($op['status'] ?? '');
                        @endphp
                        @if($statusEnum && $statusEnum->isAcquitte())
                            <span class="mk-status ok">Acquitté</span>
                        @elseif($statusEnum === \App\Enums\OperationStatus::SousDouane)
                            <span class="mk-status warn">Sous douane</span>
                        @else
                            <span class="mk-status pending">{{ ucfirst($op['status'] ?? 'En cours') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($op['has_pdf'] ?? false)
                            <a href="{{ route('marketeur.document.download', ['type' => $op['doc_type'], 'id' => $op['doc_id']]) }}"
                               class="gv-btn-blue" style="padding:4px 10px;font-size:0.8rem;">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:#6b7280;">Aucune opération trouvée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@php
    $fmtShort = fn ($n) => $n >= 1_000_000 ? round($n / 1_000_000, 1) . 'M' : ($n >= 1_000 ? round($n / 1_000) . 'K' : $fmt($n));
    // Point 6 : calcul réel du pourcentage sous douane
    $baseTotal = $sousDouaneActuel + $stockTotal;
    $sousPct = $baseTotal > 0 ? min(100, max(0, (int) round($sousDouaneActuel / $baseTotal * 100))) : 0;
@endphp

<div class="mk-summary-grid">
    <div class="mk-summary-card navy">
        <i class="fas fa-download corner-icon"></i>
        <div class="label">TOTAL DÉPOTAGES (MOIS)</div>
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
