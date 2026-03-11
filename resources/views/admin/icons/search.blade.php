<x-app-layout>
    <div class="flex min-h-screen bg-slate-900">
        <x-admin-sidebar />
        <div class="flex-1">
            <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10 overflow-hidden">
                <div class="px-3 sm:px-6 lg:px-8 py-4">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                        <span class="text-3xl sm:text-4xl">🎨</span>
                        <span>{{ __('admin.icons_title') }} ({{ $count }} Icons)</span>
                    </h1>
                    <p class="text-slate-400 mt-1">{{ __('admin.icons_subtitle') }}</p>
                </div>
            </div>
            <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">
        <div class="w-full max-w-7xl mx-auto">
            <!-- Search Form -->
            <div class="mb-6 card p-6">
                <div class="flex gap-2">
                    <input type="text" id="iconSearch" placeholder="{{ __('admin.icons_search_placeholder') }}"
                           class="flex-1 px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500">
                    <a href="{{ route('admin.icons.search') }}" class="px-6 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded font-semibold">
                        {{ __('admin.icons_clear') }}
                    </a>
                </div>
                <p class="text-xs text-slate-400 mt-2">{{ __('admin.icons_results_updating') }}</p>
            </div>

            <!-- Icons Grid -->
            <div id="iconsGrid" class="card p-6">
                @if(count($icons) > 0)
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4" id="iconsList">
                        @foreach($icons as $icon)
                            <div class="icon-card group relative p-4 bg-slate-700 rounded hover:bg-slate-600 transition cursor-pointer"
                                 data-icon-name="{{ $icon['type'] === 'simple' ? 'si-' . $icon['name'] : $icon['name'] }}"
                                 data-icon-type="{{ $icon['type'] }}">

                                <!-- Icon Preview -->
                                <div class="mb-3 flex justify-center h-12 text-slate-300">
                                    @php
                                        $svgContent = null;
                                        if ($icon['type'] === 'tabler') {
                                            $tablerPath = base_path("vendor/secondnetwork/blade-tabler-icons/resources/svg/{$icon['name']}.svg");
                                            if (file_exists($tablerPath)) {
                                                $svgContent = file_get_contents($tablerPath);
                                                $svgContent = str_replace(['<svg', '</svg>'], ['<svg class="w-8 h-8"', '</svg>'], $svgContent);
                                            }
                                        } else {
                                            $simpleSvgPath = base_path("vendor/codeat3/blade-simple-icons/resources/svg/{$icon['name']}.svg");
                                            if (file_exists($simpleSvgPath)) {
                                                $svgContent = file_get_contents($simpleSvgPath);
                                                $svgContent = str_replace(['<svg', '</svg>'], ['<svg class="w-8 h-8 fill-current"', '</svg>'], $svgContent);
                                            }
                                        }
                                    @endphp
                                    @if($svgContent)
                                        <div class="w-8 h-8" style="display: flex; align-items: center; justify-content: center;">
                                            {!! $svgContent !!}
                                        </div>
                                    @else
                                        <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                        </svg>
                                    @endif
                                </div>

                                <!-- Icon Name & Type -->
                                <div class="mb-2">
                                    <p class="text-xs text-slate-300 text-center truncate font-mono">
                                        @if($icon['type'] === 'simple')
                                            si-{{ $icon['name'] }}
                                        @else
                                            {{ $icon['name'] }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-blue-400 text-center truncate">
                                        {{ $icon['label'] }}
                                    </p>
                                </div>

                                <!-- Copy Tooltip -->
                                <div class="hidden group-hover:block absolute inset-0 bg-black/80 rounded flex items-center justify-center z-10">
                                    <div class="text-center">
                                        <p class="text-white text-xs font-semibold mb-1">📋 Click to copy</p>
                                        <p class="text-slate-300 text-xs font-mono break-all">
                                            @if($icon['type'] === 'simple')
                                                si-{{ $icon['name'] }}
                                            @else
                                                {{ $icon['name'] }}
                                            @endif
                                        </p>
                                        @if($icon['type'] === 'simple')
                                            <p class="text-blue-300 text-xs font-semibold mt-1">Simple Icons</p>
                                        @else
                                            <p class="text-gray-400 text-xs font-semibold mt-1">Tabler</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if(count($icons) < $count)
                        <div class="mt-6 text-center">
                            <p class="text-slate-400 text-sm mb-4" id="iconsCounter">
                                Affichage <strong id="displayedCount">{{ count($icons) }}</strong> icons sur <strong id="totalCount">{{ $count }}</strong>
                            </p>
                            <button onclick="loadMoreIcons()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold transition">
                                📥 Charger plus
                            </button>
                            <p class="text-slate-500 text-xs mt-3">💡 Conseil: Utilisez la recherche pour trouver rapidement un icon</p>
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <p class="text-slate-400 text-lg">❌ {{ __('admin.icons_no_found') }}</p>
                        <p class="text-slate-500 text-sm mt-2">{{ __('admin.icons_try_search') }}</p>
                    </div>
                @endif
            </div>

            <!-- Tips -->
            <div class="mt-6 card p-4 bg-slate-700/50">
                <h3 class="font-semibold text-white mb-2">{{ __('admin.icons_tips') }}</h3>
                <ul class="text-slate-300 text-sm space-y-1">
                    <li>• {{ __('admin.icons_search_networks') }} <code class="text-blue-300">brand-</code> (twitter, github, facebook, etc.)</li>
                    <li>• {{ __('admin.icons_search_common') }} <code class="text-blue-300">mail</code>, <code class="text-blue-300">link</code>, <code class="text-blue-300">phone</code></li>
                    <li>• {{ __('admin.icons_click_copy') }}</li>
                    <li>• {{ __('admin.icons_use_in_form') }}</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
let searchTimeout;

// Event delegation pour les clics sur les icônes
document.addEventListener('click', function(e) {
    const iconCard = e.target.closest('.icon-card');
    if (iconCard) {
        const iconName = iconCard.getAttribute('data-icon-name');
        if (iconName) {
            copyToClipboard(iconName);
        }
    }
});

// Search en temps réel
const searchInput = document.getElementById('iconSearch');
if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();

        searchTimeout = setTimeout(() => {
            if (query) {
                searchIcons(query);
            }
        }, 300);
    });
}

