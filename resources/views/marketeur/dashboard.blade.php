@extends('layouts.marketeur')

@section('pageTitle', 'Tableau de bord')

@section('marketeur-content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Tableau de bord</h1>
            <p class="mt-2 text-sm text-slate-500">Résumé des cessions et de vos volumes échangés.</p>
        </div>
        <a href="{{ route('marketeur.cessions') }}" class="inline-flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-200/10 hover:bg-slate-800">
            Voir les cessions
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $totalCessions = $cessionsEnvoyees + $cessionsRecues;
        @endphp

        <article class="rounded-[32px] border border-slate-200 bg-slate-950 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between gap-3">
                <span class="rounded-2xl bg-slate-900/80 px-3 py-2 text-xs uppercase tracking-[.2em]">Total cessions</span>
                <i class="fas fa-users text-2xl text-slate-300"></i>
            </div>
            <p class="mt-8 text-5xl font-semibold">{{ number_format($totalCessions, 0, ',', ' ') }}</p>
            <p class="mt-4 text-sm text-slate-300">Opérations totales</p>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-lg">
            <div class="flex items-center justify-between gap-3">
                <span class="rounded-2xl bg-emerald-100 px-3 py-2 text-emerald-700 text-xs uppercase tracking-[.2em]">Actifs</span>
                <i class="fas fa-check-circle text-2xl text-emerald-500"></i>
            </div>
            <p class="mt-8 text-5xl font-semibold text-slate-950">{{ number_format($cessionsEnvoyees, 0, ',', ' ') }}</p>
            <p class="mt-4 text-sm text-slate-500">Cessions envoyées</p>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-lg">
            <div class="flex items-center justify-between gap-3">
                <span class="rounded-2xl bg-sky-100 px-3 py-2 text-sky-700 text-xs uppercase tracking-[.2em]">Inactifs</span>
                <i class="fas fa-user-clock text-2xl text-sky-500"></i>
            </div>
            <p class="mt-8 text-5xl font-semibold text-slate-950">{{ number_format($cessionsRecues, 0, ',', ' ') }}</p>
            <p class="mt-4 text-sm text-slate-500">Cessions reçues</p>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-lg">
            <div class="flex items-center justify-between gap-3">
                <span class="rounded-2xl bg-rose-100 px-3 py-2 text-rose-700 text-xs uppercase tracking-[.2em]">Alertes</span>
                <i class="fas fa-exclamation-circle text-2xl text-rose-500"></i>
            </div>
            <p class="mt-8 text-5xl font-semibold text-slate-950">{{ number_format($totalVolumeCede + $totalVolumeRecu, 0, ',', ' ') }} L</p>
            <p class="mt-4 text-sm text-slate-500">Volume total échangé</p>
        </article>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
        <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-lg">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">Niveaux de stock par produit</h2>
                    <p class="mt-1 text-sm text-slate-500">Valeurs estimées à partir des volumes de cessions.</p>
                </div>
                <a href="{{ route('marketeur.cessions') }}" class="text-sm font-semibold text-slate-900 hover:text-slate-700">Voir plus <i class="fas fa-arrow-right ml-2"></i></a>
            </div>

            @php
                $productVolumes = $recentCessions->groupBy(fn($item) => $item->produit->nom ?? ($item->produit->libelle ?? 'Produit'))
                    ->map(fn($group) => $group->sum('volume'));
                $maxVolume = $productVolumes->max() ?: 1;
            @endphp

            <div class="mt-6 space-y-4 rounded-[32px] bg-sky-50 p-5">
                @forelse($productVolumes as $label => $volume)
                    @php
                        $ratio = min(100, max(5, intval($volume / $maxVolume * 100)));
                    @endphp
                    <div class="rounded-3xl bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-semibold text-slate-900">{{ strtoupper($label) }}</span>
                            <span class="text-sm text-slate-500">{{ number_format($volume, 0, ',', ' ') }} L</span>
                        </div>
                        <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-cyan-400" style="width: {{ $ratio }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="mt-4 text-sm text-slate-500">Aucune donnée de produit disponible pour le moment.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-lg">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">Historique des opérations</h2>
                    <p class="mt-1 text-sm text-slate-500">Dernières cessions impliquant votre compte.</p>
                </div>
                <a href="{{ route('marketeur.cessions') }}" class="text-sm font-semibold text-slate-900 hover:text-slate-700">Voir plus <i class="fas fa-arrow-right ml-2"></i></a>
            </div>

            <div class="mt-6 overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Produit</th>
                            <th class="px-4 py-3">Volume</th>
                            <th class="px-4 py-3">Société</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($recentCessions as $cession)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 text-slate-700">{{ optional($cession->date_cession)->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-4 text-slate-700">Cession</td>
                                <td class="px-4 py-4 text-slate-700">{{ $cession->produit->nom ?? 'N/A' }}</td>
                                <td class="px-4 py-4 text-slate-700">{{ number_format($cession->volume, 0, ',', ' ') }} L</td>
                                <td class="px-4 py-4 text-slate-700">{{ $cession->beneficiaire->company_name ?? $cession->cedant->company_name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Aucune opération récente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
"