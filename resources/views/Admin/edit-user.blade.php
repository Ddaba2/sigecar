@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Modifier l'utilisateur</h1>

    <form action="{{ route('admin.update-user', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
            <input type="text" name="name" id="name" value="{{ $user->name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" value="{{ $user->email }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>

        <div class="mb-4">
            <label for="role" class="block text-sm font-medium text-gray-700">Rôle</label>
            <select name="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="gestionnaire" {{ $user->role === 'gestionnaire' ? 'selected' : '' }}>Gestionnaire</option>
                <option value="marketeur" {{ $user->role === 'marketeur' ? 'selected' : '' }}>Marketeur</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
            <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Actif</option>
                <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Désactivé</option>
            </select>
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Mettre à jour
        </button>
    </form>
</div>
@endsection
