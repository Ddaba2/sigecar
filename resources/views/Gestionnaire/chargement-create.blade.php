@extends('layouts.gestionnaire')

@section('gestionnaire-content')
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

<div class="gv-section-head" style="margin-top:0;">
    <div class="gv-section-title">
        <i class="fas fa-clock-rotate-left"></i>
        HISTORIQUE DE CHARGEMENT
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
                <th>CLIENT</th>
                <th>PRODUIT</th>
                <th>VOLUME</th>
                <th>CUVE</th>
                <th>N°</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentChargements as $c)
                <tr>
                    <td>{{ $c->date_operation->format('d/m/Y H:i') }}</td>
                    <td>{{ $c->client_nom }}</td>
                    <td style="text-transform:uppercase;">{{ $c->produit->name ?? '—' }}</td>
                    <td>{{ $fmt($c->volume_brut) }} L</td>
                    <td style="text-transform:uppercase;">{{ $c->cuve->nom ?? $c->cuve->code ?? '—' }}</td>
                    <td>{{ $c->numero_chargement }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#6b7280;">Aucun chargement récent.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<h2 class="gv-page-title" style="font-size:1.35rem;">Nouvel enregistrement</h2>

<form method="POST" action="{{ route('gestionnaire.chargement.store') }}">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
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
                    <select name="produit_id" required>
                        <option value="">Choisir un produit</option>
                        @foreach($produits as $p)
                            <option value="{{ $p->id }}" @selected(old('produit_id') == $p->id)>{{ $p->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="gv-field">
                    <label>Cuve source</label>
                    <select name="cuve_source_id" id="chg-cuve" required>
                        <option value="">Choisir la cuve</option>
                        @foreach($cuves as $c)
                            <option value="{{ $c->id }}" data-niveau="{{ $c->niveau_actuel }}" @selected(old('cuve_source_id') == $c->id)>
                                {{ $c->nom ?? $c->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="gv-field">
                    <label>Volume brut (L)</label>
                    <input type="number" name="volume_brut" id="chg-vb" value="{{ old('volume_brut') }}" min="1" required>
                </div>
                <div class="gv-field">
                    <label>Température (°C)</label>
                    <input type="number" step="0.1" name="temperature" id="chg-temp" value="{{ old('temperature', '15') }}" required>
                </div>
                <div class="gv-field">
                    <label>Client</label>
                    <input type="text" name="client_nom" value="{{ old('client_nom') }}" placeholder="TOTALENERGIES CI" required>
                </div>
                <div class="gv-field">
                    <label>Code client</label>
                    <input type="text" name="client_code" value="{{ old('client_code') }}" placeholder="Code">
                </div>
            </div>
        </div>

        <div class="gv-card-navy" style="margin-bottom:0;">
            <div class="gv-card-header"><span><i class="fas fa-truck" style="margin-right:8px;"></i>TRANSPORT &amp; CHAUFFEUR</span></div>
            <div class="gv-form-grid">
                <div class="gv-field">
                    <label>Immatriculation</label>
                    <input type="text" name="plaque_imm" value="{{ old('plaque_imm') }}" placeholder="AA-882-HH" required>
                </div>
                <div class="gv-field">
                    <label>Capacité totale camion (L)</label>
                    <input type="number" name="capacite_camion" value="{{ old('capacite_camion') }}" min="1" placeholder="45000" required>
                </div>
                <div class="gv-field" style="grid-column:1/-1;">
                    <label>Nom du chauffeur</label>
                    <input type="text" name="chauffeur_nom" value="{{ old('chauffeur_nom') }}" required>
                </div>
                <div class="gv-field" style="grid-column:1/-1;">
                    <label>Permis N°</label>
                    <input type="text" name="chauffeur_permis" value="{{ old('chauffeur_permis') }}" required>
                </div>
            </div>
        </div>
    </div>

    <div class="gv-form-actions" style="justify-content:space-between;margin-top:24px;">
        <a href="{{ route('gestionnaire.operations') }}" class="gv-btn-red"><i class="fas fa-xmark"></i> Annuler</a>
        <button type="submit" class="gv-btn-blue"><i class="fas fa-check"></i> Valider &amp; Générer PDF</button>
    </div>
</form>
@endsection
