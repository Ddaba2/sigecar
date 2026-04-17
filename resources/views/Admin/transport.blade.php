@extends('layouts.admin')

@section('admin-content')
<h2 class="admin-subtitle"><i class="fas fa-history"></i> HISTORIQUES DES CHARGEMENT</h2>
<table class="admin-table">
    <thead><tr><th>DATE</th><th>SOCIETE</th><th>PRODUIT</th><th>VOLUME</th><th>CUVE</th><th>STATUS</th></tr></thead>
    <tbody>
        @foreach($chargements as $chargement)
        <tr>
            <td>{{ $chargement->date_operation ? $chargement->date_operation->format('d M Y') : 'N/A' }}</td>
            <td>{{ $chargement->client_nom ?: 'N/A' }}</td>
            <td>{{ $chargement->produit ? $chargement->produit->nom : 'N/A' }}</td>
            <td>{{ number_format($chargement->volume_corrige ?: $chargement->volume_brut, 0, ',', ' ') }} L</td>
            <td>{{ $chargement->cuve ? $chargement->cuve->code : 'N/A' }}</td>
            <td><span class="tag {{ $chargement->status === 'acquitte' ? 'ok' : 'red' }}">{{ ucfirst($chargement->status ?: 'pending') }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
<style>
    .tag { padding:2px 8px; color:#fff; font-size:18px; font-family:Georgia,serif; border-radius:3px; }
    .tag.ok { background:#31b952; }
    .tag.red { background:#ff1111; }
</style>
@endsection
