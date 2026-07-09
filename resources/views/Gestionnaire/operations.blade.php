@extends('layouts.gestionnaire')

@section('gestionnaire-content')
@php $fmt = fn ($n) => number_format((float) $n, 0, ',', ' '); @endphp

@if(session('success'))
    <div class="gv-alert gv-alert-success">{{ session('success') }}</div>
@endif

<h1 class="gv-page-title">Historique des opérations</h1>
<p class="gv-page-sub">Dépotages, chargements et cessions enregistrés dans le système.</p>

{{-- Barre de filtres --}}
<form method="GET" action="{{ route('gestionnaire.operations') }}" class="gv-filter-bar">
    <div class="gv-filter-group">
        <label class="gv-filter-label">Type</label>
        <div class="gv-filter-types">
            @foreach(['tous' => 'Tous', 'depotage' => 'Dépotages', 'chargement' => 'Chargements', 'cession' => 'Cessions'] as $val => $label)
                <label class="gv-type-chip {{ $type === $val ? 'active' : '' }}">
                    <input type="radio" name="type" value="{{ $val }}" {{ $type === $val ? 'checked' : '' }} hidden>
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </div>
    <div class="gv-filter-group">
        <label class="gv-filter-label" for="date_debut">Du</label>
        <input type="date" id="date_debut" name="date_debut" class="gv-filter-input" value="{{ $dateDebut ?? '' }}">
    </div>
    <div class="gv-filter-group">
        <label class="gv-filter-label" for="date_fin">Au</label>
        <input type="date" id="date_fin" name="date_fin" class="gv-filter-input" value="{{ $dateFin ?? '' }}">
    </div>
    <div class="gv-filter-actions">
        <button type="submit" class="gv-btn-blue" style="padding:7px 18px;">Filtrer</button>
        @if($type !== 'tous' || $dateDebut || $dateFin)
            <a href="{{ route('gestionnaire.operations') }}" class="gv-btn-reset">Réinitialiser</a>
        @endif
    </div>
</form>
<style>
.gv-filter-bar{display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;margin:18px 0 24px;}
.gv-filter-group{display:flex;flex-direction:column;gap:4px;}
.gv-filter-label{font-size:.74rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;}
.gv-filter-types{display:flex;gap:6px;flex-wrap:wrap;}
.gv-type-chip{padding:5px 13px;border-radius:20px;font-size:.82rem;font-weight:500;cursor:pointer;border:1.5px solid #d1d5db;background:#fff;color:#374151;transition:all .15s;}
.gv-type-chip:hover{border-color:#6366f1;color:#6366f1;}
.gv-type-chip.active{background:#6366f1;border-color:#6366f1;color:#fff;}
.gv-filter-input{height:34px;padding:0 10px;border:1.5px solid #d1d5db;border-radius:7px;font-size:.87rem;color:#374151;background:#fff;}
.gv-filter-input:focus{outline:none;border-color:#6366f1;}
.gv-filter-actions{display:flex;align-items:center;gap:10px;padding-top:16px;}
.gv-btn-reset{font-size:.82rem;color:#6b7280;text-decoration:underline;}
</style>
<script>
document.querySelectorAll('.gv-type-chip input[type=radio]').forEach(radio => {
    radio.closest('.gv-type-chip').addEventListener('click', function() {
        document.querySelectorAll('.gv-type-chip').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input').checked = true;
        this.closest('form').submit();
    });
});
</script>

@if($type === 'tous' || $type === 'depotage')
<div class="gv-section-title" style="margin:24px 0 12px;">Dépotages</div>
<div class="gv-table-wrap">
    <table class="gv-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>N°</th>
                <th>Fournisseur</th>
                <th>Produit</th>
                <th>Volume (L)</th>
                <th>Cuve</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($depotages as $d)
                <tr>
                    <td>{{ $d->date_operation->format('d/m/Y H:i') }}</td>
                    <td>{{ $d->numero_depotage }}</td>
                    <td>{{ $d->fournisseur }}</td>
                    <td style="text-transform:uppercase;">{{ $d->produit->nom ?? '—' }}</td>
                    <td>{{ $fmt($d->volume_brut) }}</td>
                    <td>{{ $d->cuve->nom ?? '—' }}</td>
                    <td>
                        @if($d->status === 'acquitte')
                            <span class="gv-badge ok">Acquitté</span>
                        @else
                            <span class="gv-badge pending">Sous douane</span>
                        @endif
                    </td>
                    <td>
                        @if($d->status === 'sous_douane')
                            <form method="POST" action="{{ route('gestionnaire.depotage.acquitter', $d) }}" style="display:inline;" onsubmit="return confirm('Marquer ce dépotage comme acquitté ?');">
                                @csrf
                                <button type="submit" class="gv-btn-blue" style="padding:6px 12px;font-size:0.78rem;">Acquitter</button>
                            </form>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#6b7280;">Aucun dépotage.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:12px;">{{ $depotages->links() }}</div>
@endif

@if($type === 'tous' || $type === 'chargement')
<div class="gv-section-title" style="margin:32px 0 12px;">Chargements</div>
<div class="gv-table-wrap">
    <table class="gv-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>N°</th>
                <th>Client</th>
                <th>Produit</th>
                <th>Volume (L)</th>
                <th>Cuve</th>
            </tr>
        </thead>
        <tbody>
            @forelse($chargements as $c)
                <tr>
                    <td>{{ $c->date_operation->format('d/m/Y H:i') }}</td>
                    <td>{{ $c->numero_chargement }}</td>
                    <td>{{ $c->client_nom }}</td>
                    <td style="text-transform:uppercase;">{{ $c->produit->nom ?? '—' }}</td>
                    <td>{{ $fmt($c->volume_brut) }}</td>
                    <td>{{ $c->cuve->nom ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#6b7280;">Aucun chargement.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:12px;">{{ $chargements->links() }}</div>
@endif

@if($type === 'tous' || $type === 'cession')
<div class="gv-section-title" style="margin:32px 0 12px;">Cessions</div>
<div class="gv-table-wrap">
    <table class="gv-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>N°</th>
                <th>Cédant</th>
                <th>Produit</th>
                <th>Volume (L)</th>
                <th>Cuve</th>
                <th>Bénéficiaire</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cessions as $c)
                <tr>
                    <td>{{ $c->date_cession->format('d/m/Y H:i') }}</td>
                    <td>{{ $c->numero_cession }}</td>
                    <td>{{ $c->cedant->company_name ?? '—' }}</td>
                    <td style="text-transform:uppercase;">{{ $c->produit->name ?? '—' }}</td>
                    <td>{{ $fmt($c->volume) }}</td>
                    <td style="text-transform:uppercase;">{{ $c->cuve->nom ?? $c->cuve->code ?? '—' }}</td>
                    <td>{{ $c->beneficiaire->company_name ?? '—' }}</td>
                    <td>
                        @if($c->status === 'pending')
                            <span class="gv-badge pending">En attente</span>
                        @elseif($c->status === 'completed' || $c->status === 'confirmed')
                            <span class="gv-badge ok">Complété</span>
                        @elseif($c->status === 'cancelled')
                            <span class="gv-badge" style="background:#6b7280;color:#fff;">Annulé</span>
                        @else
                            <span class="gv-badge pending">{{ ucfirst($c->status) }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#6b7280;">Aucune cession.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:12px;">{{ $cessions->links() }}</div>
@endif
@endsection
