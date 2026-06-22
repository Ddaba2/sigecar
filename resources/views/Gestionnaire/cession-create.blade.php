@extends('layouts.gestionnaire')

@section('gestionnaire-content')
@php $fmt = fn ($n) => number_format((float) $n, 0, ',', ' '); @endphp

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
@if($errors->any())
    <div class="gv-alert gv-alert-error">{{ $errors->first() }}</div>
@endif

<div class="gv-section-head" style="margin-top:0;">
    <div class="gv-section-title">
        <i class="fas fa-clock-rotate-left"></i>
        HISTORIQUE DE CESSION
    </div>
    <a href="{{ route('gestionnaire.operations') }}" class="gv-btn-blue" style="background:#fff;color:var(--gv-blue)!important;border:1px solid var(--gv-blue);">
        Voir toutes les opérations <i class="fas fa-arrow-right"></i>
    </a>
</div>

<div class="gv-table-wrap" style="margin-bottom:32px;">
    <table class="gv-table">
        <thead>
            <tr>
                <th>DATE &amp; HEURE</th>
                <th>SOCIÉTÉ (cédant)</th>
                <th>PRODUIT</th>
                <th>VOLUME</th>
                <th>CUVE</th>
                <th>BÉNÉFICIAIRE</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentCessions as $c)
                <tr>
                    <td>{{ $c->date_cession->format('d/m/Y H:i') }}</td>
                    <td>{{ ($c->cedant && $c->cedant->isMarketeur()) ? $c->cedant->operatorName() : '—' }}</td>
                    <td style="text-transform:uppercase;">{{ $c->produit->nom ?? '—' }}</td>
                    <td>{{ $fmt($c->volume) }} L</td>
                    <td style="text-transform:uppercase;">{{ $c->cuve->nom ?? $c->cuve->code ?? '—' }}</td>
                    <td>{{ ($c->beneficiaire && $c->beneficiaire->isMarketeur()) ? $c->beneficiaire->operatorName() : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#6b7280;">Aucune cession récente.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<h2 class="gv-page-title" style="font-size:1.35rem;">Nouvel enregistrement</h2>

{{-- Point 10 : confirmation avant soumission --}}
<form method="POST" action="{{ route('gestionnaire.cession.store') }}" id="form-cession"
      onsubmit="return confirm('Confirmer le transfert de carburant entre les deux opérateurs ?');">
    @csrf
    <div class="gv-card">
        <div class="gv-card-header">
            <i class="fas fa-database"></i>
            Origine des produits
        </div>
        <div class="gv-form-grid">
            <div class="gv-field">
                <label>Date &amp; heure</label>
                <input type="datetime-local" name="date_cession" value="{{ old('date_cession', now()->format('Y-m-d\TH:i')) }}" required>
            </div>
            <div class="gv-field">
                <label>Type de produit</label>
                <select name="produit_id" id="ces-produit" required>
                    <option value="">Sélectionner</option>
                    @foreach($produits as $p)
                        <option value="{{ $p->id }}" @selected(old('produit_id') == $p->id)>{{ $p->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gv-field">
                <label>Cuve source</label>
                <select name="cuve_id" id="ces-cuve" required>
                    <option value="">Sélectionner</option>
                    @foreach($cuves as $c)
                        <option value="{{ $c->id }}" @selected(old('cuve_id') == $c->id)>
                            {{ $c->nom ?? $c->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="gv-field">
                <label>Stock disponible (opérateur cédant)</label>
                <div style="background:var(--gv-input);border-radius:8px;padding:12px 14px;">
                    <div style="height:10px;background:#e5e7eb;border-radius:5px;overflow:hidden;">
                        <div id="ces-stock-bar" style="height:100%;width:0%;background:#28a745;border-radius:5px;transition:width .2s;"></div>
                    </div>
                    <div id="ces-stock-lbl" style="margin-top:8px;font-weight:600;">— L</div>
                </div>
            </div>
        </div>
    </div>

    <div class="gv-card">
        <div class="gv-card-header">
            <i class="fas fa-file-contract"></i>
            Informations cession
        </div>
        <div class="gv-form-grid">
            <div class="gv-field">
                <label>Nom du cédant</label>
                <select name="cedant_id" id="ces-cedant" required>
                    <option value="">Ex : NDC</option>
                    @foreach($marketeurs as $m)
                        <option value="{{ $m->id }}" @selected(old('cedant_id') == $m->id)>{{ $m->operatorName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gv-field">
                <label>Nom du bénéficiaire</label>
                <select name="beneficiaire_id" required>
                    <option value="">Ex : Petro golf</option>
                    @foreach($marketeurs as $m)
                        <option value="{{ $m->id }}" @selected(old('beneficiaire_id') == $m->id)>{{ $m->operatorName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gv-field">
                <label>Quantité à transférer (L)</label>
                <input type="number" name="volume" value="{{ old('volume') }}" min="1" placeholder="30 000" required>
            </div>
        </div>
        <input type="hidden" name="temperature" value="15">
        <div class="gv-form-actions">
            <button type="submit" class="gv-btn-blue"><i class="fas fa-check"></i> Valider cession</button>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const cedantSel  = document.getElementById('ces-cedant');
    const produitSel = document.getElementById('ces-produit');
    const bar = document.getElementById('ces-stock-bar');
    const lbl = document.getElementById('ces-stock-lbl');

    // Point 1 : appel au stock logique de l'opérateur (et non au niveau physique de la cuve)
    function fetchOperatorStock() {
        const cedantId  = cedantSel ? cedantSel.value : '';
        const produitId = produitSel ? produitSel.value : '';

        if (!cedantId || !produitId) {
            bar.style.width = '0%';
            lbl.textContent = '— L';
            return;
        }

        fetch(`{{ url('/gestionnaire/api/operator-stock') }}/${cedantId}/${produitId}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            const qty = parseFloat(data.quantite) || 0;
            // Barre proportionnelle sur 50 000 L = 100 % (indicateur visuel)
            const pct = Math.min(100, Math.round(qty / 50000 * 100));
            bar.style.width = pct + '%';
            bar.style.background = qty > 0 ? '#28a745' : '#dc3545';
            lbl.textContent = new Intl.NumberFormat('fr-FR').format(Math.round(qty)) + ' L';
        })
        .catch(() => {
            bar.style.width = '0%';
            lbl.textContent = '— L';
        });
    }

    if (cedantSel)  cedantSel.addEventListener('change', fetchOperatorStock);
    if (produitSel) produitSel.addEventListener('change', fetchOperatorStock);

    // Chargement initial si des valeurs sont pré-sélectionnées (old())
    if (cedantSel?.value && produitSel?.value) fetchOperatorStock();
})();
</script>
@endpush
@endsection
