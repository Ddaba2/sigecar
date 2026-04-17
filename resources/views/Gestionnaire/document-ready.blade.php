@extends('layouts.gestionnaire')

@section('gestionnaire-content')
<p class="gv-breadcrumb">OPÉRATIONS / RAPPORT</p>
<h1 class="gv-page-title">{{ $operationType }} enregistré</h1>
<p class="gv-page-sub">Le rapport a été généré et est prêt à être téléchargé.</p>

@if(isset($message))
    <div class="gv-alert gv-alert-success">{{ $message }}</div>
@endif

<div class="gv-card" style="max-width:600px;margin-top:20px;">
    <div class="gv-card-header" style="text-transform:uppercase;">
        <i class="fas fa-file-pdf"></i>
        Rapport {{ $operationType }}
    </div>
    <div style="padding:20px;">
        <p><strong>Référence :</strong> {{ $reference }}</p>
        <p>Le document PDF a été généré automatiquement.</p>
        <div style="display:flex;gap:12px;margin-top:16px;flex-wrap:wrap;">
            <a href="{{ $documentUrl }}" class="gv-btn-blue" style="display:inline-flex;align-items:center;">
                <i class="fas fa-download" style="margin-right:8px;"></i>
                Télécharger le PDF
            </a>
            <a href="{{ route('gestionnaire.operations') }}" class="gv-btn-red" style="display:inline-flex;align-items:center;">
                <i class="fas fa-arrow-left" style="margin-right:8px;"></i>
                Voir mes opérations
            </a>
        </div>
    </div>
</div>
@endsection
