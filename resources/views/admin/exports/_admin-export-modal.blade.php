<div id="adminExportModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-90" onclick="closeAdminExportModal()" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Content -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <!-- Close button -->
            <div class="absolute top-4 right-4 z-10">
                <button onclick="closeAdminExportModal()" class="text-slate-400 hover:text-white p-2 bg-slate-800 rounded-full transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-6 sm:px-8">
                <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                    <span>📥</span> Exporter la watchlist
                </h3>
                <p class="text-purple-100 text-sm mt-2" id="adminUserName">Utilisateur</p>
            </div>

            <!-- Form -->
            <form id="adminExportForm" class="p-6 sm:p-8 space-y-6">
                @csrf
                <input type="hidden" id="exportUserId" name="user_id" value="">

                <!-- Filtres -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <span>🔍</span> Filtres
                    </h4>
                    <div class="space-y-3 bg-slate-800/50 p-4 rounded-lg border border-slate-700">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="filters[to_watch]" value="1" checked class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">📺 À regarder</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="filters[watching]" value="1" checked class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">🎬 En train de voir</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="filters[watched]" value="1" checked class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">✅ Regardés</span>
                        </label>
                    </div>
                </div>

                <!-- Colonnes -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <span>📋</span> Colonnes à inclure
                    </h4>
                    <div class="space-y-3 bg-slate-800/50 p-4 rounded-lg border border-slate-700">
                        <label class="flex items-center gap-3 cursor-pointer group" id="adminPosterLabel">
                            <input type="checkbox" name="columns[poster]" value="1" checked class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-purple-500 cursor-pointer" id="adminPosterCheckbox">
                            <span class="text-slate-200 group-hover:text-white transition">🖼️ Images Poster</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="columns[title]" value="1" checked disabled class="w-5 h-5 rounded bg-slate-600 border-slate-500 text-purple-500 cursor-not-allowed opacity-75">
                            <span class="text-slate-300">Titre (obligatoire)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="columns[status]" value="1" checked class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">Statut</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="columns[rating]" value="1" checked class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">⭐ Rating personnel</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="columns[year]" value="1" checked class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">📅 Année</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="columns[vote_average]" value="1" checked class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">🎯 Vote TMDB</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="columns[genres]" value="1" checked class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">🎭 Genres</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="columns[synopsis]" value="1" class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">📖 Synopsis</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="columns[networks]" value="1" class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">📺 Networks (Netflix, etc.)</span>
                        </label>
                    </div>
                </div>

                <!-- Tri -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <span>↕️</span> Tri
                    </h4>
                    <div class="space-y-3 bg-slate-800/50 p-4 rounded-lg border border-slate-700">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="sort" value="added_at" checked class="w-5 h-5 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">📅 Par date d'ajout (récent)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="sort" value="title" class="w-5 h-5 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">A-Z 📝 Par titre</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="sort" value="rating" class="w-5 h-5 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">⭐ Par rating personnel</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="sort" value="vote_average" class="w-5 h-5 text-purple-500 cursor-pointer">
                            <span class="text-slate-200 group-hover:text-white transition">🎯 Par vote TMDB</span>
                        </label>
                    </div>
                </div>

                <!-- Format -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <span>💾</span> Format
                    </h4>
                    <div class="space-y-3 bg-slate-800/50 p-4 rounded-lg border border-slate-700">
                        <label class="flex items-center gap-3 cursor-pointer group p-3 rounded-lg bg-purple-600/20 border border-purple-500/50">
                            <input type="radio" name="format" value="pdf" checked class="w-5 h-5 text-purple-500 cursor-pointer">
                            <div>
                                <span class="text-white font-bold">📄 PDF</span>
                                <p class="text-sm text-slate-300">Avec styling coloré et options visibles</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group p-3 rounded-lg hover:bg-slate-700/50 transition">
                            <input type="radio" name="format" value="csv" class="w-5 h-5 text-purple-500 cursor-pointer">
                            <div>
                                <span class="text-white font-bold">📊 CSV</span>
                                <p class="text-sm text-slate-300">Pour import dans Excel/Sheets</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Envoi par email -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <span>📧</span> Notification
                    </h4>
                    <label class="flex items-center gap-3 cursor-pointer group p-4 rounded-lg bg-blue-600/10 border border-blue-500/30 hover:border-blue-500/50 transition">
                        <input type="checkbox" name="send_email" value="1" class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-blue-500 cursor-pointer">
                        <div>
                            <span class="text-white font-bold">📧 Envoyer le fichier par email</span>
                            <p class="text-sm text-slate-300">L'utilisateur recevra l'export directement dans sa boîte mail</p>
                        </div>
                    </label>
                </div>

                <!-- Boutons -->
                <div class="flex gap-3 pt-4 border-t border-slate-700">
                    <button type="button" onclick="closeAdminExportModal()" class="flex-1 px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-lg transition" id="adminExportCancelBtn">
                        Annuler
                    </button>
                    <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-bold rounded-lg transition flex items-center justify-center gap-2" id="adminExportSubmitBtn">
                        <span>📥</span> Exporter
                    </button>
                </div>

                <!-- Spinner de chargement -->
                <div id="adminExportSpinner" class="hidden mt-4 flex items-center justify-center gap-3 p-4 bg-slate-800 rounded-lg">
                    <div class="animate-spin">
                        <svg class="w-5 h-5 text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <span class="text-slate-300 font-semibold" id="adminSpinnerText">Génération du PDF en cours...</span>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAdminExportModal(userId, userName) {
    document.getElementById('exportUserId').value = userId;
    document.getElementById('adminUserName').textContent = userName;
    document.getElementById('adminExportModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setupAdminFormatToggle();
}

