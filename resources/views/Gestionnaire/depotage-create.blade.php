@extends('layouts.gestionnaire')

@section('gestionnaire-content')
<p class="gv-breadcrumb">OPÉRATIONS / ENREGISTREMENT DÉPOTAGE</p>
<h1 class="gv-page-title">Formulaire de Réception</h1>
<p class="gv-page-sub">Veuillez renseigner les données pour la mise sous douane du produit pétrolier réceptionné.</p>

@if(session('success'))
    <div class="gv-alert gv-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="gv-alert gv-alert-error">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="gv-alert gv-alert-error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('gestionnaire.depotage.store') }}" id="form-depotage">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        <div class="gv-card" style="margin-bottom:0;">
            <div class="gv-card-header" style="color:var(--gv-blue);text-transform:uppercase;">
                <i class="fas fa-circle-info"></i>
                Détails de l'opération
            </div>
            <div class="gv-form-grid">
                <div class="gv-field">
                    <label>Date &amp; heure</label>
                    <input type="datetime-local" name="date_operation" value="{{ old('date_operation', now()->format('Y-m-d\TH:i')) }}" required>
                </div>
                <div class="gv-field">
                    <label>Produit</label>
                    <select name="produit_id" id="dep-produit" required>
                        <option value="">Choisir un produit</option>
                        @foreach($produits as $p)
                            <option value="{{ $p->id }}" @selected(old('produit_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="gv-field">
                    <label>Cuves de destination</label>
                    <select name="cuve_destination_id" id="dep-cuve" required>
                        <option value="">Choisir la cuve</option>
                        @foreach($cuves as $c)
                            <option value="{{ $c->id }}" data-niveau="{{ $c->niveau_actuel }}" data-capacite="{{ $c->capacite_totale }}" @selected(old('cuve_destination_id') == $c->id)>
                                {{ $c->nom ?? $c->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="gv-field">
                    <label>Propriétaire (fournisseur)</label>
                    <input type="text" name="fournisseur" value="{{ old('fournisseur') }}" placeholder="Sélectionner ou saisir" required>
                </div>
                <div class="gv-field">
                    <label>Provenance</label>
                    <input type="text" name="provenance" value="{{ old('provenance') }}" placeholder="EX : Niger" required>
                </div>
                <div class="gv-field">
                    <label>Numéro du bon de chargement</label>
                    <input type="text" name="numero_bon_chargement" value="{{ old('numero_bon_chargement') }}" placeholder="PO-2023-XXXX">
                </div>
                <div class="gv-field">
                    <label>Volume brut (L)</label>
                    <input type="number" name="volume_brut" id="dep-vb" value="{{ old('volume_brut') }}" min="1" required>
                </div>
                <div class="gv-field">
                    <label>Température (°C)</label>
                    <input type="number" step="0.1" name="temperature" id="dep-temp" value="{{ old('temperature', '15') }}" required>
                </div>
            </div>
        </div>

        <div class="gv-card-navy" style="margin-bottom:0;">
            <div class="gv-card-header"><span><i class="fas fa-truck" style="margin-right:8px;"></i>TRANSPORT</span></div>
            <div class="gv-form-grid">
                <div class="gv-field">
                    <label>Plaque d'immatriculation</label>
                    <input type="text" name="plaque_imm" value="{{ old('plaque_imm') }}" placeholder="AA-0000-XX" required>
                </div>
                <div class="gv-field">
                    <label>Nombre de creux</label>
                    <input type="number" min="1" value="1" id="dep-nb-creux" placeholder="EX : 4">
                </div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        <div class="gv-card" style="margin-bottom:0;">
            <div class="gv-card-header" style="color:var(--gv-blue);text-transform:uppercase;">
                <i class="fas fa-circle-info"></i>
                Détails du creux
            </div>
            <div id="creux-rows">
                <div class="gv-form-grid creux-row" style="margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
                    <div class="gv-field">
                        <label>Numéro de creux</label>
                        <input type="text" name="creux[0][numero]" value="{{ old('creux.0.numero', '1') }}" placeholder="EX : 1" required>
                    </div>
                    <div class="gv-field">
                        <label>Capacité</label>
                        <input type="number" name="creux[0][capacite]" value="{{ old('creux.0.capacite') }}" min="1" placeholder="EX : 9000">
                    </div>
                    <div class="gv-field" style="grid-column:1/-1;">
                        <label>Produit</label>
                        <select name="creux[0][produit_id]" class="creux-produit">
                            <option value="">Même que l'opération</option>
                            @foreach($produits as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
                <strong>Volume total : <span id="creux-total">—</span> L</strong>
                <button type="button" class="gv-btn-blue" style="padding:8px 14px;font-size:0.8rem;background:#fff;color:var(--gv-blue)!important;border:1px solid var(--gv-blue);" id="add-creux">
                    <i class="fas fa-plus"></i> Ajouter un autre creux
                </button>
            </div>
        </div>

        <div class="gv-card" style="margin-bottom:0;">
            <div class="gv-card-header" style="color:#111;text-transform:uppercase;">
                <i class="fas fa-user"></i>
                <span style="border-bottom:1px solid #ccc;padding-bottom:6px;">Chauffeur</span>
            </div>
            <div class="gv-form-grid">
                <div class="gv-field" style="grid-column:1/-1;">
                    <label>Nom complet</label>
                    <input type="text" name="chauffeur_nom" value="{{ old('chauffeur_nom') }}" placeholder="EX : Moussa DIARRA" required>
                </div>
                <div class="gv-field">
                    <label>Téléphone</label>
                    <input type="text" name="chauffeur_tel" value="{{ old('chauffeur_tel') }}" placeholder="(+223) 79 00 00 00">
                </div>
                <div class="gv-field">
                    <label>Numéro de permis</label>
                    <input type="text" name="chauffeur_permis" value="{{ old('chauffeur_permis') }}" required>
                </div>
                <div class="gv-field">
                    <label>Déclaration douane</label>
                    <input type="text" name="declaration_douane" value="{{ old('declaration_douane') }}" placeholder="DC-992-K8">
                </div>
                <div class="gv-field">
                    <label>Bureau douane</label>
                    <input type="text" name="bureau_douane" value="{{ old('bureau_douane') }}" placeholder="Faladiè">
                </div>
            </div>
        </div>
    </div>

    <div class="gv-form-actions" style="justify-content:space-between;">
        <a href="{{ route('gestionnaire.operations') }}" class="gv-btn-red"><i class="fas fa-xmark"></i> Annuler</a>
        <button type="submit" class="gv-btn-blue"><i class="fas fa-check"></i> Valider &amp; Générer PDF</button>
    </div>
</form>

@push('scripts')
<script>
(function () {
    let creuxIndex = 1;
    const container = document.getElementById('creux-rows');
    const form = document.getElementById('form-depotage');

    document.getElementById('add-creux').addEventListener('click', function () {
        const div = document.createElement('div');
        div.className = 'gv-form-grid creux-row';
        div.style.cssText = 'margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #e5e7eb;';
        div.innerHTML = `
            <div class="gv-field">
                <label>Numéro de creux</label>
                <input type="text" name="creux[${creuxIndex}][numero]" value="${creuxIndex + 1}" required>
            </div>
            <div class="gv-field">
                <label>Capacité</label>
                <input type="number" name="creux[${creuxIndex}][capacite]" min="1" placeholder="EX : 9000" required>
            </div>
            <div class="gv-field" style="grid-column:1/-1;">
                <label>Produit</label>
                <select name="creux[${creuxIndex}][produit_id]" class="creux-produit">
                    <option value="">Même que l'opération</option>
                    @foreach($produits as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>`;
        container.appendChild(div);
        creuxIndex++;
    });

    form.addEventListener('submit', function () {
        const vb = document.getElementById('dep-vb').value;
        document.querySelectorAll('.creux-row').forEach(function (row) {
            const cap = row.querySelector('input[name*="[capacite]"]');
            if (cap && !cap.value && vb) cap.value = vb;
            const sel = row.querySelector('.creux-produit');
            if (sel && !sel.value) sel.removeAttribute('name');
        });
    });
})();
</script>
@endpush
@endsection
