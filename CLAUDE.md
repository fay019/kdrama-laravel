# 🎬 KDrama Laravel - Documentation Complète

**Date de mise à jour:** 2026-04-16
**Dernier développement:** Professional Async Queue Setup + Admin Jobs Dashboard + Dual Catalog View System

---

## 🔄 Recent Changes (2026-04-16)

### ✅ Professional Async Queue Setup - Database-Backed Job Processing
**Major Addition:** Transitioned from synchronous to professional asynchronous job queue with worker processes

**Features Implemented:**
- ✅ **Queue Driver:** Changed from `sync` to `database` (MySQL-backed persistent jobs)
- ✅ **Queue Tables:** `jobs` and `failed_jobs` tables for reliable job storage
- ✅ **Admin Jobs Dashboard** (`/admin/jobs`):
  - 3 Quick Action Cards: Sync Actors, Cleanup PDFs, Update Production
  - Live Pending Queue Table with 2-second auto-refresh
  - Failed Jobs Management (Retry/Delete buttons)
  - Live Logs Viewer (last 100 lines from `storage/logs/jobs.log`)
- ✅ **Isolated Job Logging:** Separate `storage/logs/jobs.log` channel for job-specific output
- ✅ **SyncPopularActors Job:** Updated with 300-second timeout, isolated logging, proper error handling
- ✅ **Queue Worker Execution:** Professional async worker process (`php artisan queue:work --timeout=300`)

**Configuration Changes:**
- `.env`: `QUEUE_CONNECTION=database` (was `sync`)
- `config/logging.php`: Added `jobs` channel pointing to `storage/logs/jobs.log`
- `app/Jobs/SyncPopularActors.php`: Added `set_time_limit(300)` and `Log::channel('jobs')`
- `config/queue.php`: Already configured for database driver with proper timeouts

**Database Schema:**
```
jobs table:           Stores pending/available jobs
failed_jobs table:    Stores failed job records with exception details
job_batches table:    Stores batch job information
```

**Admin Routes Added:**
- `GET /admin/jobs` - Main dashboard
- `GET /admin/jobs/history` - Job execution history
- `POST /admin/jobs/dispatch` - Dispatch SyncPopularActors job
- `POST /admin/jobs/run-command` - Run Artisan commands
- `DELETE /admin/jobs/pending/{jobId}` - Delete pending job from queue
- `POST /admin/jobs/failed/{uuid}/retry` - Retry failed job
- `DELETE /admin/jobs/failed/{uuid}` - Delete failed job
- `GET /admin/jobs/logs` - Fetch live logs (AJAX)
- `POST /admin/jobs/logs/clear` - Clear all job logs

**Job History Tracking System:**
- ✅ New `job_history` table stores all job executions (completed & failed)
- ✅ JobHistory model with metadata support (JSON)
- ✅ `/admin/jobs/history` page with:
  - Desktop table view (job, status, duration, output, date)
  - Mobile card view
  - Pagination (20 per page)
  - Metadata display (dramas_processed, actors_synced, etc.)
- ✅ SyncPopularActors records:
  - Execution start/end times
  - Duration in seconds
  - Status (completed/failed)
  - Output summary and exception details
  - Custom metadata for each job run

**UI/UX Enhancements:**
- ✅ **Colorized Live Logs** with syntax highlighting:
  - Timestamps (gray)
  - INFO/ERROR/WARNING levels (blue/red/amber)
  - Success keywords (green)
  - Numbers/stats (yellow)
  - Key info (cyan)
  - Start/End messages with ▶/✓/✗ indicators
- ✅ **Color Legend** below logs with explanation
- ✅ **Delete Pending Jobs** feature:
  - Delete button in pending queue table (desktop)
  - Delete button in pending queue cards (mobile)
  - Confirmation dialog before deletion
- ✅ **Clear Logs Button** with confirmation
- ✅ **Whitespace preservation** in logs (whitespace-pre-wrap CSS)
- ✅ **Flexible wrap layout** for action buttons

**Memory & Performance Optimization:**
- ✅ **Batch size reduced** from 500 to 100 (5568 actors → ~56 queries instead of 12)
- ✅ **Memory limit increased:**
  - `.user.ini`: `memory_limit = 256M` (was 128M)
  - Queue worker launch: `php -d memory_limit=512M artisan queue:work --timeout=300`
- ✅ **Execution time:** `max_execution_time = 300` seconds (5 minutes)
- ✅ **Verified:** SyncPopularActors completes in ~1m 45s with 5568 actors

**Translation Keys Added (FR/EN/DE):**
- 40+ keys for jobs management (section_jobs, nav_jobs_monitor, jobs_dispatched, etc.)
- 10+ keys for job history (jobs_history_title, jobs_status_completed, jobs_completed_at, etc.)
- Sidebar integration with collapsible 'Jobs & Tasks' section
- Sidebar link to Job History page (📜 Job History)

**Documentation:**
- ✅ New file: `QUEUE.md` - Complete guide to queue setup, worker configuration, monitoring, and troubleshooting
- ✅ CLAUDE.md updated with all 2026-04-16 changes
- ✅ Quick start: Run `php -d memory_limit=512M artisan queue:work --timeout=300` in separate terminal
- ✅ Verification: Watch `/admin/jobs` dashboard for real-time job processing and history

**Files Modified/Created:**
- `app/Http/Controllers/Admin/AdminJobsController.php` - NEW (6 methods for job management)
- `app/Models/JobHistory.php` - NEW (job execution history model)
- `resources/views/admin/jobs/index.blade.php` - NEW (dashboard with live logs, queue, failed jobs)
- `resources/views/admin/jobs/history.blade.php` - NEW (job history page with pagination)
- `database/migrations/2026_04_16_043912_create_job_history_table.php` - NEW
- `config/logging.php` - Added jobs channel
- `app/Jobs/SyncPopularActors.php` - Updated with:
  - Reduced batch size (500 → 100)
  - JobHistory recording on success/failure
  - Proper start time tracking
  - Metadata collection
- `routes/web.php` - Added 9 new admin routes
- `resources/views/components/admin-sidebar.blade.php` - Added jobs section with history link
- `lang/{fr|en|de}/admin.php` - Added 50+ translation keys
- `QUEUE.md` - NEW comprehensive queue documentation
- `.user.ini` - NEW (max_execution_time = 300, memory_limit = 256M)

**Production Deployment:**
1. Run: `php -d memory_limit=512M artisan queue:work --timeout=300` in background process or supervisor
2. Monitor via `/admin/jobs` dashboard in real-time
3. Check job history at `/admin/jobs/history` for past executions
4. View detailed logs in `storage/logs/jobs.log`
5. For production, use Supervisor/systemd to manage worker process lifecycle

---

### ✅ Content Reporting & Moderation System (2026-04-16)
**Major Addition:** Complete content report workflow with admin moderation

**Features Implemented:**
- ✅ **Report Button** on drama/actor cards (desktop & mobile)
- ✅ **Report Modal** with:
  - Category selection (inappropriate content, spam, other)
  - Custom message textarea
  - Submit button with validation
- ✅ **Admin Dashboard** at `/admin/contact` with:
  - Status workflow (pending → in_progress → resolved/spam)
  - Report type display
  - Message preview with full text
  - Mark as spam button
- ✅ **Email Notifications** to admin with:
  - Report type and category
  - User message
  - Drama/actor details
  - Status tracking link
- ✅ **Database:** `contact_messages` table stores all reports
- ✅ **Translations:** Full FR/EN/DE support

**Files Modified:**
- `app/Http/Controllers/ContentController.php` - Added reportContent() method
- `resources/views/kdrams/show.blade.php` - Added report button
- `resources/views/kdrams/_card.blade.php` - Added report button
- `resources/views/kdrams/_actor_card.blade.php` - Added report button
- `resources/views/admin/contact/` - Report management pages
- `lang/{fr|en|de}/show.php` - Report translations

### ✅ Adult Content Marking System (2026-04-16)
**Major Addition:** Admin ability to mark K-dramas as adult content

**Features Implemented:**
- ✅ **Toggle in Admin** - Mark kdrama as `adult_only` in `/admin/kdrama/{id}/edit`
- ✅ **User Filter** - `?hide_adult=1` parameter to exclude adult content
- ✅ **Default Behavior** - Adult content hidden by default for guests
- ✅ **Database** - `kdramas.adult_only` boolean column
- ✅ **Translations** - Settings available in FR/EN/DE

