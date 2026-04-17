@extends('layouts.marketeur')

@section('pageTitle', 'Gestion du transport')

@section('marketeur-content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Gestion du transport</h1>
            <p class="mt-2 text-sm text-slate-500">Suivi de vos dépôtages et chargements.</p>
        </div>
        <a href="{{ route('marketeur.dashboard') }}" class="inline-flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-200/10 hover:bg-slate-800">
            Retour au tableau de bord
        </a>
    </div>

    <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-lg">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-950">Historique des opérations</h2>
                <p class="mt-1 text-sm text-slate-500">Dépôtages et chargements réalisés pour votre entreprise.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <div class="inline-flex items-center gap-3 rounded-3xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-slate-200 text-slate-900">D</span>
                    {{ $depotages->total() }} dépôtages
                </div>
                <div class="inline-flex items-center gap-3 rounded-3xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-slate-200 text-slate-900">C</span>
                    {{ $chargements->total() }} chargements
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-6 xl:grid-cols-2">
            <div class="rounded-[28px] bg-slate-50 p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Historiques des dépôtages</h3>
                        <p class="mt-2 text-sm text-slate-500">Dépôts de carburant liés à votre compte.</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">{{ $depotages->total() }} résultats</span>
                </div>

                <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200 bg-white">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Produit</th>
                                <th class="px-4 py-3">Volume</th>
                                <th class="px-4 py-3">Cuve</th>
                                <th class="px-4 py-3">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse($depotages as $depotage)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-4 text-slate-700">{{ optional($depotage->date_operation)->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ $depotage->produit->nom ?? 'N/A' }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ number_format($depotage->volume_brut, 0, ',', ' ') }} L</td>
                                    <td class="px-4 py-4 text-slate-700">{{ $depotage->cuve->code ?? 'N/A' }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ ucfirst($depotage->status ?? 'Validé') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Aucun dépôtage trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-[28px] bg-slate-50 p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Historiques des chargements</h3>
                        <p class="mt-2 text-sm text-slate-500">Chargements réalisés pour votre société.</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">{{ $chargements->total() }} résultats</span>
                </div>

                <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200 bg-white">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Produit</th>
                                <th class="px-4 py-3">Volume</th>
                                <th class="px-4 py-3">Client</th>
                                <th class="px-4 py-3">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse($chargements as $chargement)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-4 text-slate-700">{{ optional($chargement->date_operation)->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ $chargement->produit->nom ?? 'N/A' }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ number_format($chargement->volume_brut, 0, ',', ' ') }} L</td>
                                    <td class="px-4 py-4 text-slate-700">{{ $chargement->client_nom ?? '-' }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">{{ ucfirst($chargement->status ?? 'Terminé') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Aucun chargement trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
