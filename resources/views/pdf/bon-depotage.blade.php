<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #102840; margin: 18px; }
        .page { width: 100%; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
        .brand { width: 58%; }
        .brand-title { font-size: 22px; color: #102840; margin: 0; letter-spacing: -0.5px; }
        .brand-sub { margin: 6px 0 0; font-size: 10px; color: #4b5563; line-height: 1.5; }
        .doc-title { text-align: right; }
        .doc-label { font-size: 12px; color: #475569; text-transform: uppercase; letter-spacing: 0.1em; }
        .doc-name { margin: 10px 0 0; font-size: 18px; color: #0f172a; }
        .doc-card { margin-top: 12px; background: #eff6ff; border-radius: 10px; padding: 12px 14px; display: inline-block; text-align: left; }
        .doc-card small { display: block; color: #475569; margin-bottom: 4px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; }
        .doc-card strong { font-size: 14px; color: #102840; }
        .intro-line { border-bottom: 1px solid #cbd5e1; margin: 16px 0 20px; }
        .section-title { font-size: 12px; color: #0f172a; letter-spacing: 0.08em; text-transform: uppercase; margin: 0 0 10px; }
        .grid { display: flex; gap: 12px; margin-bottom: 14px; }
        .box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; width: 100%; }
        .box strong { display: block; margin-bottom: 6px; font-size: 11px; color: #0f172a; }
        .box span { display: block; font-size: 10px; color: #475569; }
        .highlight { background: #0f172a; color: #fff; padding: 12px 14px; border-radius: 12px; }
        .highlight small { color: #cbd5e1; }
        .row-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .row-table th, .row-table td { padding: 10px 12px; border: 1px solid #e2e8f0; }
        .row-table th { background: #f8fafc; color: #475569; font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; }
        .row-table td { color: #102840; font-size: 11px; }
        .badge { display: inline-block; background: #0f766e; color: #fff; padding: 4px 10px; font-size: 9px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.08em; }
        .footer { display: flex; justify-content: space-between; margin-top: 24px; gap: 12px; }
        .signature { width: 100%; border-top: 1px dashed #cbd5e1; padding-top: 10px; color: #475569; font-size: 10px; }
        .note { margin-top: 20px; font-size: 9px; color: #64748b; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="brand">
                <p class="brand-title">SIGECAR</p>
                <p class="brand-sub">Exploitation pétrolière<br>OMAP<br>BP 1945, Bamako, Mali<br>Tél: +223 20 20 00 90</p>
            </div>
            <div class="doc-title">
                <div class="doc-label">Bon de dépotage</div>
                <div class="doc-name">BON DE DÉPOTAGE</div>
                <div class="doc-card">
                    <small>Numéro de reçu</small>
                    <strong>{{ $depotage->numero_depotage }}</strong>
                </div>
                <div class="doc-card" style="margin-top:8px;">
                    <small>Date &amp; heure d'émission</small>
                    <strong>{{ $depotage->date_operation->format('d F Y — H:i') }}</strong>
                </div>
            </div>
        </div>

        <div class="intro-line"></div>

        <div class="grid">
            <div class="box">
                <strong>Informations générales</strong>
                <span>Produit</span>
                <strong>{{ $depotage->produit->name ?? '—' }}</strong>
                <span>Marketeur</span>
                <strong>{{ $depotage->fournisseur }}</strong>
                <span>Fournisseur</span>
                <strong>{{ $depotage->fournisseur }}</strong>
            </div>
            <div class="box">
                <strong>Source & destination</strong>
                <span>Tank de destination</span>
                <strong>{{ $depotage->cuve->nom ?? $depotage->cuve->code ?? '—' }}</strong>
                <span>Source</span>
                <strong>{{ $depotage->provenance }}</strong>
                <span>ID transaction</span>
                <strong>{{ $depotage->numero_bon_chargement ?? '—' }}</strong>
            </div>
        </div>

        <div class="grid">
            <div class="box">
                <strong>Informations douane</strong>
                <span>Statut</span>
                <strong class="badge">{{ strtoupper(str_replace('_', ' ', $depotage->status)) }}</strong>
                <span>Déclaration n°</span>
                <strong>{{ $depotage->declaration_douane ?? '—' }}</strong>
                <span>Bureau douane</span>
                <strong>{{ $depotage->bureau_douane ?? '—' }}</strong>
            </div>
            <div class="box">
                <strong>Transport & chauffeur</strong>
                <span>Plaque camion</span>
                <strong>{{ $depotage->plaque_imm }}</strong>
                <span>Nom chauffeur</span>
                <strong>{{ $depotage->chauffeur_nom }}</strong>
                <span>N° permis / ID</span>
                <strong>{{ $depotage->chauffeur_permis }}</strong>
            </div>
        </div>

        <div class="grid">
            <div class="highlight">
                <small>Volume brut</small>
                <strong>{{ number_format($depotage->volume_brut, 0, ',', ' ') }} litres</strong>
            </div>
            <div class="highlight" style="background:#f8fafc; color:#0f172a; border:1px solid #cbd5e1;">
                <small>Température</small>
                <strong>{{ $depotage->temperature }} °C</strong>
            </div>
            <div class="highlight">
                <small>Volume corrigé (15°C)</small>
                <strong>{{ number_format($depotage->volume_corrige, 0, ',', ' ') }} litres</strong>
            </div>
        </div>

        @if($depotage->operationsCreux->isNotEmpty())
            <div class="section-title">Mesures par creux</div>
            <table class="row-table">
                <thead>
                    <tr>
                        <th>Compart.</th>
                        <th>Type produit</th>
                        <th>Volume brut (L)</th>
                        <th>Temp. (°C)</th>
                        <th>Volume @ 15°C (L)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($depotage->operationsCreux as $c)
                        <tr>
                            <td>{{ $c->numero_creux }}</td>
                            <td>{{ $c->produit->name ?? '—' }}</td>
                            <td>{{ number_format($c->capacite, 0, ',', ' ') }}</td>
                            <td>{{ $depotage->temperature }} °C</td>
                            <td>{{ number_format($c->volume, 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="footer" style="justify-content: space-between;">
            <div class="signature" style="width:48%; text-align:left;">Signature du chauffeur<br><span>Lu et approuvé</span></div>
            <div class="signature" style="width:48%; text-align:right;">Cachet & signature du gestionnaire<br><span>Nom, signature & cachet</span></div>
        </div>

        <p class="note">Note: Calcul basé sur la table de conversion ASTM 54B pour les produits pétroliers légers. Document généré par SIGECAR.</p>
    </div>
</body>
</html>