**Files Modified:**
- `app/Models/Kdrama.php` - Added adult_only column
- `app/Http/Controllers/ContentController.php` - Added adult filtering logic
- `database/migrations/` - Added adult_only column migration
- `lang/{fr|en|de}/catalog.php` - Filter translations

### ✅ Actor Detail Caching & Enhanced Display (2026-04-16)
**Major Addition:** Intelligent caching system for actor details with combined credits

**Features Implemented:**
- ✅ **Combined Credits** - `combined_credits` JSON column on actors table
- ✅ **Lazy-Load Caching** - Actor details cached on first view
- ✅ **Cache Duration** - 30 days before re-fetching from TMDB
- ✅ **Detail Modal** - Enhanced actor bio with:
  - Combined filmography from cache
  - Profile image with fallback
  - Popularity score
  - Birth date and place
- ✅ **Database** - Efficient storage with `last_synced_at` tracking

**Performance Gains:**
- Actor modal loads instantly from cache
- Reduced TMDB API calls
- Better user experience on actor cards

**Files Modified:**
- `app/Models/Actor.php` - Added combined_credits column
- `app/Services/TmdbService.php` - Cache logic added
- `resources/views/kdrams/_actor_modal.blade.php` - Enhanced display
- `database/migrations/` - Added combined_credits column

### ✅ Catalog Pagination Preservation (2026-04-16)
**Bug Fix:** Global pagination counts now preserved during local filtering

**Issue:** Switching between drama/actor views reset pagination to page 1

**Solution:**
- Preserve global `page` parameter in view switch
- Keep local filter counts separate from global pagination
- Updated routes to maintain pagination state

**Files Modified:**
- `app/Http/Controllers/ContentController.php` - Fixed pagination logic
- `resources/views/kdrams/index.blade.php` - Updated view switch links

### ✅ Actor Search Improvements (2026-04-15)
**Enhancement:** Better actor discovery with improved search

**Features:**
- ✅ Reduced TMDB pages scanned (100 → 50 for better performance)
- ✅ Smart API fallback when actor database < 50 records
- ✅ Pagination improvements
- ✅ Better filtering

**Files Modified:**
- `app/Services/TmdbService.php` - Optimized search logic
- `tests/Feature/SearchReliabilityTest.php` - New test suite

### ✅ Settings Management Enhancements (2026-04-15)
**Enhancement:** Improved settings CRUD with sensitivity flags

**Features:**
- ✅ **Sensitive Settings** - Mark fields as sensitive (API keys, secrets)
- ✅ **Custom Groups** - Organize settings by category
- ✅ **Fallback Descriptions** - Auto-generate for custom groups
- ✅ **Validation** - Full form validation on updates

**Files Modified:**
- `database/migrations/` - Added sensitivity columns
- `app/Models/Setting.php` - Added sensitivity support
- `resources/views/admin/settings/` - Enhanced UI
- Admin settings page with sensitivity indicators

### ✅ Complete Multilingual Coverage (2026-04-15)
**Major Addition:** 100% translation of all pages (FR/EN/DE)

**Coverage:**
- ✅ All 9 language files complete
- ✅ 500+ translation keys
- ✅ All admin pages translated
- ✅ All user-facing content translated
- ✅ Error messages translated
- ✅ Success messages translated

**Files Updated:**
- `lang/{fr|en|de}/*.php` - All files 100% complete
- Database: `users.preferred_language` column
- Middleware: `SetLocale.php` for automatic detection
- Footer & profile selectors for language switching

---

### ✅ Dual Catalog View System - Dramas & Actors Explorer
**Major Addition:** Complete actor browsing and search system integrated into the catalog page

**Features Implemented:**
- ✅ **Toggle View**: Switch between `?view=dramas` (default) and `?view=actors` in catalog
- ✅ **Actor Cards**: New `_actor_card.blade.php` component displaying actor photos with known works
- ✅ **Search Actors**: Full-text search across all TMDB actors with pagination
- ✅ **Filter Options**: 
  - `exact_name=true` - Match actor names exactly (case-insensitive)
  - `has_photo=true` - Show only actors with profile images
- ✅ **Popular Actors**: Default view when no search query shows trending actors
- ✅ **Click to View**: Click any actor card to open their modal with full bio and filmography

**New Methods in TmdbService:**
```php
// Retrieve popular actors paginated
getPopularActors(int $page = 1, bool $hasPhoto = false): array

// Search actors with filters
searchPerson(string $query, int $page = 1, bool $exactName = false, bool $hasPhoto = false): array
```

**New Route Parameters:**
```
/kdrams?view=actors                     # Show actor catalog
/kdrams?view=actors&search=park%20shin  # Search actors
/kdrams?view=actors&exact_name=true     # Exact name matching
/kdrams?view=actors&has_photo=true      # Show only with photos
/kdrams?view=dramas                     # Default drama view
```

**Database Impact:** None - uses TMDB API only

**Testing:**
- ✅ New test file: `tests/Feature/SearchReliabilityTest.php`
- ✅ Tests cover: Korean content filtering, multi-page scanning, title+actor combination search
- Run: `php artisan test --filter=SearchReliability`

**Files Modified:**
- `app/Http/Controllers/ContentController.php` - Added view switching logic, refactored actor search
- `app/Services/TmdbService.php` - New actor search methods with filters
- `resources/views/kdrams/index.blade.php` - Added actor view layout + view toggle
- `resources/views/kdrams/_actor_card.blade.php` - **NEW** actor card component
- `resources/views/kdrams/_actor_modal.blade.php` - Enhanced actor modal (full bio, social links)
- `lang/*/catalog.php` - Translation keys for actor view
- `routes/web.php` - No changes required (uses existing route)

**UI/UX:**
- Actor cards display poster-style with hover zoom effect
- Known works displayed below actor name
- Fallback placeholder for actors without profile photos
- Seamless toggle between dramas and actors view
- Search filters work identically to drama search

---

## ⚠️ IMPORTANT - Git Commits

**NE JAMAIS faire de commit automatiquement.**

Les commits doivent TOUJOURS être demandés explicitement par l'utilisateur avec la commande `/commit` ou en disant "ok commit".

Si des modifications sont détectées:
1. ✅ Informer l'utilisateur qu'il y a des changements
2. ✅ Afficher un résumé des fichiers modifiés
3. ❌ NE PAS créer de commit sans autorisation explicite
4. ⏳ Attendre l'instruction de l'utilisateur

---

## 📋 Vue d'ensemble du projet

**KDrama Hub** est une plateforme Laravel moderne pour découvrir, suivre et exporter des K-Dramas avec système de notation, cache PDF, et panel admin complet.

### Stack Technique
- **Framework:** Laravel 12
- **Frontend:** Tailwind CSS + Alpine.js
- **Database:** MySQL
- **Auth:** Laravel Breeze
- **Icons:**
  - Tabler Icons (@tabler/icons) - 5000+ general UI icons
  - Simple Icons (codeat3/blade-simple-icons) - 1000+ brand logos with automatic Tabler fallback
- **PDF:** Browsershot (Headless Chrome)
- **Caching:** File-based + DB settings

---

## 🏗️ Architecture & Structure

