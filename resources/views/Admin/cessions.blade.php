@extends('layouts.admin')

@section('admin-content')
<h2 class="admin-subtitle"><i class="fas fa-history"></i> HISTORIQUES DES CESSION</h2>
<table class="admin-table">
    <thead><tr><th>DATE</th><th>CEDANT</th><th>PRODUIT</th><th>VOLUME</th><th>CUVE</th><th>BENEFICIAIRE</th></tr></thead>
    <tbody>
        @foreach($cessions as $cession)
        <tr>
            <td>{{ $cession->date_cession ? $cession->date_cession->format('d M Y') : 'N/A' }}</td>
            <td>{{ $cession->cedant ? $cession->cedant->company_name : 'N/A' }}</td>
            <td>{{ $cession->produit ? $cession->produit->nom : 'N/A' }}</td>
            <td>{{ number_format($cession->volume_corrige ?: $cession->volume, 0, ',', ' ') }} L</td>
            <td>{{ $cession->cuve ? $cession->cuve->code : 'N/A' }}</td>
            <td>{{ $cession->beneficiaire ? $cession->beneficiaire->company_name : 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
