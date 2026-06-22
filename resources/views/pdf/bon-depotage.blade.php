<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #102840;
            margin: 12px 14px;
        }

        /* ── Header ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 8px;
            border-bottom: 2px solid #0f172a;
            margin-bottom: 8px;
        }
        .brand-title { font-size: 20px; color: #102840; font-weight: bold; }
        .brand-sub { font-size: 8px; color: #64748b; line-height: 1.5; margin-top: 3px; }

        .doc-right { text-align: right; }
        .doc-label { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; }
        .doc-name { font-size: 16px; font-weight: bold; color: #0f172a; margin: 2px 0 4px; }
        .doc-meta { display: flex; gap: 10px; justify-content: flex-end; }
        .meta-item { background: #eff6ff; border-radius: 6px; padding: 4px 8px; text-align: left; }
        .meta-item small { display: block; color: #64748b; font-size: 7px; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 1px; }
        .meta-item strong { font-size: 10px; color: #0f172a; }

        /* ── 4-column info grid ── */
        .info-grid { display: flex; gap: 6px; margin-bottom: 7px; }
        .info-box {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 7px 8px;
        }
        .box-title {
            font-size: 8px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 5px;
        }
        .info-row { margin-bottom: 4px; }
        .info-row .lbl { font-size: 7.5px; color: #64748b; }
        .info-row .val { font-size: 9px; font-weight: bold; color: #0f172a; }

        /* ── Badge ── */
        .badge {
            display: inline-block;
            background: #0f766e;
            color: #fff;
            padding: 2px 7px;
            font-size: 7px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        /* ── Volume strip ── */
        .volumes { display: flex; gap: 6px; margin-bottom: 8px; }
        .vol-box { flex: 1; border-radius: 6px; padding: 7px 10px; }
        .vol-dark { background: #0f172a; color: #fff; }
        .vol-dark small  { display: block; color: #94a3b8; font-size: 7.5px; margin-bottom: 2px; }
        .vol-dark strong { font-size: 13px; }
        .vol-light { background: #f8fafc; border: 1px solid #e2e8f0; color: #0f172a; }
        .vol-light small  { display: block; color: #64748b; font-size: 7.5px; margin-bottom: 2px; }
        .vol-light strong { font-size: 13px; }

        /* ── Mesures table ── */
        .section-title {
            font-size: 8px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 4px;
        }
        .row-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .row-table th, .row-table td { padding: 5px 8px; border: 1px solid #e2e8f0; }
        .row-table th {
            background: #f1f5f9;
            color: #475569;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .row-table td { color: #102840; font-size: 9px; }

        /* ── Signatures ── */
        .footer {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
            margin-top: 10px;
        }
        .signature { flex: 1; font-size: 8.5px; color: #475569; min-height: 38px; }
        .signature.right { text-align: right; }
        .signature span { color: #94a3b8; }

        .note { margin-top: 8px; font-size: 7.5px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 5px; }
    </style>
</head>
<body>

    <!-- ── En-tête ── -->
    <div class="header">
        <div>
            <div class="brand-title">SIGECAR</div>
            <div class="brand-sub">
                Exploitation pétrolière — OMAP<br>
                BP 1945, Bamako, Mali &nbsp;|&nbsp; Tél: +223 20 20 00 90
            </div>
        </div>
        <div class="doc-right">
            <div class="doc-label">Document officiel</div>
            <div class="doc-name">BON DE DÉPOTAGE</div>
            <div class="doc-meta">
                <div class="meta-item">
                    <small>Numéro de reçu</small>
                    <strong>{{ $depotage->numero_depotage }}</strong>
                </div>
                <div class="meta-item">
                    <small>Date &amp; heure d'émission</small>
                    <strong>{{ $depotage->date_operation->format('d/m/Y — H:i') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 4 colonnes d'information ── -->
    <div class="info-grid">
        <div class="info-box">
            <div class="box-title">Infos générales</div>
            <div class="info-row"><div class="lbl">Produit</div><div class="val">{{ $depotage->produit->nom ?? '—' }}</div></div>
            <div class="info-row"><div class="lbl">Marketeur</div><div class="val">{{ $depotage->user?->operatorName() ?? '—' }}</div></div>
            <div class="info-row"><div class="lbl">Fournisseur</div><div class="val">{{ $depotage->fournisseur }}</div></div>
        </div>

        <div class="info-box">
            <div class="box-title">Source &amp; destination</div>
            <div class="info-row"><div class="lbl">Tank de destination</div><div class="val">{{ $depotage->cuve->nom ?? $depotage->cuve->code ?? '—' }}</div></div>
            <div class="info-row"><div class="lbl">Source</div><div class="val">{{ $depotage->provenance }}</div></div>
            <div class="info-row"><div class="lbl">ID transaction</div><div class="val">{{ $depotage->numero_bon_chargement ?? '—' }}</div></div>
        </div>

        <div class="info-box">
            <div class="box-title">Douane</div>
            <div class="info-row"><div class="lbl">Statut</div><div class="val"><span class="badge">{{ strtoupper(str_replace('_', ' ', $depotage->status)) }}</span></div></div>
            <div class="info-row"><div class="lbl">Déclaration n°</div><div class="val">{{ $depotage->declaration_douane ?? '—' }}</div></div>
            <div class="info-row"><div class="lbl">Bureau douane</div><div class="val">{{ $depotage->bureau_douane ?? '—' }}</div></div>
        </div>

        <div class="info-box">
            <div class="box-title">Transport &amp; chauffeur</div>
            <div class="info-row"><div class="lbl">Plaque camion</div><div class="val">{{ $depotage->plaque_imm }}</div></div>
            <div class="info-row"><div class="lbl">Nom chauffeur</div><div class="val">{{ $depotage->chauffeur_nom }}</div></div>
            <div class="info-row"><div class="lbl">N° permis / ID</div><div class="val">{{ $depotage->chauffeur_permis }}</div></div>
        </div>
    </div>

    <!-- ── Volumes ── -->
    <div class="volumes">
        <div class="vol-box vol-dark">
            <small>Volume brut</small>
            <strong>{{ number_format($depotage->volume_brut, 0, ',', ' ') }} L</strong>
        </div>
        <div class="vol-box vol-light">
            <small>Température</small>
            <strong>{{ $depotage->temperature }} °C</strong>
        </div>
        <div class="vol-box vol-dark">
            <small>Volume corrigé (15°C)</small>
            <strong>{{ number_format($depotage->volume_corrige, 0, ',', ' ') }} L</strong>
        </div>
    </div>

    <!-- ── Mesures par creux ── -->
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
                        <td>{{ $c->produit->nom ?? '—' }}</td>
                        <td>{{ number_format($c->capacite, 0, ',', ' ') }}</td>
                        <td>{{ $depotage->temperature }} °C</td>
                        <td>{{ number_format($c->volume, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- ── Signatures ── -->
    <div class="footer">
        <div class="signature">
            Signature du chauffeur<br>
            <span>Lu et approuvé</span>
        </div>
        <div class="signature right">
            Cachet &amp; signature du gestionnaire<br>
            <span>Nom, signature &amp; cachet</span>
        </div>
    </div>

    <p class="note">Note: Calcul basé sur la table de conversion ASTM 54B pour les produits pétroliers légers. Document généré par SIGECAR.</p>

</body>
</html>