### Répertoires clés
```
app/
├── Http/Controllers/
│   ├── Admin/              # Controllers admin
│   │   ├── AdminAuthorController.php
│   │   ├── AdminContactController.php
│   │   ├── AdminExportController.php
│   │   ├── AdminIconsController.php
│   │   ├── AdminSettingsController.php
│   │   └── AdminUserController.php
│   ├── AdminController.php         # Admin base (redirect)
│   ├── ContactController.php       # Contact form submission
│   ├── ContentController.php       # Kdrama catalog & details
│   ├── DashboardController.php     # User dashboard
│   ├── ProfileController.php       # User profile
│   ├── SetupController.php         # First-time setup wizard
│   └── WatchlistController.php     # Watchlist + exports + ratings
├── Models/
│   ├── User.php            # User avec is_admin
│   ├── Kdrama.php          # Kdrama (TMDB data cached)
│   ├── WatchlistItem.php   # User watchlist + rating column
│   ├── Setting.php         # Key-value settings (DB)
│   ├── SocialLink.php      # Social links pour footer
│   ├── SiteMetadata.php    # SEO metadata
│   ├── ExportLog.php       # Export statistics
│   ├── ContactMessage.php  # Contact form submissions
│   └── StreamingAvailability.php  # RapidAPI cache
├── Services/
│   ├── TmdbService.php             # TMDB API wrapper
│   ├── WatchlistExportService.php  # CSV/PDF exports + cache
│   └── StreamingAvailabilityService.php  # RapidAPI wrapper
├── Jobs/
│   └── SendExportEmail.php  # Queue job pour email exports
└── Mail/
    └── ExportNotification.php  # Email mailable

resources/views/
├── layouts/
│   ├── app.blade.php        # Main layout (hides nav on admin)
│   └── navigation.blade.php # Top navbar
├── components/
│   ├── admin-sidebar.blade.php      # Sidebar admin (desktop + mobile)
│   ├── icon-picker-modal.blade.php  # Icon picker component
│   ├── footer.blade.php             # Social links display
│   ├── production-info.blade.php    # Studios/networks display
│   └── ...
├── admin/
│   ├── dashboard.blade.php          # Admin dashboard
│   ├── users/index.blade.php        # User management
│   ├── settings/index.blade.php     # Settings CRUD
│   ├── author/edit.blade.php        # Author/SEO/Social
│   ├── icons/search.blade.php       # Icon picker page
│   ├── exports/
│   │   ├── cache.blade.php          # Cache management
│   │   ├── stats.blade.php          # Export statistics
│   │   └── _admin-export-modal.blade.php
│   └── contact/index.blade.php      # Contact messages
├── kdrams/
│   ├── show.blade.php               # Detail page (streaming, ratings)
│   ├── _card.blade.php              # Reusable card component
│   └── ...
├── emails/
│   └── export-notification.blade.php  # Email template
└── ...

database/migrations/
├── 0001_01_01_000000_create_users_table.php
├── 0001_01_01_000001_create_cache_table.php
├── 0001_01_01_000002_create_jobs_table.php
├── 2026_03_*_create_kdramas_table.php          # Kdrama avec TMDB data
├── 2026_03_*_create_watchlist_items_table.php  # Watchlist + ratings consolidé
├── 2026_03_*_create_settings_table.php         # Configuration key-value
├── 2026_03_*_create_social_links_table.php     # Social links
├── 2026_03_*_create_site_metadata_table.php    # SEO metadata
├── 2026_03_*_create_streaming_availabilities_table.php  # RapidAPI cache
├── 2026_03_*_create_contact_messages_table.php # Contact forms
├── 2026_03_*_create_export_logs_table.php      # Export tracking
└── 2026_03_*_telescope_entries_table.php       # Debugging tool

routes/
├── web.php  # Toutes les routes (public + admin + api)
└── api.php

storage/app/exports/  # PDFs cachés (7 jours TTL)

lang/
├── fr/                    # Fichiers français
│   ├── common.php         # Textes communs
│   ├── admin.php          # Textes admin
│   ├── catalog.php        # Catalogue
│   ├── contact.php        # Contact
│   ├── show.php           # Pages détail
│   ├── auth.php           # Authentification
│   ├── dashboard.php      # Dashboard
│   ├── watchlist.php      # Watchlist
│   └── emails.php         # Emails
├── en/                    # English files
└── de/                    # Deutsche Dateien
```

---

## 🌍 Localisation & Multilangue

### Vue d'ensemble
Le projet supporte **3 langues complètement traduites** (FR/EN/DE) avec sélection de langue instantanée sur toutes les pages.

### Architecture
- **Défaut:** Français (FR)
- **Fallback:** Anglais (EN)
- **Middleware:** `SetLocale.php` - détecte et applique la locale
  - Priorité: `user->preferred_language` → `session('locale')` → `'fr'`
- **Validation:** Locales supportées = ['fr', 'en', 'de']

### Où Change la Langue ?

**1. Navbar (Pages publiques)**
```blade
<!-- 3 boutons: 🇫🇷 FR | 🇬🇧 EN | 🇩🇪 DE -->
<!-- Route: POST /language/{locale} -->
```

**2. Admin Sidebar (Pages admin)**
```blade
<!-- 3 formulaires en footer du sidebar (desktop + mobile) -->
<!-- Même route: POST /language/{locale} -->
```

**3. Profil Utilisateur**
```blade
<!-- Champ select "Langue préférée" -->
<!-- Sauvegardée dans user.preferred_language -->
<!-- Appliquée automatiquement à chaque connexion -->
```

### Fichiers Clés
- `app/Http/Middleware/SetLocale.php` - Détection et application locale
- `routes/web.php` - Route POST `/language/{locale}` (language.switch)
- `lang/fr|en|de/*.php` - Fichiers de traduction
- `resources/views/components/admin-sidebar.blade.php` - Sélecteur sidebar
- `resources/views/profile/partials/update-profile-information-form.blade.php` - Profil
- `resources/views/layouts/navigation.blade.php` - Navbar

### Pattern d'Utilisation
```blade
<!-- Textes simples -->
{{ __('common.cancel') }} → "Annuler" (FR) / "Cancel" (EN) / "Abbrechen" (DE)

<!-- Avec variables -->
{{ __('contact.thanks', ['name' => $user->name]) }}

<!-- Choisir la clé -->
<!-- common.php → textes partagés -->
<!-- {module}.php → textes spécifiques au module -->
<!-- admin.php → textes admin -->
```

### Database
```sql
ALTER TABLE users ADD preferred_language VARCHAR(5) DEFAULT 'fr';
```
- Défaut: `'fr'`
- Valeurs acceptées: `'fr'`, `'en'`, `'de'`

---

## 🔑 Concepts Clés & Patterns

### 1. **WatchlistItem Model** (Core Data Structure)

#### Structure complète
```php
$item->id                  // Primary key
$item->user_id            // FK → users.id
$item->tmdb_id            // TV show ID from TMDB
$item->is_in_watchlist    // boolean - "À regarder"
$item->is_watched         // boolean - "Regardé"
$item->rating             // int|null - 1=👎, 2=👍, 3=👍👍 (CONSOLIDATED)
$item->notes              // text - Notes personnelles (unused)
$item->added_at           // timestamp - Quand ajouté
$item->updated_at         // timestamp
```

#### Relations
```php
$item->kdrama()     // belongsTo(Kdrama::class, 'tmdb_id', 'tmdb_id')
$item->user()       // belongsTo(User::class)
```

#### Méthodes clés utiles
```php
// Récupérer watchlist de l'utilisateur
WatchlistItem::where('user_id', $userId)
    ->where('is_in_watchlist', true)
    ->with('kdrama')
    ->get();

// Récupérer items regardés avec ratings
WatchlistItem::where('user_id', $userId)
    ->where('is_watched', true)
    ->whereNotNull('rating')
    ->with('kdrama')
    ->get();

// Vérifier si user a regardé un kdrama et sa note
$item = WatchlistItem::where('user_id', $userId)
    ->where('tmdb_id', $tmdbId)
    ->first();

if ($item && $item->is_watched) {
    echo "Note: " . ($item->rating === 1 ? '👎' : '👍');
}
```

**Important:**
- Les ratings sont DANS `watchlist_items.rating` (pas table séparée)
- Un item peut avoir `rating` seulement si `is_watched=true`
- Quand on retire le statut "regardé", on set `rating = null`

---

### 2. **Settings Table** (Configuration Dynamique)

#### Pattern clé-valeur avec groupes
```php
// Dans migrations/seeders:
Setting::set('key', 'value', 'group');

// Structure DB:
id | key | value | group | created_at | updated_at
1  | rapidapi_cache_hours | 48 | api | ...
2  | rapidapi_key | xxx | api | ...
3  | site_name | KDrama Hub | site | ...
```

#### Settings clés utilisées
```php
// API & Caching
Setting::get('rapidapi_cache_hours', 24)    // Durée cache RapidAPI (heures)
Setting::get('rapidapi_key')                 // Clé API RapidAPI
Setting::get('api_source_priority')          // 'env_first' ou 'db_first'
Setting::get('tmdb_api_key')                // Clé TMDB

// Site
Setting::get('site_name')                    // Nom du site
Setting::get('site_tagline')                 // Tagline
Setting::get('footer_text')                  // Texte footer

// Métadonnées
Setting::get('meta_description')             // SEO meta desc
Setting::get('og_image')                     // Image OG tag
```

#### Méthodes utiles
```php
// Get with default
$hours = Setting::get('rapidapi_cache_hours', 24);

// Set (create or update)
Setting::set('my_key', 'my_value', 'my_group');

// Tous les settings d'un groupe
Setting::where('group', 'api')->get();

// Query builder
Setting::where('key', 'like', 'api%')->get();
```

