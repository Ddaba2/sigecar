@extends('layouts.gestionnaire')

@section('gestionnaire-content')
@php $fmt = fn ($n) => number_format((float) $n, 0, ',', ' '); @endphp

@if(session('success'))
    <div class="gv-alert gv-alert-success">{{ session('success') }}</div>
@endif

<h1 class="gv-page-title">Historique des opérations</h1>
<p class="gv-page-sub">Dépotages, chargements et cessions enregistrés dans le système.</p>

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
                    <td style="text-transform:uppercase;">{{ $c->produit->nom ?? '—' }}</td>
                    <td>{{ $fmt($c->volume) }}</td>
                    <td>{{ $c->cuve->nom ?? $c->cuve->code ?? '—' }}</td>
                    <td>{{ $c->beneficiaire->company_name ?? '—' }}</td>
                    <td>
                        @if($c->status === 'pending')
                            <span class="gv-badge pending">En attente</span>
                        @elseif($c->status === 'completed')
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
@endsection
