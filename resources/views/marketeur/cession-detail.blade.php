@extends('layouts.marketeur')

@section('marketeur-content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Détail de la cession</h1>
            <p class="mt-2 text-sm text-slate-500">Informations complètes sur cette transaction.</p>
        </div>
        <a href="{{ route('marketeur.cessions') }}" class="inline-flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-200/10 hover:bg-slate-800">
            Retour à la liste
        </a>
    </div>

    <section class="rounded-[32px] border border-slate-200 bg-white p-8 shadow-sm">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <div>
                    <p class="text-sm uppercase tracking-[.2em] text-slate-500">Cession</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $cession->numero_cession ?? 'N° non disponible' }}</h2>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-[.2em] text-slate-500">Date</p>
                        <p class="mt-2 text-sm text-slate-700">{{ optional($cession->date_cession)->format('d M Y H:i') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[.2em] text-slate-500">Statut</p>
                        <p class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">{{ ucfirst($cession->status ?? 'En attente') }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-[.2em] text-slate-500">Cédant</p>
                    <p class="mt-2 text-sm text-slate-700">{{ $cession->cedant->company_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[.2em] text-slate-500">Bénéficiaire</p>
                    <p class="mt-2 text-sm text-slate-700">{{ $cession->beneficiaire->company_name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-3">
            <div class="rounded-3xl bg-slate-50 p-5">
                <p class="text-xs uppercase tracking-[.2em] text-slate-500">Produit</p>
                <p class="mt-3 text-lg font-semibold text-slate-900">{{ $cession->produit->nom ?? 'N/A' }}</p>
            </div>
            <div class="rounded-3xl bg-slate-50 p-5">
                <p class="text-xs uppercase tracking-[.2em] text-slate-500">Volume</p>
                <p class="mt-3 text-lg font-semibold text-slate-900">{{ number_format($cession->volume, 0, ',', ' ') }} L</p>
            </div>
            <div class="rounded-3xl bg-slate-50 p-5">
                <p class="text-xs uppercase tracking-[.2em] text-slate-500">Cuve</p>
                <p class="mt-3 text-lg font-semibold text-slate-900">{{ $cession->cuve->code ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <div class="rounded-3xl bg-slate-50 p-6">
                <p class="text-xs uppercase tracking-[.2em] text-slate-500">Prix unitaire</p>
                <p class="mt-3 text-lg font-semibold text-slate-900">{{ number_format($cession->prix_unitaire, 2, ',', ' ') ?? '0,00' }} FCFA</p>
            </div>
            <div class="rounded-3xl bg-slate-50 p-6">
                <p class="text-xs uppercase tracking-[.2em] text-slate-500">Montant total</p>
                <p class="mt-3 text-lg font-semibold text-slate-900">{{ number_format($cession->montant_total, 2, ',', ' ') ?? '0,00' }} FCFA</p>
            </div>
        </div>
    </section>
</div>
@endsection