**Pattern:** Toute config qui doit être changeable par l'admin doit aller dans settings!

---

### 3. **Export System** (CSV + PDF + Cache + Email)

#### WatchlistExportService - Méthodes principales

```php
// 1. EXPORT TO CSV
exportToCSV(int $userId, array $options = []): string
├─ Récupère items filtrés
├─ Formate en CSV selon colonnes sélectionnées
└─ Retourne string CSV (pas de cache)

// 2. EXPORT TO PDF
exportToPDF(int $userId, array $options = []): string
├─ Récupère items
├─ Génère HTML via Browsershot
├─ Utilise cache si disponible
└─ Retourne PDF binary string

// 3. EXPORT WITH CACHE (optimisé)
exportToPDFWithCache(int $userId, array $options = []): string
├─ Génère hash cache
├─ Vérifie getCachedPDF()
├─ Si trouvé → retour du cache
├─ Sinon → générer + cachePDF()
└─ Retourne PDF

// 4. CACHE METHODS
generateCacheHash(int $userId, array $options): string
├─ MD5(user_id . filter_watched . filter_to_watch . sort . columns)
└─ Déterministe = même options = même hash

getCachedPDF(int $userId, array $options): ?string
├─ Cherche storage/app/exports/{hash}.pdf
├─ Vérifie création_date < 7 jours
├─ Retourne contenu ou null

cachePDF(string $hash, string $content): void
├─ Sauvegarde storage/app/exports/watchlist_{userId}_{hash}.pdf
└─ Accessible 7 jours
```

#### Options structure
```php
$options = [
    'filters' => [
        'watched' => true,      // Inclure items regardés
        'to_watch' => true,     // Inclure items à regarder
    ],
    'columns' => [
        'poster' => true,       // Image poster
        'title' => true,        // Titre
        'status' => true,       // Statut (Vu/À voir)
        'rating' => true,       // Note utilisateur
        'year' => true,         // Année
        'vote_average' => true, // Vote TMDB
        'genres' => true,       // Genres
        'networks' => false,    // Networks (optionnel)
        'synopsis' => false,    // Synopsis (optionnel)
    ],
    'sort' => 'added_at'  // 'added_at', 'title', 'rating', 'vote_average'
];
```

#### Export Flow
```
User clicks "Export"
    ↓
WatchlistController::export() validates
    ↓
Choose format (CSV or PDF)
    ↓
If PDF:
  ├─ Check cache with getCachedPDF()
  ├─ If found → use cached file
  └─ If not → generatePDF() + cachePDF()
    ↓
Log export to export_logs table
    ↓
If send_email → dispatch SendExportEmail job (async)
    ↓
Download file or queue for email
```

#### Cache file location
```
storage/app/exports/
├─ watchlist_{userId}_{cacheHash}.pdf  (7 days TTL)
├─ watchlist_1_abc123def.pdf
└─ watchlist_2_xyz789abc.pdf
```

---

### 4. **Admin Sidebar** (Collapsible Sections)

#### Structure & State
```html
<!-- Desktop sidebar (always visible on md+) -->
<button onclick="toggleSection('site')">
  🌐 Site
  <span class="site-toggle">▼</span>  <!-- ▼ = open, ▶ = closed -->
</button>
<div id="site-items">
  <!-- Menu items here -->
</div>

<!-- Mobile sidebar (overlay on mobile) -->
<button onclick="toggleMobileAdminMenu()">
  <!-- FAB menu button -->
</button>
```

#### localStorage Keys
```javascript
// Persist section state
localStorage.setItem('sidebar-site', 'open');    // or 'closed'
localStorage.setItem('sidebar-admin', 'closed');
localStorage.setItem('sidebar-exports', 'open');

// Mobile versions
localStorage.setItem('sidebar-mobile-site', 'open');
localStorage.setItem('sidebar-mobile-admin', 'closed');
localStorage.setItem('sidebar-mobile-exports', 'open');

// Default: all open on first visit
const isOpen = localStorage.getItem(`sidebar-${section}`) === null
  ? true
  : localStorage.getItem(`sidebar-${section}`) === 'open';
```

#### JavaScript Functions
```javascript
toggleSection(section)
├─ Toggle d'une section
├─ Update DOM (hidden class, opacity, icon)
└─ Save state to localStorage

collapseSection(section)
├─ Set maxHeight = 0
├─ Add 'hidden' class
├─ Set opacity = 0
└─ Change icon to ▶

expandSection(section)
├─ Remove 'hidden' class
├─ Set maxHeight = 'none'
├─ Set opacity = 1
└─ Change icon to ▼

toggleMobileAdminMenu()
├─ Toggle 'hidden' class sur #mobileAdminMenu
└─ Show/hide overlay menu
```

#### CSS Transitions
```css
.site-items, .admin-items, .exports-items {
  max-height: none;
  opacity: 1;
  overflow: hidden;
  transition: max-height 0.3s ease, opacity 0.3s ease;
}

.site-items.hidden {
  max-height: 0;
  opacity: 0;
}
```

---

### 5. **Rating System** (Netflix-style - CONSOLIDATED)

#### Architecture
```
watchlist_items.rating (int|null)
├─ null = not rated
├─ 1 = 👎 Pas bien
├─ 2 = 👍 Bien
└─ 3 = 👍👍 Très bien
```

#### Controller: WatchlistController::rateItem()
```php
public function rateItem($tmdbId, Request $request)
{
    // 1. Validate rating is 1|2|3|null
    $rating = $request->input('rating');
    if ($rating !== null && !in_array($rating, [1, 2, 3])) {
        return response()->json(['error' => 'Invalid rating'], 400);
    }

    // 2. Check item exists AND is_watched=true (critical!)
    $item = WatchlistItem::where('user_id', auth()->id())
        ->where('tmdb_id', $tmdbId)
        ->first();

    if (!$item || !$item->is_watched) {
        return response()->json(['error' => 'Not watched'], 403);
    }

    // 3. Update rating column
    $item->rating = $rating;
    $item->save();

    // 4. Return JSON response
    return response()->json([
        'status' => 'success',
        'rating' => $rating,
        'message' => $rating ? "✅ Note enregistrée" : "Note supprimée"
    ]);
}
```

#### AJAX Endpoint
```javascript
POST /api/rating/{contentId}
Headers: {
  'X-CSRF-TOKEN': token,
  'X-Requested-With': 'XMLHttpRequest',
  'Content-Type': 'application/json'
}
Body: { rating: 1|2|3|null }

Response: {
  status: 'success',
  rating: 1|2|3|null,
  message: '✅ Note enregistrée'
}
```

#### UI Implementation Pattern
```blade
<!-- Show rating block only if watched -->
@if($item->is_watched)
  <div class="rating-buttons">
    @foreach([1 => '👎', 2 => '👍', 3 => '👍👍'] as $val => $emoji)
      <button data-rating="{{ $val }}"
              class="rating-btn {{ $item->rating === $val ? 'active' : '' }}"
              onclick="submitRating({{ $contentId }}, {{ $val }})">
        {{ $emoji }}
      </button>
    @endforeach
  </div>
@endif

<script>
function submitRating(contentId, ratingValue) {
  const newRating = currentRating === ratingValue ? null : ratingValue;

  fetch(`/api/rating/${contentId}`, {
    method: 'POST',
    headers: { /* ... */ },
    body: JSON.stringify({ rating: newRating })
  })
  .then(r => r.json())
  .then(data => {
    if (data.status === 'success') {
      currentRating = data.rating;
      updateRatingUI();
      showToast(data.message);
    }
  });
}
</script>
```

#### Logic Quand utilisateur marque comme "regardé"
```javascript
// Dans toggleWatched():
if (data.inWatched) {
  // Show rating block
  document.getElementById('ratingBlock').style.display = '';
} else {
  // Hide rating block ET clear rating
  currentRating = null;
  document.getElementById('ratingBlock').style.display = 'none';
}
```

#### Queries utiles
```php
// Get tous les items notés d'un user
WatchlistItem::where('user_id', $userId)
    ->whereNotNull('rating')
    ->with('kdrama')
    ->get();

// Stats: Nombre et moyenne
WatchlistItem::where('user_id', $userId)
    ->whereNotNull('rating')
    ->count();  // Nombre noté

WatchlistItem::where('user_id', $userId)
    ->whereNotNull('rating')
    ->avg('rating');  // Moyenne
```

---

### 6. **Integrated Icon System** (Tabler + Simple Icons with Fallback)

