<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a365d; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #1a365d; }
        .muted { color: #64748b; font-size: 9px; }
        .line { border-bottom: 1px solid #1a365d; margin: 12px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        th { background: #1a365d; color: #fff; }
    </style>
</head>
<body>
    <h1>CESSION DE STOCK — SIGECAR</h1>
    <div class="muted">N° {{ $cession->numero_cession }} — {{ $cession->date_cession->format('d/m/Y H:i') }}</div>
    <div class="line"></div>

    <table>
        <tr>
            <th>Cédant</th><td>{{ $cession->cedant->company_name ?? '—' }}</td>
        </tr>
        <tr>
            <th>Bénéficiaire</th><td>{{ $cession->beneficiaire->company_name ?? '—' }}</td>
        </tr>
        <tr>
            <th>Produit</th><td>{{ $cession->produit->nom ?? '—' }}</td>
        </tr>
        <tr>
            <th>Cuve</th><td>{{ $cession->cuve->nom ?? $cession->cuve->code ?? '—' }}</td>
        </tr>
        <tr>
            <th>Volume</th><td>{{ number_format($cession->volume, 0, ',', ' ') }} L (corrigé : {{ number_format($cession->volume_corrige, 0, ',', ' ') }} L)</td>
        </tr>
        <tr>
            <th>Statut</th><td>{{ $cession->status }}</td>
        </tr>
    </table>

    <p class="muted" style="margin-top:24px;">Document généré par SIGECAR</p>
</body>
</html>
