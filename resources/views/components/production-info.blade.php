@props(['kdrama'])

@php
    $productionCompanies = $kdrama['production_companies'] ?? [];
    $networks = $kdrama['networks'] ?? [];
    $hasData = count($productionCompanies) > 0 || count($networks) > 0;
@endphp

@if($hasData)
<div class="bg-slate-800/50 border border-slate-700 rounded-lg p-6">
    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
        🎬 Production & Diffusion
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Studios de Production -->
        @if(count($productionCompanies) > 0)
        <div>
            <h4 class="text-sm font-semibold text-slate-300 mb-3 flex items-center gap-2">
                <span>🏢</span> Studios
            </h4>
            <div class="space-y-2">
                @foreach($productionCompanies as $company)
                    <div class="flex items-center gap-2">
                        @if($company['logo_path'] ?? false)
                            <img src="https://image.tmdb.org/t/p/w300{{ $company['logo_path'] }}"
                                 alt="{{ $company['name'] }}"
                                 class="h-8 object-contain"
                                 title="{{ $company['name'] }}">
                        @else
                            <span class="text-slate-500">•</span>
                        @endif
                        <span class="text-slate-300 text-sm">{{ $company['name'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Réseaux de Diffusion -->
        @if(count($networks) > 0)
        <div>
            <h4 class="text-sm font-semibold text-slate-300 mb-3 flex items-center gap-2">
                <span>📡</span> Réseaux
            </h4>
            <div class="space-y-3">
                @foreach($networks as $network)
                    <div class="flex items-center gap-3 bg-slate-900/50 p-3 rounded border border-slate-700/50 hover:border-purple-500/50 transition">
                        @if($network['logo_path'] ?? false)
                            <img src="https://image.tmdb.org/t/p/w300{{ $network['logo_path'] }}"
                                 alt="{{ $network['name'] }}"
                                 class="h-10 object-contain"
                                 title="{{ $network['name'] }}">
                        @else
                            <span class="text-purple-400 text-lg">📺</span>
                        @endif
                        <span class="font-semibold text-slate-200">{{ $network['name'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endif