function searchIcons(query) {
    console.log('Searching for:', query);
    fetch(`{{ route('admin.icons.search') }}?q=${encodeURIComponent(query)}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Found icons:', data.icons.length);
        displayIcons(data.icons, query);
    })
    .catch(error => {
        console.error('Search error:', error);
        showToast('❌ {{ __('admin.icons_search_error') }}', 'error');
    });
}

let currentOffset = 100;

function loadMoreIcons() {
    const button = event.target;
    button.disabled = true;
    button.innerHTML = '⏳ Chargement...';

    fetch(`{{ route('admin.icons.search') }}?offset=${currentOffset}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const list = document.getElementById('iconsList');
        const grid = document.getElementById('iconsGrid');

        if (data.icons.length === 0) {
            button.remove();
            showToast('✅ Tous les icons sont affichés', 'success');
            return;
        }

        // Ajouter les nouveaux icons à la grille
        data.icons.forEach(icon => {
            const escapedName = icon.name.replace(/"/g, '&quot;');
            const displayName = icon.type === 'simple' ? 'si-' + escapedName : escapedName;

            const iconHtml = `
                <div class="icon-card group relative p-4 bg-slate-700 rounded hover:bg-slate-600 transition cursor-pointer"
                     data-icon-name="${displayName}">
                    <div class="mb-3 flex justify-center h-12 text-slate-300">
                        ${icon.svg || '<svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle></svg>'}
                    </div>
                    <p class="text-xs text-slate-300 text-center truncate font-mono">${displayName}</p>
                    <p class="text-xs text-blue-400 text-center truncate">${icon.label || ''}</p>
                    <div class="hidden group-hover:block absolute inset-0 bg-black/80 rounded flex items-center justify-center z-10">
                        <div class="text-center">
                            <p class="text-white text-xs font-semibold mb-1">📋 {{ __('admin.icons_click_copy') }}</p>
                            <p class="text-slate-300 text-xs font-mono break-all">${displayName}</p>
                        </div>
                    </div>
                </div>
            `;
            list.innerHTML += iconHtml;
        });

        currentOffset += data.icons.length;

        // Mettre à jour le compteur
        const displayedCount = document.getElementById('displayedCount');
        const totalCount = document.getElementById('totalCount');
        const counter = document.getElementById('iconsCounter');

        if (displayedCount) {
            displayedCount.textContent = currentOffset;
        }
        if (totalCount) {
            totalCount.textContent = data.total;
        }

        // Mettre à jour ou supprimer le bouton
        if (currentOffset >= data.total) {
            button.remove();
            const message = document.createElement('p');
            message.className = 'text-slate-500 text-sm text-center mt-4';
            message.innerHTML = '✅ Tous les icons sont affichés';
            grid.appendChild(message);
        } else {
            button.disabled = false;
            button.innerHTML = '📥 Charger plus';
        }

        showToast(`✅ ${data.icons.length} icons chargés`, 'success');
    })
    .catch(error => {
        console.error('Load error:', error);
        button.disabled = false;
        button.innerHTML = '📥 Charger plus';
        showToast('❌ Erreur lors du chargement', 'error');
    });
}

