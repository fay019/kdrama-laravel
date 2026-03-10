@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-900">
    <!-- Sidebar -->
    <x-admin-sidebar />

    <!-- Main Content -->
    <div class="flex-1">
        <!-- Header -->
        <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10 overflow-hidden">
            <div class="px-3 sm:px-6 lg:px-8 py-4">
                <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                    <span class="text-3xl sm:text-4xl">📦</span>
                    <span>Cache Management</span>
                </h1>
                <p class="text-slate-400 mt-1">Manage and clean up PDF export files</p>
            </div>
        </div>

        <!-- Page Content -->
        <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">
    <div class="w-full max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">📦 Gestion du Cache PDF</h1>
            <p class="text-slate-400">Gérez les fichiers PDF en cache et libérez de l'espace disque</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-sm mb-1">Fichiers en cache</p>
                        <p class="text-3xl font-bold text-white">{{ $fileCount }}</p>
                    </div>
                    <div class="text-4xl">📄</div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-sm mb-1">Espace utilisé</p>
                        <p class="text-3xl font-bold text-white">{{ $totalSizeMb }} MB</p>
                    </div>
                    <div class="text-4xl">💾</div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-sm mb-1">Période de rétention</p>
                        <p class="text-3xl font-bold text-white">7 jours</p>
                    </div>
                    <div class="text-4xl">⏰</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-slate-800 rounded-lg p-6 border border-slate-700 mb-8">
            <h2 class="text-lg font-semibold text-white mb-4">Actions</h2>
            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.exports.cache.purge-all') }}" class="inline">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('⚠️ Êtes-vous sûr? Cela supprimera TOUS les fichiers en cache.')"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
                        🗑️ Supprimer tout le cache
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.exports.cache.purge-expired') }}" class="inline">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Supprimer les fichiers expirés (> 7 jours)?')"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold transition">
                        ⏰ Supprimer fichiers expirés
                    </button>
                </form>
            </div>
        </div>

        <!-- Files Table -->
        <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden">
            @if(count($files) > 0)
                <table class="w-full">
                    <thead class="bg-slate-700 border-b border-slate-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">Nom du fichier</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">Taille</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">Créé le</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">Statut</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-slate-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @foreach($files as $file)
                            <tr class="hover:bg-slate-700/50 transition">
                                <td class="px-6 py-4 text-sm text-white">
                                    <code class="bg-slate-900 px-2 py-1 rounded text-xs">{{ $file['name'] }}</code>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-300">
                                    {{ $file['size_mb'] }} MB
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-300">
                                    {{ $file['created_at']->format('d/m/Y à H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($file['is_expired'])
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-900/30 text-red-400">
                                            ❌ Expiré
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-900/30 text-green-400">
                                            ✅ Actif ({{ $file['days_remaining'] }}j)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-right">
                                    <form method="POST" action="{{ route('admin.exports.cache.delete', $file['name']) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                onclick="return confirm('Supprimer ce fichier?')"
                                                class="text-red-400 hover:text-red-300 font-semibold transition">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-8 text-center">
                    <p class="text-slate-400 text-lg">📭 Aucun fichier en cache pour le moment</p>
                </div>
            @endif
        </div>
        </div>
    </div>
</div>
@endsection
