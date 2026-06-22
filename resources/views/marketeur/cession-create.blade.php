@extends('layouts.marketeur')

@section('marketeur-content')
@php $fmt = fn ($n) => number_format((float) $n, 0, ',', ' '); @endphp

@if(session('success'))
    <div class="gv-alert gv-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="gv-alert gv-alert-error">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="gv-alert gv-alert-error">{{ $errors->first() }}</div>
@endif

<p class="gv-breadcrumb">Gestion des flux</p>
<h1 class="gv-page-title gv-page-title-serif">Nouvelle Enregistrement</h1>
<p class="gv-page-sub">Saisie des informations decession, de propriété et stock acquitté.</p>

{{-- Point 10 : confirmation avant soumission --}}
<form method="POST" action="{{ route('marketeur.cession.store') }}"
      onsubmit="return confirm('Confirmer le transfert ? Cette action est irréversible.');">

    @csrf
    <div style="background:#fff;border-radius:14px;box-shadow:0 4px 24px rgba(0, 27, 51, 0.08);padding:32px 36px 36px;max-width:920px;">
        <!-- SECTION ORIGINE DES PRODUITS -->
        <div style="display:flex; justify-content:center; align-items:center; gap:20px; margin-bottom:30px;">
             <img src="https://img.icons8.com/isometric/50/petrol-station.png" alt="station" style="width:40px; height:40px;">
             <h3 style="font-family:var(--gv-serif-display); font-size:1.6rem; margin:0; color:#0f172a;">Origine des produits</h3>
        </div>

        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:24px 32px;">
            <div>
                <label style="display:block;font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:8px;">DATE &amp; HEURE</label>
                <div style="position:relative;">
                    <input type="datetime-local" name="date_cession" id="date_cession" value="{{ old('date_cession', now()->format('Y-m-d\TH:i')) }}" required 
                        style="width:100%;padding:14px 16px;border:0;border-radius:10px;background:#eef2ff;font-size:0.95rem;font-family:var(--gv-sans);color:#1e293b;">
                </div>
            </div>
            <div>
                <label style="display:block;font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:8px;">TYPE DE PRODUIT</label>
                <select name="produit_id" required 
                    style="width:100%;padding:14px 16px;border:0;border-radius:10px;background:#eef2ff;font-size:0.95rem;font-family:var(--gv-sans);color:#1e293b;appearance:none;">
                    <option value="">GASOIL</option>
                    @foreach($produits as $p)
                        <option value="{{ $p->id }}" @selected(old('produit_id') == $p->id)>{{ strtoupper($p->nom) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block;font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:8px;">CUVE SOURCE</label>
                <select name="cuve_id" id="cuve_id_select" required 
                    style="width:100%;padding:14px 16px;border:0;border-radius:10px;background:#eef2ff;font-size:0.95rem;font-family:var(--gv-sans);color:#1e293b;appearance:none;">
                    <option value="">Sélectionner</option>
                    @foreach($cuves as $c)
                        <option value="{{ $c->id }}" data-nom="{{ $c->nom ?? $c->code }}" @selected(old('cuve_id') == $c->id)>{{ strtoupper($c->nom ?? $c->code) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block;font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:8px;">STOCK DISPONIBLE</label>
                <div style="background:#eef2ff; border-radius:10px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
                    <div style="flex:1; height:10px; background:#fff; border-radius:5px; overflow:hidden;">
                        <div id="stock_bar" style="height:100%; width:0%; background:#10b981; border-radius:5px; transition:width 0.3s ease;"></div>
                    </div>
                    <span id="stock_text" style="font-size:0.85rem; font-weight:700; color:#334155; white-space:nowrap;">0,000L</span>
                </div>
            </div>
        </div>

        <div style="height:2px;background:#e5e7eb;margin:40px 0;"></div>

        <!-- SECTION INFORMATIONS CESSION -->
        <div style="display:flex; justify-content:center; align-items:center; gap:20px; margin-bottom:30px;">
             <img src="https://img.icons8.com/isometric/50/contract.png" alt="contract" style="width:40px; height:40px;">
             <h3 style="font-family:var(--gv-serif-display); font-size:1.6rem; margin:0; color:#0f172a;">Informations Cession</h3>
        </div>

        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:24px 32px;">
            <div>
                <label style="display:block;font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:8px;">NOM DU BENEFICIAIRE</label>
                <select name="beneficiaire_id" required 
                    style="width:100%;padding:14px 16px;border:0;border-radius:10px;background:#eef2ff;font-size:0.95rem;font-family:var(--gv-sans);color:#1e293b;appearance:none;">
                    <option value="">Ex: Petro golf</option>
                    @foreach($marketeurs as $m)
                        <option value="{{ $m->id }}" @selected(old('beneficiaire_id') == $m->id)>{{ $m->operatorName() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block;font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:8px;">CONTACT DU BENEFICIAIRE</label>
                <input type="text" placeholder="Ex: Petro golf" 
                    style="width:100%;padding:14px 16px;border:0;border-radius:10px;background:#eef2ff;font-size:0.95rem;font-family:var(--gv-sans);color:#1e293b;">
            </div>
            <div style="grid-column: 1 / -1;">
                <label style="display:block;font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:8px; text-align:center;">QUANTITE A TRANSFERE</label>
                <input type="number" name="volume" value="{{ old('volume') }}" min="1" placeholder="Ex: 30,000L" required 
                    style="width:100%;padding:14px 16px;border:0;border-radius:10px;background:#eef2ff;font-size:1rem;font-family:var(--gv-sans);color:#1e293b;text-align:center;">
            </div>
        </div>

        <input type="hidden" name="temperature" value="15">

        <div style="margin-top:40px;display:flex;justify-content:center;">
            <button type="submit" style="display:inline-flex;align-items:center;justify-content:center;gap:12px;padding:16px 64px;border:0;border-radius:12px;background:var(--gv-blue);color:#fff;font-size:1.1rem;font-weight:700;cursor:pointer;font-family:var(--gv-sans);box-shadow:0 10px 20px rgba(0, 123, 189, 0.2);">
                <span style="width:24px;height:24px;border-radius:50%;border:2px solid #fff;display:inline-flex;align-items:center;justify-content:center;font-size:0.75rem;"><i class="fas fa-check"></i></span>
                Valider Cession
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cuveSelect = document.getElementById('cuve_id_select');
    const produitSelect = document.querySelector('select[name="produit_id"]');
    const stockBar = document.getElementById('stock_bar');
    const stockText = document.getElementById('stock_text');

    function updateStockDisplay(qty, capacite) {
        const cap = capacite || 1;
        const pct = cap > 0 ? (qty / cap * 100) : 0;
        stockBar.style.width = Math.min(100, pct) + '%';
        stockText.innerText = new Intl.NumberFormat('fr-FR').format(qty) + 'L';
    }

    function loadOperatorStock(produitId, cuveId) {
        if (!produitId) {
            updateStockDisplay(0, 1);
            return;
        }
        fetch(`{{ url('/marketeur/api/stock-produit') }}/${produitId}`, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                if (cuveId) {
                    fetch(`{{ url('/marketeur/api/cuve-stock') }}/${cuveId}`, { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(cuve => updateStockDisplay(data.quantite || 0, cuve.capacite_totale));
                } else {
                    updateStockDisplay(data.quantite || 0, data.quantite || 1);
                }
            });
    }

    cuveSelect.addEventListener('change', function() {
        loadOperatorStock(produitSelect.value, this.value);
    });

    produitSelect.addEventListener('change', function() {
        loadOperatorStock(this.value, cuveSelect.value);
    });

    if (produitSelect.value) {
        loadOperatorStock(produitSelect.value, cuveSelect.value);
    }
});
</script>
@endsection