#### Overview
- **Tabler Icons** (5,021 total): General UI icons via `@tabler/icons` npm package
- **Simple Icons** (3,356 total): ALL brand logos via `codeat3/blade-simple-icons` package
- **Naming Convention**: Simple Icons use `si-` prefix (e.g., `si-youtube`, `si-instagram`)
- **Total Icons Available**: 8,377 icons (5,021 Tabler + 3,356 Simple)
- **Dynamic Count**: Recalculated from file system on each request (updates with composer updates)
- **Fallback System**: Missing Simple Icons automatically use Tabler fallback (pre-loaded all)

#### Admin Icon Browser (`/admin/icons`)
```
Features:
- Live search across 8,377 total icons (Tabler + Simple)
- Display 100 icons per page with "Load More" pagination
- Real-time counter: "Affichage 200 icons sur 8377" (dynamically calculated)
- **Icon labels in blue** for all icons (Tabler + Simple) - better information
- Click any icon to copy name to clipboard (with si- prefix for Simple Icons)
- Dual SVG sources: actual files for display
- Dynamic total: Updates if composer updates icon packages
```

#### Icon Picker Modal (in admin/author)
```html
<!-- Used in /admin/author for social links icon selection -->
<x-icon-picker-modal />

Features:
- Searchable icon library (all icons + fallbacks)
- Displays icon with name on hover
- Automatically adds "si-" prefix for Simple Icons
- Toast notification on selection
```

#### Backend System (AdminIconsController)
```php
// Returns combined icon list with metadata:
$icon = [
    'name' => 'youtube',        // Base name without prefix
    'type' => 'simple',         // 'tabler' or 'simple'
    'label' => 'YouTube',       // Human-readable label
    'svg' => '<svg>...',        // Actual SVG content
];

// Automatic fallback when Simple Icon missing:
if (!simpleIconExists('disneyplus')) {
    // Uses 'disneyplus.svg' from Tabler instead
    $type = 'tabler';
    $svg = getTablerSvg('disneyplus');
}
```

#### Frontend Display
```blade
<!-- In admin/icons page -->
@if($icon['type'] === 'simple')
    si-{{ $icon['name'] }}    <!-- Display with prefix -->
@else
    {{ $icon['name'] }}       <!-- Tabler (no prefix) -->
@endif

<!-- Data attribute for copying -->
data-icon-name="{{ $icon['type'] === 'simple' ? 'si-' . $icon['name'] : $icon['name'] }}"
```

#### Footer Usage
```blade
<!-- Automatically detects icon type by si- prefix -->
@if(str_starts_with($link->icon, 'si-'))
    <!-- Simple Icon: use fill-current -->
    {{ actualIconName = substr($link->icon, 3); }}
    {!! getSimpleIconSvg(actualIconName) !!}
@else
    <!-- Tabler Icon: use stroke-current -->
    {!! getTablerSvg($link->icon) !!}
@endif
```

#### JavaScript Pattern (Icon Picker Modal)
```javascript
function selectIcon(iconName, iconType = 'tabler') {
    // Automatically adds si- prefix for Simple Icons
    const displayName = iconType === 'simple'
        ? 'si-' + iconName
        : iconName;

    inputElement.value = displayName;  // Set input value
    showToast(`✅ Selected: ${displayName}`);
}

// JSON response includes type for correct handling:
{
    icons: [
        { name: 'instagram', type: 'simple', svg: '...' },
        { name: 'brand-instagram', type: 'tabler', svg: '...' },
    ]
}
```

#### Helper Methods
```php
// IconHelper class
IconHelper::getSimpleIcons()           // Get all available Simple Icons
IconHelper::getSimpleIconLabel($name)  // Get label for icon
IconHelper::hasSimpleIcon($name)       // Check if exists

// Example usage
$youtube = IconHelper::getSimpleIconLabel('youtube');  // "YouTube"
```

---

### 7. **Toast Notifications System**

#### Pattern Réutilisable
```javascript
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';

  toast.innerHTML = `
    <div class="fixed top-4 right-4 ${bgColor} text-white px-4 py-3 rounded shadow-lg z-50">
      ${message}
    </div>
  `;

  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

// Usage partout:
showToast('✅ Copié: face-id-error', 'success');
showToast('❌ Erreur', 'error');
```

---

### 8. **User Status Tracking** (Watchlist + Ratings)

#### Pattern dans ContentController
```php
// Récupérer status complet pour un user
$userStatus = [];
if (auth()->check()) {
  $items = WatchlistItem::where('user_id', auth()->id())->get();

  $userStatus = $items->mapWithKeys(function ($item) {
    return [$item->tmdb_id => [
      'is_in_watchlist' => $item->is_in_watchlist,
      'is_watched' => $item->is_watched,
      'rating' => $item->rating  // Nouveau!
    ]];
  })->toArray();
}

// Dans la vue:
@if($userStatus[$kdramaId]['is_watched'])
  Note: {{ $userStatus[$kdramaId]['rating'] }}
@endif
```

#### Pattern flexible (array ou object)
```php
// Peut être array (catalog) ou object (detail)
$userStatus = is_array($userStatus)
  ? $userStatus[$id] ?? null
  : $userStatus;

if ($userStatus) {
  $isWatched = is_array($userStatus)
    ? $userStatus['is_watched']
    : $userStatus->is_watched;
}
```

---

## 🎯 Patterns Réutilisables pour Nouvelles Features

### Pattern 1: Add New User Preference/Statut
```php
// 1. Add column to watchlist_items
Schema::table('watchlist_items', function (Blueprint $table) {
    $table->string('new_field')->nullable()->after('rating');
});

// 2. Add to Model
protected $fillable = [..., 'new_field'];
protected $casts = ['new_field' => 'string'];

// 3. Update in Controller via AJAX
POST /api/watchlist/{id}/update-field
Body: { field: 'new_field', value: 'something' }

// 4. Display in Views
@if($item->new_field)
  {{ $item->new_field }}
@endif
```

### Pattern 2: Add New Admin Setting
```php
// 1. Add to settings in seeder
Setting::set('new_setting_key', 'default_value', 'group_name');

// 2. In controller
$value = Setting::get('new_setting_key');

// 3. In admin/settings view
<input name="settings[new_setting_key]" value="{{ Setting::get('new_setting_key') }}">

// 4. In settings controller update
foreach ($request->input('settings', []) as $key => $value) {
    Setting::set($key, $value);
}
```

### Pattern 3: Add New Collapsible Section in Sidebar
```html
<!-- 1. Add button -->
<button onclick="toggleSection('newsection')">
  📌 New Section
  <span class="newsection-toggle">▼</span>
</button>

<!-- 2. Add items div -->
<div id="newsection-items" class="newsection-items space-y-1">
  <a href="...">Item 1</a>
  <a href="...">Item 2</a>
</div>

<!-- 3. CSS (déjà défini pour tous) -->
.newsection-items {
  max-height: none;
  opacity: 1;
  transition: max-height 0.3s ease, opacity 0.3s ease;
}

.newsection-items.hidden {
  max-height: 0;
  opacity: 0;
}

<!-- 4. localStorage automatiquement par toggleSection() -->
```

### Pattern 4: Add AJAX Endpoint
```php
// 1. Route
Route::post('/api/something/{id}', [SomeController::class, 'doSomething']);

// 2. Controller method
public function doSomething($id, Request $request) {
    $data = $request->validate([...]);
    // Do work
    return response()->json(['status' => 'success', ...]);
}

// 3. JavaScript
fetch('/api/something/123', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
.then(r => r.json())
.then(data => {
    if (data.status === 'success') {
        showToast(data.message);
    }
});
```

### Pattern 5: Add Export Column/Filter Option
```php
// 1. Update WatchlistExportService
// In getFilteredWatchlist()
'new_column' => true/false in options

// 2. In CSV export
if ($selectedColumns['new_column'] ?? true) {
    $headers[] = 'New Column';
    // Add column data in loop
}

// 3. In export modal
<label>
  <input type="checkbox" name="columns[new_column]">
  New Column
</label>
```

### Pattern 6: Add localStorage State
```javascript
// Save
localStorage.setItem('key', JSON.stringify(value));

// Load
const value = JSON.parse(localStorage.getItem('key') || '[]');

// Clear
localStorage.removeItem('key');

// Common keys in project:
// - sidebar-{section}
// - sidebar-mobile-{section}
// (future) - recent-icons
// (future) - export-preferences
```

