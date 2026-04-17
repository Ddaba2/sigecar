@extends('layouts.app')

@section('title', 'Marketeur - ' . ($title ?? 'Dashboard'))

@section('content')
<div class="min-h-screen flex bg-slate-100 text-slate-900">
    <aside class="w-72 bg-slate-950 text-slate-100 shadow-2xl relative">
        <div class="p-6 border-b border-slate-800">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-white text-slate-950 shadow-lg">
                    <i class="fas fa-oil-can text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">SIGECAR</h1>
                    <p class="text-xs uppercase tracking-[.2em] text-slate-400">Espace Opérateur</p>
                </div>
            </div>
        </div>

        <nav class="p-4 space-y-3">
            <a href="{{ route('marketeur.dashboard') }}" class="sidebar-link @if(request()->routeIs('marketeur.dashboard')) active @endif">
                <i class="fas fa-th-large"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="{{ route('marketeur.operations') }}" class="sidebar-link @if(request()->routeIs('marketeur.operations')) active @endif">
                <i class="fas fa-truck"></i>
                <span>Gestion du transport</span>
            </a>
            <a href="{{ route('marketeur.cessions') }}" class="sidebar-link @if(request()->routeIs('marketeur.cessions') || request()->routeIs('marketeur.cession.show')) active @endif">
                <i class="fas fa-exchange-alt"></i>
                <span>Gestion des cessions</span>
            </a>
        </nav>

        <div class="absolute bottom-0 w-full p-5 border-t border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-3xl bg-slate-800 flex items-center justify-center">
                    <i class="fas fa-user-circle text-xl text-slate-300"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold">{{ Auth::user()->name ?? 'Marketeur' }}</p>
                    <p class="text-xs uppercase tracking-[.2em] text-slate-500">Opérateur</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-300 hover:text-rose-400" title="Déconnexion">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="flex-1 min-h-screen overflow-y-auto bg-slate-100">
        <div class="sticky top-0 z-20 border-b border-slate-200 bg-slate-100/80 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-3xl bg-white text-slate-950 shadow-lg">
                        <i class="fas fa-oil-can text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[.3em] text-slate-500">SIGECAR</p>
                        <h2 class="text-xl font-semibold text-slate-900">@yield('pageTitle', 'Tableau de bord opérateur')</h2>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-slate-700 shadow-sm hover:bg-slate-50">
                        <i class="fas fa-bell"></i>
                    </button>
                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-slate-700 shadow-sm hover:bg-slate-50">
                        <i class="fas fa-user-circle"></i>
                    </button>
                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-slate-700 shadow-sm hover:bg-slate-50">
                        <i class="fas fa-cog"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="mx-auto w-full max-w-7xl px-6 py-8">
            @yield('marketeur-content')
        </div>
    </main>
</div>

@push('styles')
<style>
    .sidebar-link {
        @apply flex items-center gap-3 rounded-3xl px-4 py-4 text-slate-300 transition-all duration-200;
    }
    .sidebar-link:hover {
        @apply bg-slate-800 text-white;
    }
    .sidebar-link.active {
        @apply bg-slate-100 text-slate-950 shadow-lg;
    }
    .sidebar-link i {
        @apply w-5 text-lg;
    }
    .sidebar-link span {
        @apply font-semibold;
    }
</style>
@endpush
@endsection
