@props(['inputName' => 'icon'])

<!-- Icon Picker Modal -->
<div id="iconPickerModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-slate-800 rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="bg-slate-700 p-4 border-b border-slate-600 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-white">🎨 Icon Picker</h3>
            <button onclick="closeIconPicker()" class="text-slate-400 hover:text-white text-2xl">✕</button>
        </div>

        <!-- Search -->
        <div class="p-4 border-b border-slate-600">
            <input type="text"
                   id="iconSearchInput"
                   placeholder="Search icons (brand-twitter, mail, link...)"
                   class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500 focus:outline-none"
                   onkeyup="searchIcons()">
        </div>

        <!-- Icons Grid -->
        <div class="flex-1 overflow-y-auto p-4">
            <div id="iconsGrid" class="grid grid-cols-4 md:grid-cols-6 gap-2">
                <!-- Loaded via JavaScript -->
            </div>
            <div id="noResults" class="hidden text-center py-8 text-slate-400">
                No icons found
            </div>
            <div id="loadingSpinner" class="hidden text-center py-8">
                <p class="text-slate-400">Loading...</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-700 p-4 border-t border-slate-600 text-xs text-slate-400">
            <p>💡 Click any icon to select it • Search by name (e.g., brand-, mail, link)</p>
        </div>
    </div>
</div>

<script>
let currentIconInput = null;
let allIconsCache = [];

function openIconPicker(inputElement) {
    currentIconInput = inputElement;
    document.getElementById('iconPickerModal').classList.remove('hidden');
    document.getElementById('iconSearchInput').focus();

    // Load icons on first open
    if (allIconsCache.length === 0) {
        loadIcons('');
    } else {
        displayIcons(allIconsCache);
    }
}

function closeIconPicker() {
    document.getElementById('iconPickerModal').classList.add('hidden');
    currentIconInput = null;
}

function searchIcons() {
    const query = document.getElementById('iconSearchInput').value.toLowerCase();
    loadIcons(query);
}

function loadIcons(query) {
    const spinner = document.getElementById('loadingSpinner');
    const grid = document.getElementById('iconsGrid');

    spinner.classList.remove('hidden');
    grid.innerHTML = '';

    fetch(`{{ route('admin.icons.search') }}?q=${encodeURIComponent(query)}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        spinner.classList.add('hidden');

        if (query === '') {
            allIconsCache = data.icons;
        }

        displayIcons(data.icons, data.total);
    });
}

function displayIcons(icons, total = null) {
    const grid = document.getElementById('iconsGrid');
    const noResults = document.getElementById('noResults');

    grid.innerHTML = '';

    if (icons.length === 0) {
        noResults.classList.remove('hidden');
        return;
    }

    noResults.classList.add('hidden');

    // Get number of columns (4 for mobile, 6 for md and up)
    const colsPerRow = window.innerWidth >= 768 ? 6 : 4;

    icons.forEach((iconData, index) => {
        // Si on reçoit un objet avec name et svg
        const iconName = iconData.name || iconData;
        const iconType = iconData.type || 'tabler';
        const svgContent = iconData.svg || null;
        const displayName = iconType === 'simple' ? 'si-' + iconName : iconName;

        // Check if in first row
        const isFirstRow = index < colsPerRow;
        const tooltipPosition = isFirstRow ? 'top-full mt-1' : 'bottom-full mb-1';

        const iconEl = document.createElement('div');
        iconEl.className = 'p-2 bg-slate-700 rounded hover:bg-slate-600 transition cursor-pointer flex items-center justify-center group relative';
        iconEl.title = displayName;
        iconEl.onclick = () => selectIcon(iconName, iconType);

        if (svgContent) {
            iconEl.innerHTML = `
                <div class="text-slate-400 group-hover:text-white transition w-6 h-6 flex items-center justify-center">
                    ${svgContent}
                </div>
                <div class="hidden group-hover:block absolute ${tooltipPosition} bg-black text-white text-xs px-2 py-1 rounded whitespace-nowrap z-50">
                    ${displayName}
                </div>
            `;
        } else {
            iconEl.innerHTML = `
                <div class="text-slate-400 group-hover:text-white transition w-6 h-6 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                    </svg>
                </div>
                <div class="hidden group-hover:block absolute ${tooltipPosition} bg-black text-white text-xs px-2 py-1 rounded whitespace-nowrap z-50">
                    ${displayName}
                </div>
            `;
        }

        grid.appendChild(iconEl);
    });
}

function selectIcon(iconName, iconType = 'tabler') {
    if (currentIconInput) {
        // Add si- prefix for Simple Icons
        const displayName = iconType === 'simple' ? 'si-' + iconName : iconName;
        currentIconInput.value = displayName;
        currentIconInput.dispatchEvent(new Event('change', { bubbles: true }));

        // Show toast
        showToast(`✅ Selected: ${displayName}`);

        // Close modal after selection
        setTimeout(() => closeIconPicker(), 300);
    }
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50 animate-fade-in';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => toast.remove(), 2000);
}

// Close modal on outside click
document.addEventListener('click', function(event) {
    const modal = document.getElementById('iconPickerModal');
    if (event.target === modal) {
        closeIconPicker();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeIconPicker();
    }
});
</script>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