function setupAdminFormatToggle() {
    const formatRadios = document.querySelectorAll('#adminExportForm input[name="format"]');
    const posterLabel = document.getElementById('adminPosterLabel');
    const posterCheckbox = document.getElementById('adminPosterCheckbox');

    formatRadios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            if (e.target.value === 'csv') {
                posterLabel.classList.add('opacity-50', 'cursor-not-allowed');
                posterCheckbox.disabled = true;
                posterCheckbox.checked = false;
            } else {
                posterLabel.classList.remove('opacity-50', 'cursor-not-allowed');
                posterCheckbox.disabled = false;
                posterCheckbox.checked = true;
            }
        });
    });
}

function closeAdminExportModal() {
    document.getElementById('adminExportModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

document.getElementById('adminExportForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    const userId = formData.get('user_id');
    const data = {
        format: formData.get('format'),
        filters: {
            watched: formData.has('filters[watched]'),
            to_watch: formData.has('filters[to_watch]'),
            watching: formData.has('filters[watching]'),
        },
        columns: {
            poster: formData.has('columns[poster]'),
            title: true,
            status: formData.has('columns[status]'),
            rating: formData.has('columns[rating]'),
            year: formData.has('columns[year]'),
            vote_average: formData.has('columns[vote_average]'),
            genres: formData.has('columns[genres]'),
            synopsis: formData.has('columns[synopsis]'),
            networks: formData.has('columns[networks]'),
        },
        sort: formData.get('sort'),
        send_email: formData.has('send_email'),
    };

    showAdminExportSpinner();

    try {
        updateAdminSpinnerText('Génération du PDF... cela peut prendre quelques secondes');

        const response = await fetch(`/admin/exports/user/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(data),
        });

        if (!response.ok) throw new Error('Export failed');

        updateAdminSpinnerText('Téléchargement du fichier...');

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = response.headers.get('content-disposition')?.split('filename="')[1]?.slice(0, -1) || `watchlist_${data.format}`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        hideAdminExportSpinner();
        closeAdminExportModal();
        showToast('✅ Export réussi!', 'success');
    } catch (error) {
        console.error('Error:', error);
        hideAdminExportSpinner();
        showToast('❌ Erreur lors de l\'export', 'error');
    }
});

function showAdminExportSpinner() {
    const spinner = document.getElementById('adminExportSpinner');
    const submitBtn = document.getElementById('adminExportSubmitBtn');
    const cancelBtn = document.getElementById('adminExportCancelBtn');

    spinner.classList.remove('hidden');
    submitBtn.disabled = true;
    cancelBtn.disabled = true;
    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    cancelBtn.classList.add('opacity-50', 'cursor-not-allowed');
}

function hideAdminExportSpinner() {
    const spinner = document.getElementById('adminExportSpinner');
    const submitBtn = document.getElementById('adminExportSubmitBtn');
    const cancelBtn = document.getElementById('adminExportCancelBtn');

    spinner.classList.add('hidden');
    submitBtn.disabled = false;
    cancelBtn.disabled = false;
    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    cancelBtn.classList.remove('opacity-50', 'cursor-not-allowed');
}

function updateAdminSpinnerText(text) {
    const spinnerText = document.getElementById('adminSpinnerText');
    if (spinnerText) {
        spinnerText.textContent = text;
    }
}

function showToast(message, type = 'success') {
    const existingToast = document.querySelector('.toast');
    if (existingToast) existingToast.remove();

    const toast = document.createElement('div');
    toast.className = `toast fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-semibold shadow-lg z-50 animate-in fade-in slide-in-from-top`;
    toast.classList.add(type === 'success' ? 'bg-green-600' : 'bg-red-600');
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'fade-out 0.3s ease-out forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slide-in {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .animate-in {
        animation: fade-in 0.3s ease-out;
    }

    .fade-in {
        animation: fade-in 0.3s ease-out;
    }

    .slide-in-from-top {
        animation: slide-in 0.3s ease-out;
    }

    @keyframes fade-out {
        from { opacity: 1; }
        to { opacity: 0; }
    }
</style>