### Pattern 7: Add Event Delegation (for dynamic content)
```javascript
// Instead of .addEventListener on each element:
// ❌ BAD (breaks on new elements)
document.querySelectorAll('.item').forEach(el => {
  el.addEventListener('click', handler);
});

// ✅ GOOD (works on dynamic elements)
document.addEventListener('click', (e) => {
  const item = e.target.closest('.item');
  if (item) {
    // Handle click
  }
});
```

### Pattern 8: Add Status Badge/Icon
```blade
<!-- Pattern used for is_watched, is_in_watchlist, rating -->
@if($item->is_watched)
  <span class="badge badge-success">✅ Watched</span>
@elseif($item->is_in_watchlist)
  <span class="badge badge-info">📺 To Watch</span>
@endif

@if($item->rating)
  <span class="badge badge-rating">
    @switch($item->rating)
      @case(1) 👎 @break
      @case(2) 👍 @break
      @case(3) 👍👍 @break
    @endswitch
  </span>
@endif
```

### Pattern 9: Add Modal/Form Component
```html
<!-- Create reusable component in resources/views/components/ -->
<x-modal id="myModal" title="Title">
  <form>
    <!-- Form content -->
    <button type="submit">Save</button>
  </form>
</x-modal>

<script>
function openModal() {
  document.getElementById('myModal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('myModal').classList.add('hidden');
}
</script>
```

### Pattern 10: Add Toast Helper
```javascript
// Already implemented globally
showToast(message, type = 'success');

// Examples:
showToast('✅ Copié: face-id-error', 'success');
showToast('❌ Erreur lors de la sauvegarde', 'error');
showToast('⏳ Chargement...', 'info');
```

---

## 💭 Idées & Concepts Implémentés

### Idea 1: Flexible Rating System
**Pourquoi:** Netflix-style feedback, simple et intuitif
**Implementation:** 3 levels (1=bad, 2=good, 3=excellent)
**Storage:** Dans watchlist_items.rating (consolidé)
**Extension possible:** Plus de niveaux, reviews texte, timestamps

### Idea 2: Collapsible Sidebar Sections
**Pourquoi:** Admin pages trop longues, besoin de réduire
**Implementation:** localStorage persistence + smooth CSS transitions
**Extension possible:** Remember last active section per user

### Idea 3: Cache PDF with TTL
**Pourquoi:** PDF generation est slow (~2-5s), réutiliser si filters identiques
**Implementation:** MD5 hash des options, check file creation date
**Benefit:** 50-100x faster for cached files
**Extension possible:** Manual cache purge per user, cache stats per user

### Idea 4: Settings in Database
**Pourquoi:** Config changeable sans redeploy, admin control
**Implementation:** Key-value table avec groupes
**Extension possible:** Type casting (int/bool/json), validation per setting

### Idea 5: Event Delegation for Dynamic Content
**Pourquoi:** Icon search results replace entire grid dynamically
**Implementation:** Click listeners sur parent document, check .closest()
**Benefit:** Works on old AND new elements without re-binding

### Idea 6: localStorage State Persistence
**Pourquoi:** User preferences (sidebar state) survive page reload
**Implementation:** Save on toggle, load on page init
**Extension possible:** Sync across tabs, server-side persistence

### Idea 7: AJAX Queue Job Pattern
**Pourquoi:** Email/PDF generation shouldn't block response
**Implementation:** Dispatch job → queue → async process
**Fallback:** QUEUE_CONNECTION=sync for instant in dev

### Idea 8: User Status as Denormalized Object
**Pourquoi:** Catalog page shows user's watchlist/rating status
**Implementation:** mapWithKeys to create flat object per user
**Benefit:** Single query instead of joining multiple tables

### Idea 9: Admin Sidebar with Active Route Highlighting
**Pourquoi:** Users know which page they're on
**Implementation:** request()->routeIs() in blade
**Example:** `{{ request()->routeIs('admin.users.*') ? 'bg-red-600' : '...' }}`

### Idea 10: Toast Notifications Global Pattern
**Pourquoi:** Consistent feedback across whole app
**Implementation:** showToast() function, removes auto after 3s
**Usage:** All AJAX responses, copies, form submissions

---

## 🔐 Authentication & Authorization

### Middleware
- `auth` - Utilisateur connecté
- `admin` - `is_admin=true` required
- `Illuminate\Auth\Middleware\Authenticate`

### Routes Publiques
- GET `/` - Home (catalog grid)
- GET `/kdrams` - Catalog (paginated)
- GET `/kdrams/{id}` - Detail (avec streaming links)
- GET `/contact` - Contact form
- POST `/contact` - Submit contact form
- GET `/api/actor/{id}` - Actor details (AJAX)

### Routes Auth (connecté)
- GET `/dashboard` - User dashboard + stats + ratings
- GET `/watchlist` - Watchlist avec ratings + export
- GET `/profile` - User profile edit
- PATCH `/profile` - Update profile
- DELETE `/profile` - Delete account
- POST `/api/watchlist/toggle/{contentId}` - Add/remove watchlist
- POST `/api/watched/toggle/{contentId}` - Mark watched
- POST `/api/rating/{contentId}` - Rate drama (👎 👍 👍👍)
- GET `/api/watchlist/status/{contentId}` - Check status
- DELETE `/api/watchlist/{contentId}` - Delete item
- GET `/watchlist/export-modal` - Export modal
- POST `/watchlist/export` - Export PDF/CSV

### Routes Admin (préfixe `/admin`)
- GET `/admin` - Admin dashboard
- GET `/admin/users` - User management (CRUD)
- GET `/admin/users/{id}` - User detail
- GET `/admin/settings` - Settings CRUD
- POST `/admin/settings` - Update settings
- GET `/admin/author` - Author/SEO/Social links edit
- POST `/admin/author` - Update author info
- POST `/admin/author/social-links/reorder` - Reorder social links (AJAX)
- GET `/admin/icons` - Icon picker with search
- GET `/admin/contact` - Contact messages list
- GET `/admin/contact/{id}` - Message detail + workflow
- POST `/admin/contact/{id}/status` - Update message status
- GET `/admin/contact/{id}/attachment` - Download attachment
- DELETE `/admin/contact/{id}` - Delete message
- GET `/admin/exports/cache` - Cache management
- POST `/admin/exports/cache/{filename}` - Delete single cache
- POST `/admin/exports/cache-purge-all` - Purge all cache
- POST `/admin/exports/cache-purge-expired` - Purge expired only
- GET `/admin/exports/stats` - Export statistics dashboard
- POST `/admin/exports/user/{userId}` - Export user watchlist

---

## 📦 Database Schema (Key Tables)

### users
```
id, name, email, password,
is_admin (bool), is_public (bool),
preferred_language, preferred_region,
timestamps
```

### watchlist_items ⭐ (IMPORTANT)
```
id, user_id, tmdb_id,
is_in_watchlist (bool),
is_watched (bool),
rating (int|null) ← NEW CONSOLIDATED
notes (text),
added_at, updated_at
```
**Important:** Pas de table `ratings` séparée! Tout est dans `rating` column.

### kdramas
```
id, tmdb_id (unique),
name, en_name, original_name,
overview, poster_path, backdrop_path,
vote_average, vote_count,
first_air_date, last_air_date,
status, original_language,
number_of_episodes, number_of_seasons,
genres (JSON), production_companies (JSON),
networks (JSON), credits (JSON),
similar (JSON), translations (JSON),
last_updated_at, timestamps
```

### settings
```
id, key (unique), value (longtext),
group (nullable), timestamps
```
Ex: `key='rapidapi_cache_hours'`, `value='48'`

### export_logs
```
id, user_id, format (pdf|csv),
item_count, file_size, cache_hash,
was_cached (bool), generation_time (ms),
filters (JSON), timestamps
```

### social_links
```
id, platform, url, icon, order,
is_visible (bool), timestamps
```

### site_metadata
```
id, meta_description, meta_keywords,
og_type, og_title, og_description, og_image,
timestamps
```

---

## 🎨 UI/UX Patterns