function displayIcons(icons, query) {
    const grid = document.getElementById('iconsGrid');

    if (icons.length === 0) {
        grid.innerHTML = `
            <div class="text-center py-12 col-span-full">
                <p class="text-slate-400 text-lg">❌ {{ __('admin.icons_no_found') }} "${query}"</p>
                <p class="text-slate-500 text-sm mt-2">{{ __('admin.icons_try_search') }}</p>
            </div>
        `;
        return;
    }

    let html = '<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">';
    icons.forEach(icon => {
        const escapedName = icon.name.replace(/"/g, '&quot;');
        const displayName = icon.type === 'simple' ? 'si-' + escapedName : escapedName;
        html += `
            <div class="icon-card group relative p-4 bg-slate-700 rounded hover:bg-slate-600 transition cursor-pointer"
                 data-icon-name="${displayName}">
                <div class="mb-3 flex justify-center h-12 text-slate-300">
                    ${icon.svg || '<svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle></svg>'}
                </div>
                <p class="text-xs text-slate-300 text-center truncate font-mono">${displayName}</p>
                <p class="text-xs text-blue-400 text-center truncate">${icon.label || ''}</p>
                <div class="hidden group-hover:block absolute inset-0 bg-black/80 rounded flex items-center justify-center z-10">
                    <div class="text-center">
                        <p class="text-white text-xs font-semibold mb-1">📋 {{ __('admin.icons_click_copy') }}</p>
                        <p class="text-slate-300 text-xs font-mono break-all">${displayName}</p>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';

    grid.innerHTML = html;
}

function copyToClipboard(text) {
    console.log('Copying:', text);

    // Utiliser une textarea comme fallback
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();

    try {
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast(`✅ {{ __('admin.icons_copied', ['name' => '']) }}`.replace('""', `"${text}"`), 'success');
    } catch (err) {
        document.body.removeChild(textarea);
        console.error('Copy failed:', err);

        // Fallback avec clipboard API
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showToast(`✅ {{ __('admin.icons_copied', ['name' => '']) }}`.replace('""', `"${text}"`), 'success');
            }).catch(() => {
                showToast('❌ {{ __('admin.icons_copy_failed') }}', 'error');
            });
        } else {
            showToast('❌ {{ __('admin.icons_copy_failed') }}', 'error');
        }
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
    toast.innerHTML = `
        <div class="fixed top-4 right-4 ${bgColor} text-white px-4 py-3 rounded shadow-lg z-50 max-w-xs">
            ${message}
        </div>
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

console.log('Icon picker script loaded');
</script>
