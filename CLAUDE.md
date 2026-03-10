# 🎬 KDrama Laravel - Documentation Complète

**Date de mise à jour:** 2026-03-10
**Dernier développement:** Force Password Change System + Netflix-style rating system + Admin sidebar + Export management

---

## 📋 Vue d'ensemble du projet

**KDrama Hub** est une plateforme Laravel moderne pour découvrir, suivre et exporter des K-Dramas avec système de notation, cache PDF, et panel admin complet.

### Stack Technique
- **Framework:** Laravel 12
- **Frontend:** Tailwind CSS + Alpine.js
- **Database:** MySQL
- **Auth:** Laravel Breeze
- **Icons:** Tabler Icons (@tabler/icons)
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
```

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

### 6. **Icon Picker** (Live Search + Copy)

#### HTML Structure
```html
<input id="iconSearch" type="text" placeholder="...">
<div id="iconsGrid">
  <!-- Icons grid here -->
</div>
```

#### Data Attributes
```html
<div class="icon-card" data-icon-name="face-id-error">
  <!-- SVG here -->
</div>
```

#### JavaScript Pattern
```javascript
// Event delegation pour clicks
document.addEventListener('click', (e) => {
  const iconCard = e.target.closest('.icon-card');
  if (iconCard) {
    const iconName = iconCard.getAttribute('data-icon-name');
    copyToClipboard(iconName);
  }
});

// Live search avec debounce
const searchInput = document.getElementById('iconSearch');
let searchTimeout;

searchInput.addEventListener('input', (e) => {
  clearTimeout(searchTimeout);
  const query = e.target.value.trim();

  searchTimeout = setTimeout(() => {
    searchIcons(query);  // AJAX call
  }, 300);  // 300ms debounce
});

// Fetch avec JSON response
fetch(`/admin/icons/search?q=${query}`, {
  headers: { 'X-Requested-With': 'XMLHttpRequest' }
})
.then(r => r.json())
.then(data => displayIcons(data.icons));

// Copy avec fallback
function copyToClipboard(text) {
  // 1. Try execCommand (legacy but compatible)
  const textarea = document.createElement('textarea');
  textarea.value = text;
  document.body.appendChild(textarea);
  textarea.select();
  document.execCommand('copy');
  document.body.removeChild(textarea);

  // 2. Fallback: clipboard API
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text);
  }
}
```

#### localStorage Pattern (future)
```javascript
// Could store recent icons copied
localStorage.setItem('recent-icons', JSON.stringify(recentList));
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

## 🔄 Recent Changes (2026-03-10)

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