### Design System
- **Colors:** Red (#ef4444) primary, Purple secondary, Amber accent
- **Dark Theme:** slate-800/900 backgrounds
- **Components:** `.btn-primary`, `.card`, `.content-card`, `.badge`
- **Responsive:** Mobile-first, md: tablet, lg: desktop

### Sidebar (NEW)
```html
<!-- Collapsible sections with localStorage persistence -->
<button onclick="toggleSection('site')">
  🌐 Site
  <span class="site-toggle">▼</span> <!-- toggles to ▶ -->
</button>
<div id="site-items"><!-- items --></div>
```

### Rating Display
```html
<!-- Show rating emoji only if watched and rated -->
@if($item->is_watched && $item->rating)
  {{ $item->rating === 1 ? '👎' : ($item->rating === 2 ? '👍' : '👍👍') }}
@endif
```

---

## 🔧 Common Tasks

### Add a New Setting
```php
// In migration or seeder
Setting::set('my_key', 'my_value', 'group_name');

// In code
$value = Setting::get('my_key', 'default');
```

### Create New Admin Page
1. Create controller: `app/Http/Controllers/Admin/MyController.php`
2. Create view: `resources/views/admin/mypage.blade.php`
3. Include `<x-admin-sidebar />` at top
4. Add route in `routes/web.php` under admin middleware
5. Add link in sidebar menu

### ⚠️ **IMPORTANT: Force Password Change Middleware Pattern**
**EVERY NEW ROUTE MUST INCLUDE `check.password` MIDDLEWARE!**

Routes with `check.password`:
- ✅ Public routes: `/`, `/kdrams`, `/kdrams/{id}`, `/contact` (protects authenticated users)
- ✅ Protected routes (auth): `/dashboard`, `/watchlist`, `/profile`, all AJAX API routes
- ✅ Admin routes: `/admin/*` (all admin pages)
- ✅ Special admin-only routes: `/kdrams/{id}/refresh-streaming`

**When creating a NEW page/route, ALWAYS add `check.password` middleware:**

```php
// For public pages (users might be logged in):
Route::get('/my-new-page', [MyController::class, 'show'])
    ->middleware('check.password')
    ->name('my.page');

// For protected pages (auth + check.password):
Route::middleware(['auth', 'check.password'])->group(function () {
    Route::get('/my-protected-page', [MyController::class, 'show'])->name('my.page');
});

// For admin pages (admin + check.password):
Route::middleware(['auth', 'admin', 'check.password'])->prefix('admin')->group(function () {
    Route::get('/my-admin-page', [AdminController::class, 'show'])->name('my.admin.page');
});
```

**Why?** If a user has `password_must_change=true`, they are redirected to `/change-password` on ANY route they try to access. This ensures they CANNOT bypass the password change requirement.

### Add Export Option
1. Modify `resources/views/watchlist/_export-modal.blade.php`
2. Update `app/Services/WatchlistExportService.php` methods
3. Add validation in `WatchlistController::export()`

### Fix Rating Issue
- Ratings are in `watchlist_items.rating` column
- **NOT** in separate `ratings` table (deleted)
- Always use `$item->rating` directly
- Query: `WatchlistItem::whereNotNull('rating')`

---

## ⚠️ Known Issues & Solutions

### Issue: Rating not showing
- ✅ **Solution:** Check `watchlist_items.rating` column exists
- ✅ Run migration: `php artisan migrate`
- Data was migrated from old `ratings` table

### Issue: Icon search not working
- ✅ **Solution:** Uses event delegation + data-icon-name attribute
- ✅ Check browser console for errors
- ✅ Copy uses execCommand + clipboard API fallback

### Issue: Admin sidebar collapsed on refresh
- ✅ **Solution:** localStorage persists state (`sidebar-{section}`)
- Default: all sections open on first visit
- Clear localStorage to reset

### Issue: PDF export slow
- ✅ **Solution:** Check cache first with `getCachedPDF()`
- Cache hash: MD5(user_id + filters + sort + columns)
- 7-day TTL in `storage/app/exports/`

### Issue: Email not sending
- ✅ Queue driver: `QUEUE_CONNECTION=sync` (immediate in dev)
- ✅ Check `config/mail.php` for credentials
- ✅ Base64 encoding used for binary content
- ✅ Herd support: detects `localhost` → uses `kdrama-laravel.test`

---

## 🚀 Setup & Run

```bash
# Setup
cp .env.example .env
php artisan key:generate
composer install
npm install
npm run build

# Database
php artisan migrate
php artisan db:seed

# Run (dev)
php artisan serve
# Visit: http://localhost:8000

# Queue (if using async)
php artisan queue:work

# Cleanup old PDFs (daily)
php artisan exports:cleanup

# Default admin credentials
# Email: admin@kdrama.local
# Password: password
```

---

## 📝 Important Files to Know

| Fichier | Description |
|---------|-------------|
| `app/Models/WatchlistItem.php` | Ratings consolidated in `rating` column (no separate table) |
| `app/Models/ContactMessage.php` | Contact form submissions with attachments |
| `app/Models/ExportLog.php` | Export tracking and statistics |
| `app/Http/Controllers/WatchlistController.php` | Export, ratings, watchlist AJAX |
| `app/Http/Controllers/ContentController.php` | Catalog, details, actor info |
| `app/Http/Controllers/ContactController.php` | Contact form submission |
| `app/Http/Controllers/Admin/AdminExportController.php` | Export management, cache, stats |
| `app/Http/Controllers/Admin/AdminContactController.php` | Contact message CRUD + workflow |
| `app/Services/WatchlistExportService.php` | PDF/CSV generation + cache |
| `app/Services/TmdbService.php` | TMDB API integration |
| `app/Services/StreamingAvailabilityService.php` | RapidAPI streaming availability |
| `app/Mail/ExportNotification.php` | Email template for exports |
| `routes/web.php` | Toutes les routes (public, auth, admin) |
| `resources/views/components/admin-sidebar.blade.php` | Admin sidebar (collapsible + localStorage) |
| `resources/views/admin/exports/_admin-export-modal.blade.php` | Reusable export modal |
| `resources/views/kdrams/show.blade.php` | Detail page + rating UI + streaming links |
| `resources/views/watchlist.blade.php` | Watchlist + ratings display + export |
| `resources/views/contact.blade.php` | Contact form avec attachments |
| `resources/views/admin/contact/` | Contact management pages |
| `database/migrations/` | All schema including ratings consolidation |
| `.env` | Config API keys (TMDB_API_KEY, RAPIDAPI_KEY), DB, mail |

---

## 🔄 Recent Changes (2026-03-12)

### Fixed PDF Export "Watching" Filter Issue
- ✅ **Root Cause Found:** AdminExportController was missing `filters.watching` validation rule
- ✅ **Impact:** Admin export modal showed "EN COURS 0" instead of 12 items with is_watching=true
- ✅ **Fix Applied:**
  - Added `'filters.watching' => 'sometimes|boolean'` to validation rules
  - Added `'watching' => $request->boolean('filters.watching', true)` to filters array
  - Removed debug logging from WatchlistExportService
  - Now admin export aligns with user export endpoint which already had correct implementation

**What was happening:**
- Validation rules didn't include 'filters.watching'
- Laravel silently stripped out the watching filter
- Options array only contained watched + to_watch filters
- PDF retrieved 33 items instead of 45 (missing 12 "watching" items)
- Stats showed "TOTAL 33 REGARDÉS 19 EN COURS 0 À VOIR 14"

**Commits:**
- `2ada0c3` - Fix: Add missing 'watching' filter to admin export endpoint

### Fixed Home Page Drama Links
- ✅ **Issue:** Home page links were using local database `id` instead of TMDB ID
- ✅ **Solution:** Use fallback operator to handle both data sources:
  - Database data (featured from admin): uses `tmdb_id`
  - API data (newest/upcoming): uses `id`
  - Fixed all 3 sections: Featured, Newest Releases, Upcoming Releases
  - Using `$item['tmdb_id'] ?? $item['id']` in route parameters

**Files Modified:**
- `resources/views/index.blade.php` - Updated 3 kdrams.show route links

**Commits:**
- `334cfe8` - Fix: Use correct ID field in home page drama links

---

## 🔄 Recent Changes (2026-03-10)

### Complete Icon System Upgrade
- ✅ **All 3,356 Simple Icons** now available in admin icon picker (`/admin/icons`)
- ✅ **Total 8,377 icons**: 5,021 Tabler + 3,356 Simple Icons
- ✅ **Dynamic icon count**: Recalculated from file system (updates with composer updates)
- ✅ **Icon labels in blue**: All icons display labels for better information
- ✅ **Live search & pagination**: Search all icons, load 100 at a time
- ✅ **Social media logos** in actor modal with Simple Icons branding
- ✅ **Fallback system**: All icons work with automatic Tabler fallback

**Admin Icon Browser Features:**
- Search across all 8,377 icons with live 300ms debounce
- Pagination: Display 100 + "Load More" for rest
- Copy to clipboard with `si-` prefix for Simple Icons
- Type badges (Tabler vs Simple Icons)
- Real-time counter updates
- Professional SVG rendering with dual sources

**Icon Helper (IconHelper.php):**
```php
// Get all available Simple Icons (now 3,356 from package)
IconHelper::getSimpleIcons()  // Returns curated list (48 popular ones)

// Get label for specific icon
IconHelper::getSimpleIconLabel('instagram')  // Returns "Instagram"

// Check if icon exists
IconHelper::hasSimpleIcon('tiktok')  // Returns true/false
```

**Available Simple Icons:**
- 1000+ brand & social logos from [Simple Icons](https://simpleicons.org/)
- All accessible via `si-` prefix in admin icon picker
- Professional quality, optimized for web use

### Actor Modal - Complete Social Media Integration + Full Translations
- ✅ **7 Social Media Networks** displayed in actor modal:
  - 📸 Instagram (gradient pink/purple button)
  - 📘 Facebook (blue button)
  - 𝕏 Twitter (dark gray button)
  - 🎵 TikTok (black button) - **Fixed URL generation**
  - 📺 YouTube (red button)
  - 🎬 IMDb (yellow button)
  - 📚 Wikidata (cyan button)
- ✅ **Complete translations for actor modal** in 3 languages (FR/EN/DE):
  - Actor real name label
  - Birth date and place labels
  - Biography section header
  - Recent projects section header
  - "View all K-Dramas" button
  - All 7 social media button labels
- ✅ **URL generation fixed** using Blade string concatenation:
  - `{{ 'https://tiktok.com/@' . $actor['external_ids']['tiktok_id'] }}`
  - Ensures correct URL format for all platforms
- ✅ **Files updated:**
  - `lang/fr/show.php` - 14 new translation keys
  - `lang/en/show.php` - 14 new translation keys
  - `lang/de/show.php` - 14 new translation keys
  - `resources/views/kdrams/_actor_modal.blade.php` - added all social links + translations

### Hero Title Placeholder Implementation
- ✅ **Fixed Hero Title Duplication Issue**
  - Changed `hero_title` from hardcoded "K-Dramas" to placeholder `{dramas}`
  - Updated translation files: `lang/fr/home.php`, `lang/en/home.php`, `lang/de/home.php`
  - Example: FR `'hero_title' => 'Découvrez les meilleurs {dramas}'`
  - Example: EN `'hero_title' => 'Discover the best {dramas}'`
  - Example: DE `'hero_title' => 'Entdecken Sie die besten {k-dramas}'`
- ✅ **Dynamic K-Dramas Injection**
  - HTML in `resources/views/index.blade.php` uses `str_replace()`:
  - `{!! str_replace('{dramas}', '<span class="gradient-text">K-Dramas</span>', __('home.hero_title')) !!}`
  - Result: "K-Dramas" appears only once with gradient styling applied
  - Fully translatable while maintaining CSS styling

### Complete Laravel Localization (FR/EN/DE)
- ✅ **Comprehensive Translation System**
  - 3 languages fully supported: Français (default) | English | Deutsch
  - 9 translation files per language (common, admin, catalog, contact, show, auth, dashboard, watchlist, emails)
  - 48+ admin-specific keys + all other page translations
- ✅ **Language Switcher Footer**
  - 3 buttons in footer: 🇫🇷 FR | 🇬🇧 EN | 🇩🇪 DE
  - Uses POST route `language.switch`
  - Saves locale to session (for guests) or user preference (for logged-in)
  - Placed at bottom right of footer (away from navbar dropdown)
- ✅ **Admin Sidebar Language Selector**
  - 3 buttons in footer of admin sidebar (both desktop + mobile versions)
  - Same functionality as footer switcher
  - Always visible for quick language changes
- ✅ **User Profile Language Preference**
  - New field in profile: "Langue préférée" / "Preferred Language" / "Bevorzugte Sprache"
  - Select dropdown with 3 options (FR/EN/DE)
  - Saved to `user.preferred_language` column
  - Applied automatically on user login via `SetLocale` middleware
- ✅ **Admin Pages Translation**
  - All 5 admin pages fully translated:
    - `/admin/icons` - Icon picker page
    - `/admin/users` - User management
    - `/admin/settings` - Site settings
    - `/admin/author` - Author & SEO
    - `/admin/contact` - Contact management (index + detail pages)
  - All sidebar labels, buttons, confirmations, and error messages translated
- ✅ **Primary Button Styling**
  - Changed color from `bg-slate-800` (gray) to `bg-red-600` (red)
  - Better visibility and consistency with brand colors
  - Applied to all form submit buttons across the application
- ✅ **Dropdown & Z-index Fixes**
  - Fixed user dropdown visibility issue on home page
  - Navigation now has `z-40` for proper layering
  - Dropdown component uses `z-[999]` with Alpine.js style binding
  - Fixed `x-show` toggle with explicit `x-bind:style="open && 'display: block;'"`

### Force Password Change System
- ✅ **Admin Panel:** Reset user password from `/admin/users/{id}/edit`
  - Click "🔑 Generate & Send New Password" button
  - Custom modal for confirmation (no browser alert)
  - Generates secure temporary password (12 chars: uppercase, lowercase, numbers, symbols)
  - Sends password via email (PasswordResetMail mailable)
  - Sets `password_must_change=true` flag
- ✅ **User Enforcement:** Middleware `CheckPasswordMustChange`
  - Applies to all routes (public, protected, admin)
  - Redirects to `/change-password` if flag is true
  - User must change password before accessing anything
  - Password change page shows warning message
- ✅ **Password Change Page:** `/change-password`
  - Requires current password verification
  - New password with strength requirements display (8+ chars, mixed case, number/symbol)
  - Password confirmation field
  - Clears `password_must_change=false` on success
  - Redirects to dashboard with success message
- ✅ **Database:** Added `password_must_change` column to users table
  - Boolean field (default false)
  - Tracked in User model with proper casting
- ✅ **Sidebar Integration:** Telescope debug link added
  - 🔍 Link to `/telescope` in Admin menu (desktop + mobile)
  - Gate `viewTelescope` ensures only admins can access
  - Fully secured - no exposed endpoints

### Documentation Update
- ✅ Updated Laravel version from 11 to 12 (confirmed in composer.json)
- ✅ Corrected model names (ContactMessage instead of Contact)
- ✅ Updated controllers list (all 6 admin controllers, 7 main controllers)
- ✅ Removed non-existent helpers and console commands
- ✅ Updated routes documentation (added new routes, organized by type)
- ✅ Updated important files section with actual project structure

## 🔄 Recent Changes (2026-03-09)

1. ✅ **Netflix-style Rating System** - Consolidated into watchlist_items.rating
   - Removed separate `ratings` table
   - Migration script copied old data
   - Updated all controllers/services

2. ✅ **Admin Sidebar Dropdowns** - Collapsible sections
   - Site, Admin, Exports sections
   - localStorage persistence
   - Mobile + desktop versions

3. ✅ **Icon Picker Fixes** - Live search + copy
   - Event delegation for click handling
   - execCommand + clipboard API
   - Better error messages

---

## 💡 Best Practices

1. **Always check `is_watched`** before using rating
2. **Use Setting::get()** for all configuration
3. **Add sidebar component** to new admin pages
4. **Use event delegation** for dynamic content
5. **Cache PDF hashes** are deterministic (same filters = same file)
6. **localStorage keys** start with `sidebar-`
7. **Admin routes** must have `middleware: 'admin'`
8. **Export columns** are configurable in modal

---

## 📞 Debug Tips

```bash
# Check database
php artisan tinker
>>> WatchlistItem::with('kdrama')->where('rating', '!=', null)->get()

# Check settings
>>> Setting::get('rapidapi_cache_hours')

# Clear all cache
>>> cache()->flush()

# View queue jobs
>>> DB::table('jobs')->get()

# Check exports
>>> ls -lah storage/app/exports/

# View logs
>>> tail -f storage/logs/laravel.log
```

---

**Auteur:** Claude Code
**Dernière mise à jour:** 2026-03-10
**Status:** Production Ready ✅

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/telescope (TELESCOPE) - v5
- laravel/boost (BOOST) - v2
- laravel/breeze (BREEZE) - v2
- laravel/dusk (DUSK) - v8
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
