<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a365d; margin: 20px; }
        h1 { font-size: 16px; margin: 0 0 12px; }
        h2 { font-size: 12px; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #94a3b8; padding: 6px 8px; text-align: left; }
        th { background: #e2e8f0; }
    </style>
</head>
<body>
    <h1>Rapport journalier SIGECAR</h1>
    <p><strong>Date :</strong> {{ $day->format('d/m/Y') }}</p>

    <h2>Dépotages</h2>
    <table>
        <tr><th>Heure</th><th>N°</th><th>Produit</th><th>Volume (L)</th><th>Fournisseur</th></tr>
        @foreach($depotagesJour as $d)
            <tr>
                <td>{{ $d->date_operation->format('H:i') }}</td>
                <td>{{ $d->numero_depotage }}</td>
                <td>{{ $d->produit->nom ?? '' }}</td>
                <td>{{ $d->volume_brut }}</td>
                <td>{{ $d->fournisseur }}</td>
            </tr>
        @endforeach
    </table>

    <h2>Chargements</h2>
    <table>
        <tr><th>Heure</th><th>N°</th><th>Produit</th><th>Volume (L)</th><th>Client</th></tr>
        @foreach($chargementsJour as $c)
            <tr>
                <td>{{ $c->date_operation->format('H:i') }}</td>
                <td>{{ $c->numero_chargement }}</td>
                <td>{{ $c->produit->nom ?? '' }}</td>
                <td>{{ $c->volume_brut }}</td>
                <td>{{ $c->client_nom }}</td>
            </tr>
        @endforeach
    </table>

    <h2>Cessions</h2>
    <table>
        <tr><th>Heure</th><th>N°</th><th>Produit</th><th>Volume (L)</th><th>Bénéficiaire</th></tr>
        @foreach($cessionsJour as $ces)
            <tr>
                <td>{{ $ces->date_cession->format('H:i') }}</td>
                <td>{{ $ces->numero_cession }}</td>
                <td>{{ $ces->produit->nom ?? '' }}</td>
                <td>{{ $ces->volume }}</td>
                <td>{{ $ces->beneficiaire->company_name ?? '' }}</td>
            </tr>
        @endforeach
    </table>

    <h2>Stocks par famille (état cuves + flux du jour, volumes corrigés)</h2>
    <table>
        <tr><th>Famille</th><th>Entrées jour (L)</th><th>Sorties jour (L)</th><th>Stock cuves (L)</th><th>Capacité (L)</th><th>% remplissage</th></tr>
        @foreach($famillesRapport as $f)
            <tr>
                <td>{{ $f['title'] }}</td>
                <td>{{ number_format($f['entrees_jour'], 0, ',', ' ') }}</td>
                <td>{{ number_format($f['sorties_jour'], 0, ',', ' ') }}</td>
                <td>{{ number_format($f['stock_cuves'], 0, ',', ' ') }}</td>
                <td>{{ number_format($f['capacite_totale'], 0, ',', ' ') }}</td>
                <td>{{ $f['pct_remplissage'] }} %</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
