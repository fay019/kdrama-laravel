document.addEventListener('DOMContentLoaded', function() {
    // Handle all action button clicks
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.toggle-watchlist-btn, .toggle-watching-btn, .toggle-watched-btn');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const contentId = btn.dataset.contentId;
        let endpoint = '';

        if (btn.classList.contains('toggle-watchlist-btn')) {
            endpoint = `/api/watchlist/toggle/${contentId}`;
        } else if (btn.classList.contains('toggle-watching-btn')) {
            endpoint = `/api/watching/toggle/${contentId}`;
        } else if (btn.classList.contains('toggle-watched-btn')) {
            endpoint = `/api/watched/toggle/${contentId}`;
        }

        if (!endpoint) return;

        try {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.animation = 'spin 1s linear infinite';

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();

                // Update button state (add/remove 'active' class and data-label)
                const card = btn.closest('.content-card');
                if (card) {
                    // Update button states based on response
                    const watchlistBtn = card.querySelector('.toggle-watchlist-btn');
                    const watchingBtn = card.querySelector('.toggle-watching-btn');
                    const watchedBtn = card.querySelector('.toggle-watched-btn');

                    if (watchlistBtn) {
                        data.inWatchlist ? watchlistBtn.classList.add('active') : watchlistBtn.classList.remove('active');
                        const watchlistLabel = data.inWatchlist
                            ? window.i18n['removed_from_watchlist_suffix']
                            : window.i18n['btn_list'];
                        watchlistBtn.setAttribute('data-label', watchlistLabel);
                    }
                    if (watchingBtn) {
                        data.inWatching ? watchingBtn.classList.add('active') : watchingBtn.classList.remove('active');
                        const watchingLabel = data.inWatching
                            ? window.i18n['removed_from_watching_suffix']
                            : window.i18n['btn_watching'];
                        watchingBtn.setAttribute('data-label', watchingLabel);
                    }
                    if (watchedBtn) {
                        data.inWatched ? watchedBtn.classList.add('active') : watchedBtn.classList.remove('active');
                        const watchedLabel = data.inWatched
                            ? window.i18n['removed_from_watched_suffix']
                            : window.i18n['btn_watched'];
                        watchedBtn.setAttribute('data-label', watchedLabel);
                    }

                    // Update status badges
                    const badgeContainer = card.querySelector('.status-badges-container');
                    if (badgeContainer) {
                        badgeContainer.innerHTML = '';

                        if (data.inWatched) {
                            const watchedBadge = document.createElement('span');
                            watchedBadge.className = 'status-badge bg-green-500/90 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg flex items-center gap-1';
                            watchedBadge.textContent = window.i18n['watchlist_badge_watched'] || '✅ Watched';
                            badgeContainer.appendChild(watchedBadge);
                        } else if (data.inWatching) {
                            const watchingBadge = document.createElement('span');
                            watchingBadge.className = 'status-badge bg-amber-500/90 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg flex items-center gap-1';
                            watchingBadge.textContent = window.i18n['watchlist_badge_watching'] || '🎬 Watching';
                            badgeContainer.appendChild(watchingBadge);
                        } else if (data.inWatchlist) {
                            const watchlistBadge = document.createElement('span');
                            watchlistBadge.className = 'status-badge bg-red-500/90 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg flex items-center gap-1';
                            watchlistBadge.textContent = window.i18n['watchlist_badge_to_watch'] || '📺 To Watch';
                            badgeContainer.appendChild(watchlistBadge);
                        }
                    }

                    // Show toast message
                    if (typeof showToast === 'function') {
                        showToast(data.message, 'success');
                    }
                }
            }
        } catch (err) {
            console.error('Error:', err);
            if (typeof showToast === 'function') {
                showToast('❌ Erreur lors de l\'action', 'error');
            }
        } finally {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.animation = 'none';
        }
    });
});
