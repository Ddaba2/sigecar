@extends('layouts.marketeur')

@section('pageTitle', 'Gestion des cessions')

@section('marketeur-content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Gestion des cessions</h1>
            <p class="mt-2 text-sm text-slate-500">Toutes vos cessions envoyées et reçues.</p>
        </div>
        <a href="{{ route('marketeur.dashboard') }}" class="inline-flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-200/10 hover:bg-slate-800">
            Retour au tableau de bord
        </a>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-[32px] border border-slate-200 bg-slate-50 p-6 shadow-lg">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">Cessions envoyées</h2>
                    <p class="mt-1 text-sm text-slate-500">Transferts réalisés depuis votre compte.</p>
                </div>
                <span class="text-sm font-semibold text-slate-700">{{ $cessionsEnvoyees->total() }} résultats</span>
            </div>

            <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-100 text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Produit</th>
                            <th class="px-4 py-3">Volume</th>
                            <th class="px-4 py-3">Bénéficiaire</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($cessionsEnvoyees as $cession)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 text-slate-700">{{ optional($cession->date_cession)->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-4 text-slate-700">{{ $cession->produit->nom ?? 'N/A' }}</td>
                                <td class="px-4 py-4 text-slate-700">{{ number_format($cession->volume, 0, ',', ' ') }} L</td>
                                <td class="px-4 py-4 text-slate-700">{{ $cession->beneficiaire->company_name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Aucune cession envoyée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $cessionsEnvoyees->links() }}</div>
        </section>

        <section class="rounded-[32px] border border-slate-200 bg-slate-50 p-6 shadow-lg">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">Cessions reçues</h2>
                    <p class="mt-1 text-sm text-slate-500">Transferts reçus par votre entreprise.</p>
                </div>
                <span class="text-sm font-semibold text-slate-700">{{ $cessionsRecues->total() }} résultats</span>
            </div>

            <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-100 text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Produit</th>
                            <th class="px-4 py-3">Volume</th>
                            <th class="px-4 py-3">Cédant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($cessionsRecues as $cession)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 text-slate-700">{{ optional($cession->date_cession)->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-4 text-slate-700">{{ $cession->produit->nom ?? 'N/A' }}</td>
                                <td class="px-4 py-4 text-slate-700">{{ number_format($cession->volume, 0, ',', ' ') }} L</td>
                                <td class="px-4 py-4 text-slate-700">{{ $cession->cedant->company_name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Aucune cession reçue.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $cessionsRecues->links() }}</div>
        </section>
    </div>
</div>
@endsection
