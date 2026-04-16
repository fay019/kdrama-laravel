# 🍿 KDrama Laravel

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
<img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Version">
<img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
<img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
<img src="https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge" alt="License">
</p>

## 📝 Aperçu
KDrama Laravel est une application web moderne permettant de parcourir des dramas asiatiques (K-Dramas), de consulter leur disponibilité sur les plateformes de streaming et de gérer une liste de favoris (watchlist). Le projet est bâti avec le framework **Laravel 12** et utilise les APIs de TMDB et RapidAPI pour les données.

---

## ✨ Fonctionnalités Principales

### 🔐 Authentification & Autorisation
- ✅ Inscription et connexion sécurisée (Laravel Breeze)
- ✅ Authentification multi-rôles (utilisateurs réguliers + administrateurs)
- ✅ Système d'admin avec middleware `IsAdmin`
- ✅ Tableau de bord utilisateur personnalisé
- ✅ **Force Password Change System** : Admin peut réinitialiser mot de passe utilisateur
  - 🔑 Génération de mot de passe temporaire sécurisé (12 chars)
  - 📧 Envoi par email automatique
  - 🔒 Forçage du changement avant accès à l'app
  - ✏️ Page de changement de mot de passe dédiée avec validation

### 🌍 Localisation Multilingue
- ✅ **Support complet FR/EN/DE** (Français, English, Deutsch)
- ✅ Sélecteur de langue dans le **footer** (buttons 🇫🇷 🇬🇧 🇩🇪)
- ✅ Sélecteur dans l'**admin sidebar** (desktop + mobile)
- ✅ **Préférence utilisateur** : sauvegardée au profil
- ✅ Traductions intégrales de toutes les pages publiques et admin
- ✅ Changement de langue instantané sans rechargement
- ✅ **Hero title avec placeholder** : "K-Dramas" injecté dynamiquement avec style gradient préservé

### 📺 Gestion de Watchlist & Notation
- ✅ Ajout/suppression de dramas à sa watchlist
- ✅ Marquage de dramas comme "regardés"
- ✅ **Système de notation Netflix-style** : 👎 Pas bien (1), 👍 Bien (2), 👍👍 Très bien (3)
- ✅ Les ratings s'affichent sur les cards du catalogue, la page watchlist et la page détail
- ✅ Suppression de la note en re-cliquant sur le même rating

### 📥 Export de Watchlist
- ✅ **Export PDF** : Mise en page élégante avec Browsershot/Chrome, emojis parfaits, images 135x203px, pagination automatique
- ✅ **Export CSV** : Colonnes sélectionnables (titre, statut, rating, année, vote TMDB, genres, networks, synopsis)
- ✅ **Filtres complets** : regardés, à regarder, ou mélange
- ✅ **Tri flexible** : date d'ajout, titre A-Z, rating personnel, vote TMDB
- ✅ **UX optimisée** : modale interactive, spinner de chargement, noms de fichier automatiques

### 🎨 Admin Panel

#### 📊 Dashboard & Infrastructure
- ✅ **Dashboard admin** avec statistiques (utilisateurs, contenus, synchronisations)
- ✅ **Sidebar collapsible** avec sections (Site, Admin, Exports, Jobs & Tasks)
- ✅ **Sélecteur de langue intégré** (changement instantané)
- ✅ **Telescope** (outil de débogage) : Accès admin uniquement
  - Visualisation des requêtes SQL, HTTP, erreurs
  - Logs en temps réel
  - Performance monitoring

#### 👥 Gestion des Utilisateurs
- ✅ **Gestion des utilisateurs** (CRUD complet)
- ✅ **Force Password Change System** :
  - 🔑 Bouton "Generate & Send New Password" pour réinitialiser le mot de passe
  - 📧 Envoi de mot de passe temporaire sécurisé (12 chars) par email
  - 🔒 Utilisateur forcé de changer le mot de passe avant accès à l'app
  - ✏️ Page `/change-password` avec validation des critères de force

#### ⚙️ Configuration & Paramètres
- ✅ **Gestion des paramètres** (settings key-value avec grouping)
- ✅ **Gestion auteur/site/SEO** :
  - Profil auteur (nom, bio, email, avatar)
  - Informations du site (nom, tagline, footer, copyright)
  - **Social links avec drag-and-drop** (réordonner via AJAX)
  - **Icônes intégrées**: Tabler (5021) + Simple Icons (3356) = 8377 icons total
  - Icon picker modal avec recherche en direct sur 8377+ icons
  - SEO : meta description, keywords, Open Graph tags (OG title, description, image, type)

#### 🎨 Gestion des Icônes
- ✅ **Admin Icon Browser** (`/admin/icons`) : Recherche tous les 8377 icons (Tabler + Simple)
- ✅ Pagination "Load More" avec compteur dynamique (100 icons par page)
- ✅ **Labels en bleu** pour tous les icons (meilleure information)
- ✅ Type badges pour distinguer Tabler et Simple Icons
- ✅ Copy to clipboard avec préfixe `si-` pour Simple Icons
- ✅ **Compteur dynamique**: Mis à jour automatiquement si les packages changent

#### 📧 Gestion des Messages de Contact
- ✅ **Liste complète** avec stats (total, en attente, lus, en cours, réglés, spam, erreurs email)
- ✅ **Recherche et filtrage** par statut
- ✅ **Affichage détaillé** (expéditeur, email, pièces jointes)
- ✅ **Workflow d'état** : Pending → Read → In Progress → Resolved (transitions logiques)
- ✅ **Téléchargement** des pièces jointes
- ✅ **Suppression** définitive des messages

#### 🔄 Gestion des Jobs & Tâches Background (`/admin/jobs`)
- ✅ **Professional Async Queue** : Database-backed persistent job processing (MySQL)
- ✅ **Live Logs Viewer** (`/admin/jobs`):
  - ✨ **Syntax Colorization** : Codes couleur automatiques (timestamps, log levels, keywords, numbers)
  - 📊 **Color Legend** : Explication visuelle des couleurs utilisées
  - 🔄 **Auto-refresh 2s** : Mise à jour automatique des logs en temps réel
  - 🎛️ **Contrôles** : Toggle auto-refresh, refresh manuel, bouton "Clear Logs"
  - 📜 **Affichage** : Dernières 100 lignes du `storage/logs/jobs.log`
- ✅ **Quick Actions** - 3 boutons pour dispatcher jobs rapidement:
  - 🎭 **Sync Actors** : Synchronise les acteurs populaires depuis TMDB
  - 🗑️ **Cleanup PDFs** : Supprime les exports PDF de plus de 7 jours
  - 🎬 **Update Production** : Met à jour les sociétés de production et réseaux
- ✅ **Monitoring de la File d'Attente** :
  - 📋 Table des jobs en attente (queue, tentatives, date disponibilité)
  - 🗑️ **Bouton Delete** : Supprimer les jobs de la file d'attente
  - 📱 **Vue mobile** : Cards avec tous les détails (desktop + mobile)
- ✅ **Gestion des Jobs Échoués** :
  - 🔴 Table avec exception preview
  - 🔁 **Bouton Retry** : Relance un job échoué
  - 🗑️ **Bouton Delete** : Supprime un job échoué

#### 📜 Historique des Jobs (`/admin/jobs/history`)
- ✅ **Exécution complète** : Page dédiée pour l'historique des jobs exécutés
- ✅ **Métadonnées enrichies** :
  - Job class et statut (completed/failed)
  - Duration (temps d'exécution)
  - Output (résumé de l'exécution)
  - Completed at (timestamp)
  - Metadata (JSON avec détails: dramas_processed, actors_synced, etc.)
- ✅ **Pagination** : 20 résultats par page
- ✅ **Responsive design** : Desktop (tables) + Mobile (cards avec badges)

#### 📥 Gestion des Exports (`/admin/exports`)
- ✅ **Export Admin** - Export watchlist de n'importe quel utilisateur
  - Filtres identiques aux users (regardés, à regarder, mélange)
  - Sélection des colonnes à inclure
  - Choix du tri (date d'ajout, titre, rating, vote TMDB)
  - PDF avec mise en page élégante, CSV avec colonnes personnalisables
- ✅ **Cache Management** (`/admin/exports/cache`):
  - 📊 Liste complète des fichiers cachés (taille, date création)
  - 🗑️ Suppression individuelle des fichiers
  - 💥 **Purge All** : Supprime tous les PDFs cachés
  - ⏰ **Purge Expired** : Supprime seulement les fichiers > 7 jours
  - 📈 Affichage de l'espace disque utilisé
- ✅ **Export Statistics** (`/admin/exports/stats`):
  - 📊 Statistiques complètes (nombre d'exports, formats, utilisateurs)
  - 📈 Graphiques et tendances
  - ⏱️ Temps moyen de génération, hit rate du cache

#### 🚀 Système de Queue Professionnel
- ✅ **Driver Database** : Jobs stockés dans MySQL (table `jobs`)
- ✅ **Failed Jobs Tracking** : Logs des failures (table `failed_jobs`)
- ✅ **Job Timeout** : 300 secondes pour les jobs longue durée
- ✅ **Isolated Logging** : Logs séparés dans `storage/logs/jobs.log`
- ✅ **Memory Optimization** : Batch processing avec réduction des requêtes SQL
- ✅ **Supervisor Integration** : Prêt pour production avec daemon management

#### 🛡️ Signalement de Contenu et Modération
- ✅ **Report Modal** : Formulaire pour signaler le contenu
  - 📝 Raison du signalement (inapproprié, faux, spam, autre)
  - 💬 Description textuelle du problème
  - 📧 Email du signaleur (optionnel)
  - 🔗 Lien direct vers le contenu signalé
- ✅ **Admin Dashboard** (`/admin/reports`):
  - 📋 Liste complète des signalements
  - 🔍 Filtrage par statut (Pending, In Review, Resolved, Dismissed)
  - 📊 Statistiques des signalements
- ✅ **Workflow de Modération** :
  - 📝 États : Pending → In Review → Resolved/Dismissed
  - 📧 Email de notification aux modérateurs
  - 💬 Notes internes pour chaque action
- ✅ **Actions Admin** :
  - 👁️ **Review** : Marquer comme en cours de revue
  - ✅ **Resolve** : Fermer le signalement (action prise)
  - ❌ **Dismiss** : Clore sans action (faux positif)
  - 🗑️ **Delete** : Supprimer le signalement

### 🔄 Synchronisation des Données
- ✅ Intégration **TMDB** pour les métadonnées des dramas
- ✅ Intégration **RapidAPI** pour la disponibilité streaming
- ✅ **Système de cache configurable** (contrôlé par admin)
- ✅ Priorité configurable entre sources API (env ou DB)
- ✅ **Production Companies & Networks** : récupération des studios et réseaux de diffusion depuis TMDB

### 🔗 Liens de Streaming Dynamiques
- ✅ Affichage des plateformes de streaming disponibles (Netflix, Apple TV+, Amazon Prime, Disney+)
- ✅ Fallback sur **liens de recherche** quand RapidAPI n'a pas de données
- ✅ Génération automatique des URL de recherche avec le titre du drama encodé
- ✅ Cards avec icônes emoji et dégradés de couleurs par plateforme

### 🎭 Casting & Réseaux Sociaux des Acteurs
- ✅ **Affichage du casting complet** avec photos, biographies, dates de naissance
- ✅ **7 réseaux sociaux intégrés** pour chaque acteur:
  - 📸 Instagram
  - 📘 Facebook
  - 𝕏 Twitter/X
  - 🎵 TikTok
  - 📺 YouTube
  - 🎬 IMDb
  - 📚 Wikidata
- ✅ **Modale dédiée** pour explorer les acteurs avec détails complets
- ✅ **Projets récents** affichés pour chaque acteur
- ✅ **Lien vers tous les K-Dramas** d'un acteur spécifique
- ✅ **Traductions complètes** FR/EN/DE pour la modale des acteurs

### 🛠 Stack Technique
- **🐘 Langage :** PHP 8.2+
- **🚀 Framework :** [Laravel 12](https://laravel.com)
- **🎨 Frontend :** Tailwind CSS, Alpine.js, Vite
- **🗄️ Base de données :** MySQL (ou SQLite pour le développement local)
- **🔐 Authentification :** Laravel Breeze
- **📦 Gestionnaire de paquets :** Composer (PHP) & npm (JS)
- **🎯 Icons System:**
  - **Tabler Icons** (@tabler/icons) - 5,021 UI icons
  - **Simple Icons** (codeat3/blade-simple-icons) - 3,356 brand logos (ALL)
  - **Total: 8,377 icons** available in admin icon browser
  - **Dynamic count** - recalculated from filesystem (updates with composer)
  - **Labels in blue** - all icons display labels for better information

---

## 🌐 Localisation et Multilingue

### Langues Supportées
Le projet supporte **3 langues** complètement traduites :
- 🇫🇷 **Français (FR)** - Langue par défaut
- 🇬🇧 **English (EN)**
- 🇩🇪 **Deutsch (DE)**

### Comment Changer la Langue ?

**1️⃣ Via le Footer** (pages publiques)
```
Boutons 🇫🇷 FR | 🇬🇧 EN | 🇩🇪 DE en bas à droite
```

**2️⃣ Via l'Admin Sidebar** (pages admin)
```
Footer du sidebar avec sélecteur de langue
Disponible sur desktop et mobile
```

**3️⃣ Via le Profil Utilisateur**
```
Settings → "Langue préférée"
Sauvegardée et appliquée à chaque connexion
```

### Fichiers de Traduction
- `lang/fr/` - Fichiers français
- `lang/en/` - Fichiers anglais
- `lang/de/` - Fichiers allemands

Chaque langue contient :
- `common.php` - Textes communs (boutons, navigation, erreurs)
- `admin.php` - Textes admin (sidebar, pages admin, modales)
- `catalog.php` - Catalogue et filtres
- `contact.php` - Formulaire contact et FAQ
- `show.php` - Pages détail
- `auth.php` - Authentification
- `dashboard.php` - Dashboard utilisateur
- `watchlist.php` - Watchlist et exports
- `emails.php` - Templates email

---

## 📋 Prérequis
Avant de commencer, assurez-vous d'avoir installé :
- ✅ PHP >= 8.2
- ✅ Composer
- ✅ Node.js & npm
- ✅ Un serveur de base de données (MySQL, PostgreSQL ou SQLite)

---

## ⚙️ Installation et Configuration

### Développement Local

1. **📂 Cloner le dépôt :**
   ```bash
   git clone <url-du-repo>
   cd kdrama-laravel
   ```

2. **🐘 Installer les dépendances PHP :**
   ```bash
   composer install
   ```

3. **📦 Installer les dépendances JavaScript :**
   ```bash
   npm install
   ```

4. **🔧 Configurer l'environnement :**
   Copiez le fichier `.env.example` en `.env` et configurez vos accès à la base de données et vos clés d'API.
   ```bash
   cp .env.example .env
   ```

5. **🔑 Générer la clé d'application :**
   ```bash
   php artisan key:generate
   ```

6. **🗃️ Lancer les migrations et les seeders :**
   ```bash
   php artisan migrate --seed
   ```

7. **⚡ Compiler les assets :**
   ```bash
   npm run build
   ```

### 🚀 Déploiement en Production

Pour l'export PDF avec Browsershot, consultez le **[Guide Complet de Déploiement (DEPLOYMENT_BROWSERSHOT.md)](DEPLOYMENT_BROWSERSHOT.md)** qui couvre :
- Installation de Chromium/Chrome par OS (Linux, Windows, macOS)
- Configuration de Puppeteer
- Dépannage des erreurs courantes
- Monitoring et disk space
- Sécurité (throttling, timeouts)

---

## 💻 Commandes et Scripts

Le projet inclut des raccourcis pratiques dans le fichier `composer.json` :

- **🚀 Installation complète (Setup) :**
  ```bash
  composer run setup
  ```
  *(Installe les dépendances, configure le .env, génère la clé, lance les migrations et compile les assets).*

- **🔥 Lancer le serveur de développement :**
  ```bash
  composer run dev
  ```
  *(Lance simultanément le serveur PHP, la file d'attente, les logs et Vite).*

- **🧪 Lancer les tests :**
  ```bash
  composer run test
  ```

- **📦 Scripts npm classiques :**
  - `npm run dev` : Lancer Vite en mode développement.
  - `npm run build` : Compiler pour la production.

---

## 🔑 Variables d'Environnement

Voici les variables spécifiques nécessaires au fonctionnement des services tiers :

- `TMDB_API_KEY` : Votre clé API The Movie Database.
- `RAPIDAPI_KEY` : Votre clé RapidAPI (pour Streaming Availability).
- `RAPIDAPI_HOST` : `streaming-availability.p.rapidapi.com`

---

## 🔄 Synchronisation des Données

Le projet synchronise plusieurs types de données depuis les APIs **TMDB** et **Streaming Availability**. Voici le détail des informations récupérées et stockées :

### 📺 Contenus (Dramas)
Les métadonnées globales des séries et films :
- **Identifiants :** IDs TMDB et IMDB pour les références externes.
- **Visuels :** Chemins vers les posters et images de fond (backdrop).
- **Statistiques :** Score moyen (vote average), nombre de votes et popularité.
- **Dates :** Date de première diffusion (first air date), dernière diffusion et date de sortie.
- **Technique :** Type (TV/Movie), nombre de saisons/épisodes, durée des épisodes.

### 📝 Titres et Synopsis
Les informations textuelles (multi-langues, principalement anglais `en`) :
- **Titre :** Nom localisé du contenu.
- **Titre original :** Nom dans la langue d'origine.
- **Overview :** Résumé complet de l'intrigue.

### 🏷️ Genres et Origines
- **Genres :** Catégorisation (ex: Action, Romance, Drame) synchronisée avec TMDB.
- **Pays d'origine :** Liste des pays producteurs (Corée du Sud, Japon, etc.).

### 🔗 Disponibilité Streaming
Données synchronisées pour une région spécifique (par défaut `fr`) :
- **Plateforme :** Service proposant le contenu (Netflix, Disney+, Crunchyroll, etc.).
- **Accès :** Type de visionnage (abonnement, achat, location, gratuit).
- **Lien :** "Deep link" redirigeant directement vers la page du drama sur la plateforme.
- **Tarification :** Prix et devise si applicable (achat/location).

---

## 📂 Structure du Projet

- `app/Http/Controllers` : Logique de l'application (Dramas, Watchlist, Admin).
- `app/Services` : Intégrations avec les APIs externes et services (TMDB, Streaming Availability, WatchlistExportService).
- `app/Jobs` : Tâches de synchronisation en arrière-plan.
- `resources/views` : Templates Blade (utilisant Tailwind CSS et Alpine.js).
- `routes/web.php` : Définition des routes publiques, utilisateurs et administration.
- `database/migrations` : Structure de la base de données.

---

## 📥 Feature : Export de Watchlist

### 🎯 Workflow

1. **Accès à la modale** : Utilisateur clique sur "📥 Exporter" dans la page `/watchlist`

2. **Configuration des options** :
   - **Filtres** : Regardés ✅ / À regarder ✅ (sélectionnables)
   - **Colonnes** : Titre, Statut, Rating, Année, Vote TMDB, Genres, Networks, Synopsis, Images Poster
   - **Tri** : Date d'ajout (défaut), Titre A-Z, Rating personnel, Vote TMDB
   - **Format** : PDF ou CSV (image désactivée pour CSV)

3. **Génération** :
   - POST `/watchlist/export` avec options
   - **PDF** : Browsershot (Headless Chrome) → 3-5 secondes
   - **CSV** : Service PHP → 100-500ms

4. **Résultat** :
   - **PDF** : A4, marges 10mm, pagination (50 items/page), images 135x203px
   - **CSV** : colonnes dynamiques, UTF-8, échappement correct
   - Nom : `watchlist_{username}_{date}.{format}`

### 📚 Fichiers Clés

| Fichier | Rôle |
|---------|------|
| `app/Services/WatchlistExportService.php` | Logique d'export PDF/CSV |
| `app/Http/Controllers/WatchlistController.php` | Endpoint POST `/watchlist/export` |
| `resources/views/watchlist/_export-modal.blade.php` | Interface modale avec spinner |
| `resources/views/exports/watchlist-pdf.blade.php` | Template PDF (A4, HTML/CSS) |
| `MIGRATION_DOMPDF_TO_BROWSERSHOT.md` | Détails techniques de la migration |

### 💡 Améliorations Apportées

- ✅ **Migration DomPDF → Browsershot** pour support parfait des emojis et CSS
- ✅ **Spinner animé** avec message dynamique durant la génération
- ✅ **Colonnes dynamiques** respectant les sélections utilisateur
- ✅ **Networks & Synopsis** dans CSV
- ✅ **Format-aware UI** : image désactivée pour CSV
- ✅ **Images optimisées** (135x203px pour posters)

---

## 🧪 Tests
Les tests sont gérés par **PHPUnit** et **Laravel Dusk**.
Pour plus de détails, consultez le [**Guide des Tests (TESTING.md)**](TESTING.md).

Commandes rapides :
```bash
# PHPUnit (Unit & Feature)
php artisan test

# Laravel Dusk (Browser/E2E)
php artisan dusk
```

---

## 🎨 Feature : Système d'Icônes Intégré (Tabler + Simple Icons)

### 📁 Architecture Dual-Icons

**Tabler Icons** (5,021 total)
- Icônes UI générales pour l'application
- Fournis via `@tabler/icons` npm package
- Chargement depuis `node_modules/@tabler/icons/icons/outline/`
- Utilisés: navigation, boutons, UI controls

**Simple Icons** (3,356 total - TOUS disponibles)
- Logos de marques (social media, services)
- Fournis via `codeat3/blade-simple-icons` package
- Chargement depuis `vendor/codeat3/blade-simple-icons/resources/svg/`
- Utilisés: social links, streaming services, branding
- **TOUS les 3,356 icons** disponibles dans l'admin icon browser

**Total: 8,377 Icons**
- Dynamique: recalculé depuis le système de fichiers à chaque requête
- Mise à jour automatique si les packages changent via `composer update`

### 🔍 Admin Icon Browser (`/admin/icons`)

**Fonctionnalités:**
- ✅ Recherche en direct sur 8,377 icons (Tabler 5,021 + Simple Icons 3,356)
- ✅ Pagination: 100 icons par page + bouton "Load More"
- ✅ **Compteur dynamique**: "Affichage 200 icons sur 8377" (se met à jour automatiquement)
- ✅ **Labels en bleu** pour TOUS les icons (Tabler + Simple) - meilleure information
- ✅ Type badges: "Tabler" ou "Simple Icons" dans les tooltips
- ✅ Copy to clipboard automatique avec préfixe `si-` pour Simple Icons
- ✅ Live search avec 300ms debounce
- ✅ Affichage du SVG réel (pas de placeholders)

**Exemple d'utilisation:**
```
Recherche "youtube" → Affiche:
  • brand-youtube (Tabler) + label bleu
  • brand-youtube-kids (Tabler) + label bleu
  • si-youtube (Simple Icons) ← avec préfixe + label bleu

Click sur "si-youtube" → copie "si-youtube" dans clipboard
```

### 🎯 Icon Picker Modal (pour Admin/Author)

**Utilisée dans:** `/admin/author` - Sélection des icônes pour les social links

**Fonctionnalités:**
- ✅ Modal searchable avec tous les icons
- ✅ Affiche SVG avec hover tooltip
- ✅ Sélection = ajoute `si-` prefix automatiquement pour Simple Icons
- ✅ Toast notification avec nom complet
- ✅ Remplit l'input form avec le nom correct

**Exemple:**
```
1. Click "🎨" button → ouvre modal
2. Cherche "youtube"
3. Click sur icon YouTube
4. Input reçoit: "si-youtube" (pas "youtube")
5. Toast: "✅ Selected: si-youtube"
```

### 🦶 Footer Display (Social Links)

**Support Intelligent:**
- ✅ Détecte le type par préfixe `si-`
- ✅ Simple Icons: load SVG + `fill-current` styling
- ✅ Tabler Icons: load SVG + `stroke-current` styling
- ✅ Fallback placeholder si icon manque dans les deux sources
- ✅ Compatible avec tous les types d'icônes

**Exemple:**
```
Social Link: instagram (Simple)
1. Icon name: "si-instagram"
2. Détecte: préfixe "si-" → Simple Icon
3. Load: /vendor/codeat3/blade-simple-icons/resources/svg/instagram.svg
4. Render: <svg class="w-6 h-6 fill-current">...</svg>
5. Affiche: Logo Instagram en couleur blanche
```

### 🛠️ Fichiers Clés (Icons System)

| Fichier | Rôle |
|---------|------|
| `app/Http/Controllers/Admin/AdminIconsController.php` | Fallback logic + pagination |
| `app/Helpers/IconHelper.php` | List of all Simple Icons + helpers |
| `resources/views/admin/icons/search.blade.php` | Browser avec "Load More" |
| `resources/views/components/icon-picker-modal.blade.php` | Modal avec si- prefix support |
| `resources/views/components/footer.blade.php` | Dual-source loading (Tabler + Simple) |

### 💡 Pattern de Nommage

**Simple Icons:** `si-{icon-name}`
- Affichage: `si-youtube`
- Copié: `si-youtube`
- Utilisé dans formulaires: `social_links[0][icon] = "si-youtube"`

**Tabler Icons:** `{icon-name}` (pas de préfixe)
- Affichage: `brand-youtube`
- Copié: `brand-youtube`
- Utilisé partout: `{icon-name}`

---

## 📧 Feature : Formulaire de Contact & Admin Panel

### 🎯 Page de Contact (`/contact`)
- **Formulaire accessible publiquement**
- Champs : Nom, Email, Sujet, Message (5000 caractères max)
- **Pièces jointes** : PDF, CSV, Excel, images, docs (max 5MB)
- **Counter dynamique** pour le message
- Envoi d'email à l'admin avec notification
- Sauvegarde en base de données (pour archivage)
- Gestion des erreurs d'email (enregistrement même si email échoue)

### 🛠️ Admin Panel : Gestion des Messages (`/admin/contact`)
- **Liste avec statistiques** : Total, En attente, Lus, En cours, Réglés, Spam, Erreur email
- **Recherche & filtrage** : Par nom, email, sujet, message, statut
- **Pagination** : 15 messages par page
- **Codes couleur** : Badges par statut avec visuels distincts

### 📋 Détail d'un Message (`/admin/contact/{id}`)
- **Infos expéditeur** : Nom, email, date, statut email
- **Contenu complet** : Message avec formatage préservé
- **Pièce jointe** : Affichage et téléchargement
- **Workflow de statut** (avec transitions logiques) :
  - **En attente (⏳)** → Lire ou Spam
  - **Lu (👀)** → En cours, Réglé ou Spam
  - **En cours (🔧)** → Réglé ou Spam
  - **Réglé (✅)** → Aucun changement possible (terminal)
  - **Spam (🚫)** → Retirer du spam (back to pending)
- **Timeline** : Affiche quand le message a été lu et réglé
- **Suppressions** : Avec confirmation (définitive)

### 📚 Fichiers Clés (Contact)
| Fichier | Rôle |
|---------|------|
| `app/Http/Controllers/ContactController.php` | Formulaire + envoi email |
| `app/Http/Controllers/Admin/AdminContactController.php` | Admin CRUD |
| `app/Models/ContactMessage.php` | Modèle avec pièces jointes |
| `app/Mail/ContactMail.php` | Mailable avec attachments |
| `resources/views/contact.blade.php` | Formulaire public |
| `resources/views/admin/contact/index.blade.php` | Liste des messages |
| `resources/views/admin/contact/show.blade.php` | Détail + workflow |
| `database/migrations/*contact_messages*` | Schéma BD |

---

## 🔍 Feature : Telescope (Débogage Admin)

### Qu'est-ce que Telescope?
Outil de débogage Laravel pour visualiser en temps réel :
- **Requêtes SQL** : Toutes les queries avec temps d'exécution
- **Requêtes HTTP** : Paramètres, réponses, durées
- **Logs** : Erreurs, warnings, infos
- **Exceptions** : Stack trace complet
- **Events, Cache, Jobs** : Monitoring complet

### 🔐 Accès (Admin Uniquement)
- URL : `http://localhost:8000/telescope`
- **Sécurité** : Middleware `auth` + Gate `viewTelescope` (admins uniquement)
- Impossible d'accéder en visiteur ou user normal

### 📚 Fichiers Clés (Telescope)
| Fichier | Rôle |
|---------|------|
| `config/telescope.php` | Configuration (middleware auth + Authorize) |
| `app/Providers/TelescopeServiceProvider.php` | Gate pour vérifier is_admin |
| `database/migrations/*telescope_entries*` | Table de stockage |

### 📚 Fichiers Clés (Admin UI & Export Management & Force Password Change)
| Fichier | Rôle |
|---------|------|
| **Navigation & Layout** | |
| `resources/views/components/admin-sidebar.blade.php` | Sidebar réutilisable avec tous les menus |
| `resources/views/layouts/app.blade.php` | Hide nav/footer on admin routes |
| `resources/views/admin/dashboard.blade.php` | Modern dashboard with sidebar + stat cards |
| **Force Password Change** | |
| `app/Http/Middleware/CheckPasswordMustChange.php` | Middleware enforcement on all routes |
| `app/Http/Controllers/Admin/AdminUserController.php` | resetPassword() method for password reset |
| `app/Http/Controllers/ProfileController.php` | changePassword() & updatePassword() methods |
| `app/Mail/PasswordResetMail.php` | Email mailable with temporary password |
| `resources/views/password/change.blade.php` | User password change form |
| `resources/views/admin/users/edit.blade.php` | User edit with reset password modal |
| `database/migrations/2026_03_10_*_add_password_must_change_to_users_table.php` | Schema migration |
| **Export Management** | |
| `app/Models/ExportLog.php` | Modèle de tracking des exports |
| `database/migrations/2026_03_09_193246_create_export_logs_table.php` | Table export_logs |
| `app/Http/Controllers/Admin/AdminExportController.php` | Admin export management (cache/stats/export user) |
| `resources/views/admin/exports/cache.blade.php` | Interface gestion cache PDF |
| `resources/views/admin/exports/stats.blade.php` | Dashboard statistiques exports |
| `resources/views/admin/exports/_admin-export-modal.blade.php` | Modale réutilisable export avec options |
| **Admin Pages** | |
| `resources/views/admin/users/index.blade.php` | User management avec sidebar |
| `resources/views/admin/settings/index.blade.php` | Settings avec sidebar |
| `resources/views/admin/author/edit.blade.php` | Author & SEO avec sidebar |
| `resources/views/admin/contact/index.blade.php` | Messages avec sidebar |
| `resources/views/admin/icons/search.blade.php` | Icon picker avec sidebar |

---

## 🚀 Fonctionnalités Complétées ✅

### Phase 1 : Authentification & Admin (✅ Complétée)
- [x] Système d'authentification (Laravel Breeze)
- [x] Admin panel avec dashboard
- [x] Gestion des utilisateurs (CRUD)
- [x] Gestion des paramètres système (settings)
- [x] First-time setup wizard

### Phase 2 : Système de Notation (✅ Complétée)
- [x] Table `ratings` (multi-utilisateurs)
- [x] Endpoints AJAX pour noter/supprimer notes
- [x] Affichage des ratings sur catalog, watchlist et détail
- [x] Suppression de rating au re-clic

### Phase 3 : Auteur/Site/SEO + Social Links (✅ Complétée)
- [x] Page d'administration auteur/site
- [x] Métadonnées SEO (meta tags, OG tags)
- [x] Gestion des social links
- [x] **Drag-and-drop pour réordonner** (Sortable.js + AJAX)
- [x] Icon picker modal avec Tabler Icons
- [x] URL auto-protocol (client + server)
- [x] Affichage des social links dans le footer

### Phase 4 : Production Data & Streaming Links (✅ Complétée)
- [x] Récupération production_companies et networks depuis TMDB
- [x] Synchronisation des données via Artisan command
- [x] **Streaming Search Links** :
  - Affichage automatique des liens de recherche quand RapidAPI est vide
  - Génération des URLs pour Netflix, Apple TV+, Amazon Prime, Disney+
  - Cards compactes avec icônes et dégradés
- [x] **Recommandations compactes** :
  - Grille optimisée (3-6 colonnes selon breakpoint)
  - Images réduites pour meilleure lisibilité
  - Support des emojis de notation sur les cards

### Phase 5 : Export de Watchlist (✅ Complétée)
- [x] **Export PDF avec Browsershot (Headless Chrome)**
  - Rendu parfait des emojis et icônes (contrairement à DomPDF)
  - Mise en page élégante avec images (135x203px)
  - Pagination automatique (50 items par page)
  - Posters téléchargés et encodés en base64
  - Stats et options affichées sur page 1
- [x] **Export CSV** :
  - Colonnes sélectionnables (titre, statut, rating, année, vote TMDB, genres, networks, synopsis)
  - Escaping CSV correct pour guillemets et sauts de ligne
  - Respect dynamique des colonnes cochées dans la modale
- [x] **Modale d'export interactive**
  - Filtrage : regardés, à regarder, ou les deux
  - Colonnes : sélection personnalisée de chaque colonne
  - Tri : par date d'ajout, titre, rating personnel, vote TMDB
  - Format : PDF ou CSV
  - Image Poster : désactivée automatiquement pour CSV
  - **Envoi par email** : Checkbox pour recevoir le fichier par email
- [x] **UX améliorée**
  - Spinner animé durant la génération du PDF
  - Mise à jour du message (génération → téléchargement)
  - Nom de fichier automatique avec date
  - Boutons grisés pendant l'export

### Phase 6 : Cache des PDFs & Email Notifications (✅ Complétée)
- [x] **Cache des PDFs exportés**
  - Hash MD5 des paramètres (user_id + filtres + colonnes + tri)
  - Stockage dans `storage/app/exports/`
  - TTL de 7 jours (expiration automatique)
  - ⚡ Regénération quasi-instantanée pour mêmes paramètres
- [x] **Envoi d'export par email**
  - Checkbox "📧 Envoyer le fichier par email" dans la modale
  - Email élégant avec stats (total, regardés, à regarder)
  - PDF/CSV en attachement (base64 encodé)
  - Lien retour vers la watchlist
  - Support Herd: URL locale `kdrama-laravel.test` en développement
- [x] **Job asynchrone pour email**
  - Queue driver `sync` exécution immédiate (développement)
  - Logging des succès/erreurs
  - Retry automatique en cas d'échec
- [x] **Nettoyage automatique des caches**
  - Commande Artisan: `php artisan exports:cleanup`
  - Scheduler: exécution quotidienne à 2h du matin
  - Suppression des PDFs > 7 jours

### Phase 6 : Contact Form & Admin Management (✅ Complétée)
- [x] Formulaire de contact public avec pièces jointes
- [x] Gestion d'emails pour l'admin (notification)
- [x] Sauvegarde persistante des messages même si email échoue
- [x] Admin panel avec liste, recherche et filtrage
- [x] Workflow de statut avec transitions logiques (Pending → Read → In Progress → Resolved)
- [x] Détail du message avec pièce jointe téléchargeable
- [x] Timeline d'état (read_at, resolved_at)
- [x] Suppression définitive avec confirmation

### Phase 7 : Telescope & Admin Debugging (✅ Complétée)
- [x] Installation et configuration de Telescope
- [x] Migration des tables Telescope
- [x] Sécurisation : accès admin uniquement (middleware auth + Gate)
- [x] Redirection automatique vers login pour visiteurs
- [x] Refus d'accès (403) pour users non-admin

### Phase 9 : Admin UI Redesign & Sidebar Navigation (✅ Complétée)
- [x] **Persistent Sidebar Navigation**
  - Site links (Home, Kdrams, Contact, Dashboard, Watchlist)
  - Complete admin section with all features
  - User info footer with profile + logout
  - Responsive: desktop sidebar + mobile overlay menu
- [x] **Hidden Top Navigation on Admin Pages**
  - Navbar hidden on all admin routes
  - Footer also hidden for clean admin interface
  - Public pages keep their navigation
- [x] **Modern Dashboard Redesign**
  - Gradient stat cards (Users, Contents, Data)
  - Feature grid with descriptions
  - Export watchlist section
  - Hover effects and shadows
- [x] **Responsive Admin Pages**
  - Sidebar on desktop, menu button on mobile
  - Sticky headers with titles + emoji + description
  - All admin pages integrated (Users, Settings, Author, Contact, Icons, Exports)

### Phase 10 : Integrated Icon System (✅ Complétée)
- [x] **Dual Icon Library System**
  - Tabler Icons (5000+) + Simple Icons (1000+) integrated
  - Automatic fallback for missing Simple Icons to Tabler alternatives
- [x] **Admin Icon Browser** (`/admin/icons`)
  - Search 6000+ combined icons with live filtering
  - Pagination: 100 icons per page + "Load More" button
  - Dynamic counter: "Displaying 200 icons out of 5051"
  - Type badges: "Tabler" vs "Simple Icons"
  - Copy to clipboard with si- prefix for Simple Icons
- [x] **Icon Picker Modal** (`icon-picker-modal.blade.php`)
  - Used in admin/author for social links selection
  - Auto-adds si- prefix when selecting Simple Icons
  - Toast notification on selection
- [x] **Footer Social Links** with dual-source display
  - Detects icon type by si- prefix
  - Loads from correct source (Tabler or Simple Icons)
  - Proper SVG styling (fill vs stroke)
- [x] **Helper Methods** (IconHelper class)
  - getSimpleIcons() - All 30+ brand logos
  - getSimpleIconLabel() - Get human-readable label
  - hasSimpleIcon() - Check if icon exists

### Phase 11 : Force Password Change System (✅ Complétée)
- [x] **Admin Password Reset Feature** (`/admin/users/{id}/edit`)
  - 🔑 Button "🔑 Generate & Send New Password"
  - 🎨 Custom confirmation modal (replaces browser alert)
  - ✉️ Secure temporary password (12 chars: mixed case, numbers, symbols)
  - 📧 Email notification with password reset mail template
  - 🔒 Sets `password_must_change=true` flag on user
- [x] **Middleware Enforcement** (`CheckPasswordMustChange`)
  - Applied to ALL routes (public, protected, admin)
  - Redirects to `/change-password` if flag is true
  - User cannot access any page until password is changed
  - Skips only password change and authentication routes
- [x] **Password Change Page** (`/change-password`)
  - Requires current password verification
  - New password with strength requirements display
    - At least 8 characters
    - Mix of uppercase & lowercase
    - At least one number or symbol
  - Password confirmation field
  - Clears `password_must_change=false` on successful change
  - Redirects to dashboard with success message
  - Shows warning if mandatory password change is required
- [x] **Database Schema**
  - Added `password_must_change` column to users table
  - Boolean field (default: false)
  - Proper casting in User model
  - Included in fillable array for mass assignment
- [x] **Admin Sidebar Integration**
  - 🔍 Telescope debug link added to Admin menu (desktop + mobile)
  - Gate `viewTelescope` ensures only `is_admin=true` users can access
  - Visible in Admin panel under "⚙️ Admin" section

### Phase 8 : Admin Export Management & Statistics (✅ Complétée)
- [x] **Logging automatique des exports**
  - Enregistrement BD: user_id, format, item_count, file_size, cache_hash, was_cached, generation_time, filters
  - Exécuté lors de chaque export (PDF/CSV)
  - Tracking des cache hits vs nouvelles générations
- [x] **Interface de gestion du cache** (`/admin/exports/cache`)
  - Liste des fichiers PDF en cache avec tailles
  - Statut expiration (actif/expiré) avec jours restants
  - Suppression individuelle de fichiers
  - Actions groupées : purger tout / purger expiré seulement
  - Sécurité : validation avec `realpath()` check
- [x] **Dashboard de statistiques** (`/admin/exports/stats`)
  - Total exports tous formats + breakdown PDF/CSV
  - Taux de cache hit avec pourcentage
  - Espace disque utilisé
  - Temps moyen génération (cachés vs non-cachés)
  - Gain de performance du cache en %
  - Top 5 utilisateurs par nombre d'exports (avec email)
  - Graphique quotidien des exports (7/30/90 jours configurable)
- [x] **Modèle ExportLog**
  - Relation `belongsTo(User::class)`
  - Casts typés pour all fields
  - Index sur user_id, format, created_at
  - Foreign key avec cascade delete
- [x] **Export des watchlists par l'admin avec filtres complets**
  - Modale réutilisable avec options complètes (filtres, colonnes, tri, format)
  - Bouton "⚙️ Exporter avec options" sur dashboard admin (`/admin`)
  - Admin peut exporter sa propre watchlist avec personnalisation
  - Bouton export (⚙️) sur page gestion utilisateurs (`/admin/users`)
  - Admin peut exporter la watchlist de n'importe quel utilisateur
  - Endpoint: `POST /admin/exports/user/{userId}` (accepte JSON avec options)
  - Logging automatique + cache PDF + support email

### Améliorations Techniques
- [x] Documentation détaillée sur les types de données synchronisés
- [x] Système de cache configurable pour API
- [x] Priorité configurable entre sources API
- [x] Helper `StreamingLinkHelper` pour génération d'URLs de recherche
- [x] Component `production-info` pour affichage studios/réseaux
- [x] Service `WatchlistExportService` avec Browsershot
- [x] Modal réactive avec JavaScript pour gestion des options
- [x] Service de gestion des messages de contact (ContactController)
- [x] Workflow state machine avec transitions validées
- [x] Telescope avec Gate personnalisée pour les admins

## 🔮 Fonctionnalités Futures (À Prévoir)

Voici une liste des idées et améliorations envisagées pour les prochaines phases du projet. Ces features sont classées par niveau de complexité.

### 🎯 Simples à Implémenter (Rapides)

1. **Plusieurs Listes Personnalisées**
   - Créer plusieurs watchlists au lieu d'une seule (p.ex. "À regarder avec ma copine", "Action", "Romantique")
   - Chaque K-drama peut être dans plusieurs listes
   - Export par liste

2. **Statistiques Personnelles de l'Utilisateur**
   - Total de K-dramas regardés
   - Genres favoris (top 3-5)
   - Acteurs/réalisateurs les plus vus
   - Temps total regardé (si nb d'épisodes ajouté)
   - Distribution des ratings (👎, 👍, 👍👍)
   - Graphique de progression (K-dramas/mois)

3. **Dark/Light Mode Toggle**
   - Améliorer l'accessibilité et l'expérience utilisateur
   - Stocker en localStorage ou base de données

4. **Recherche Avancée Améliorée**
   - Filtrer par: genre, année de sortie, statut (en cours/terminé), réseau (Netflix, Apple TV, etc)
   - Tri multiple: rating utilisateur, rating TMDB, date d'ajout

5. **Badges/Achievements**
   - Gamification légère (p.ex. "Regardé 10 K-dramas", "Fan de Romance")
   - Affichage dans le profil utilisateur
   - Système de progression

### 🔗 Moyennes à Implémenter (Travail Modéré)

6. **Système d'Avis Texte (Reviews)**
   - Avis écrit en complément de la notation 1-3
   - Affichage sur page détail du K-drama
   - Options: public, amis ou privé

7. **Partage de Watchlist**
   - Générer un lien public pour partager sa watchlist
   - Lien lisible: `/user/{id}/watchlist/public`
   - Options de confidentialité: privé/amis/public

8. **Import depuis MyDramaList (MDL)**
   - Parser fichier CSV de MyDramaList
   - Importer watchlist existante automatiquement
   - Mapper TMDB IDs

9. **Système de Notifications/Reminders**
   - Rappels email pour items wishlist
   - Alertes: "K-drama X est dispo sur Netflix!"
   - Nouveautés en genres préférés
   - Cron job pour envois périodiques

### 🚀 Avancées à Implémenter (Plus Complexes)

10. **Système de Recommandations**
    - "Utilisateurs qui ont aimé X ont aussi aimé Y"
    - Basé sur ratings similaires ou watchlist croisées
    - Collaborative filtering simple

11. **Communauté/Social**
    - Voir ratings d'autres utilisateurs
    - Commentaires sur K-dramas
    - Top-rated par la communauté
    - Profils utilisateurs publics (optionnel)

12. **Calendrier de Sortie**
    - Dates des nouveaux épisodes
    - Alertes pour prochains épisodes de la watchlist
    - ICS export pour intégration calendrier

13. **Tests de Bout en Bout**
    - Laravel Dusk pour tests E2E (navigateur)
    - Coverage des workflows critiques

14. **API Publique**
    - REST API pour développeurs
    - Documentation OpenAPI
    - Rate limiting et authentication

---

## ✅ Corrections & Améliorations Récentes

### 🐛 2026-03-12: Deux Fixes Critiques

#### 1. Fix: PDF Export "Watching" Filter
**Problème:** Le modal d'export admin ne prenait pas en compte le filtre "En train de voir", affichant "EN COURS 0" au lieu de 12 items

**Cause Racine:** AdminExportController manquait la validation `filters.watching`
- Laravel silencieusement supprimait le filtre `watching`
- Options contenait seulement watched + to_watch
- PDF récupérait 33 items au lieu de 45
- Stats affichaient: "TOTAL 33 REGARDÉS 19 EN COURS 0 À VOIR 14"

**Solution:**
- ✅ Ajout validation: `'filters.watching' => 'sometimes|boolean'`
- ✅ Extraction du filtre: `'watching' => $request->boolean('filters.watching', true)`
- ✅ Suppression logs debug de WatchlistExportService
- ✅ Alignement du endpoint admin avec user endpoint

**Commit:** `2ada0c3` - Fix: Add missing 'watching' filter to admin export endpoint

#### 2. Fix: Home Page Drama Links
**Problème:** Les liens sur la page d'accueil utilisaient l'ID local au lieu du TMDB ID

**Cause Racine:** Deux sources de données différentes:
- Featured depuis base de données → utilise `tmdb_id`
- Newest/Upcoming depuis API TMDB → utilise `id`

**Solution:**
- ✅ Fallback operator: `$item['tmdb_id'] ?? $item['id']`
- ✅ Correction des 3 sections: Featured, Newest Releases, Upcoming Releases
- ✅ Maintenant tous les liens utilisent le bon TMDB ID

**Commit:** `334cfe8` - Fix: Use correct ID field in home page drama links

---

### ✨ 2026-03-10: Système d'Icônes Complet + Localisations + Force Password Change

#### Complete Icon System Upgrade
- ✅ **8,377 total icons** accessible: 5,021 Tabler + 3,356 Simple Icons
- ✅ **Admin Icon Browser** (`/admin/icons`) avec:
  - Recherche live sur tous les 8,377 icons
  - Pagination: 100 icons + "Load More"
  - Labels en bleu pour meilleure info
  - Type badges (Tabler vs Simple Icons)
  - Copy to clipboard avec préfixe `si-`
  - Compteur dynamique recalculé depuis filesystem
- ✅ **Fallback System:** Simple Icons manquants = Tabler fallback automatique
- ✅ **Icon Picker Modal** avec recherche pour sélection icônes admin

#### Actor Modal - Social Media Integration
- ✅ **7 réseaux sociaux** intégrés:
  - 📸 Instagram | 📘 Facebook | 𝕏 Twitter
  - 🎵 TikTok | 📺 YouTube | 🎬 IMDb | 📚 Wikidata
- ✅ **Traductions complètes** FR/EN/DE pour tous les labels
- ✅ **URL generation** fixée avec Blade string concatenation
- ✅ **Icônes professionnelles** depuis Simple Icons package

#### Hero Title Placeholder
- ✅ Changé de hardcoded "K-Dramas" à placeholder `{dramas}`
- ✅ Injected dynamiquement avec `str_replace()` dans index.blade.php
- ✅ Préserve styling gradient tout en restant translatable
- ✅ Élimine duplication de texte

#### Complete Laravel Localization (FR/EN/DE)
- ✅ **3 langues complètes:** Français (défaut) | English | Deutsch
- ✅ **9 fichiers traduction** par langue
- ✅ **48+ clés admin-spécifiques**
- ✅ **Language Switcher Footer:** 🇫🇷 FR | 🇬🇧 EN | 🇩🇪 DE
- ✅ **Admin Sidebar Selector:** Changement instantané
- ✅ **User Profile Preference:** Sauvegardé dans `user.preferred_language`
- ✅ **Tous les admin pages traduits:** settings, users, author, icons, contact
- ✅ **Primary Button** changé en `bg-red-600` pour meilleure visibilité
- ✅ **Dropdown & Z-index fixes** pour layering correct

#### Force Password Change System
- ✅ **Admin Panel:** Bouton "🔑 Generate & Send New Password"
  - Génère mot de passe temporaire sécurisé (12 chars)
  - Envoi email automatique (PasswordResetMail)
  - Set `password_must_change=true` flag
- ✅ **Middleware Enforcement:** `CheckPasswordMustChange`
  - S'applique à TOUS les routes
  - Redirige vers `/change-password`
  - Utilisateur DOIT changer avant accès
- ✅ **Password Change Page:** `/change-password`
  - Vérification password courant requise
  - Affichage requirements force
  - Confirmation password
  - Clears flag et redirige
- ✅ **Database:** Colonne `password_must_change` ajoutée
- ✅ **Sidebar Integration:** Lien Telescope 🔍 sécurisé pour admins

---

## 🔄 Recent Major Updates (2026-04-15 to 2026-04-16)

### ⚙️ Professional Async Queue with Job History (2026-04-16)
- ✅ **Queue Driver:** Database-backed persistent jobs (MySQL)
- ✅ **Admin Jobs Dashboard** at `/admin/jobs`:
  - 3 Quick Action Cards (Sync Actors, Cleanup PDFs, Update Production)
  - Live Pending Queue with auto-refresh (2 second intervals)
  - Failed Jobs Management (Retry/Delete buttons)
  - **Live Logs Viewer** with:
    - Syntax colorization (timestamps, log levels, keywords, numbers)
    - Color legend explanation
    - Whitespace preservation for readability
    - Auto-refresh toggle
    - Clear logs button
- ✅ **Job History Tracking** at `/admin/jobs/history`:
  - Complete execution history (completed & failed jobs)
  - Duration tracking, status, metadata
  - Desktop table + mobile card views
  - Pagination (20 per page)
  - Metadata display (dramas_processed, actors_synced, etc.)
- ✅ **Memory Optimization:**
  - Batch size reduced from 500 to 100
  - Memory limit: 256M (with option for 512M in production)
  - Execution time: 300 seconds (5 minutes)
  - SyncPopularActors completes in ~1m 45s for 5568 actors
- ✅ **Documentation:** Complete `QUEUE.md` guide with deployment instructions

### 📋 Content Reporting & Moderation (2026-04-16)
- ✅ **Report Button** on drama/actor cards
- ✅ **Report Modal** with category selection and messaging
- ✅ **Admin Dashboard** at `/admin/contact` for managing reports
- ✅ **Email Notifications** to admin on new reports
- ✅ **Status Workflow:** pending → in_progress → resolved/spam

### 🚫 Adult Content Marking (2026-04-16)
- ✅ **Admin Toggle** to mark K-dramas as adult content
- ✅ **User Filter** with `?hide_adult=1` parameter
- ✅ **Default Behavior** - adult content hidden for guests
- ✅ **Database Column:** `kdramas.adult_only`

### 🎭 Actor Enhancements (2026-04-16)
- ✅ **Detail Caching** with lazy-load system
- ✅ **Combined Credits** JSON column for filmography
- ✅ **Enhanced Modal** with bio, birth info, social links
- ✅ **Cache Duration:** 30 days before re-fetching
- ✅ **Performance Gain:** Actor details load instantly from cache
- ✅ **"View All Dramas" Functionality:**
  - Actor modal button now filters catalog by actor
  - Direct TMDB credits lookup for faster filtering
  - Actor ID stored in URL for state preservation
  - Auto-clears actor filter when user starts searching
  - Works on both catalog and detail pages

### 🔍 Search & Catalog Improvements (2026-04-15/16)
- ✅ **Actor Search Optimization** with API fallback
- ✅ **Catalog Pagination Preservation** when switching views
- ✅ **Better Filtering** across drama and actor catalogs

### ⚙️ Settings Management (2026-04-15)
- ✅ **Sensitivity Flags** for protecting API keys/secrets
- ✅ **Custom Groups** for organizing settings
- ✅ **Fallback Descriptions** auto-generated for custom categories
- ✅ **Missing Migrations** included for settings sensitivity columns
- ✅ **Toggle UI** for sensitive status in admin settings page

### 🌐 Complete Multilingual Coverage (2026-04-15)
- ✅ **100% Translation** across all 9 language files
- ✅ **500+ Translation Keys** covering all admin & user pages
- ✅ **Automatic Detection** via `SetLocale` middleware

### 🔧 Development & Deployment Improvements (2026-04-15)
- ✅ **Corrected .gitignore**
  - Properly ignores development files
  - Excludes storage and build artifacts
  - Prevents tracking of local env files
  - Boost configuration files handled correctly
- ✅ **Updated Composer Dependencies**
  - Latest stable versions installed
  - Security patches applied
  - Performance optimizations from newer versions
- ✅ **Laravel Boost Integration**
  - Development files added to configuration
  - MCP server configurations ready
  - Agent configurations for development

---

### 🔄 2026-03-09: Netflix-style Rating + Admin Sidebar + Icon Picker

1. ✅ **Rating System Consolidé** - watchlist_items.rating
2. ✅ **Admin Sidebar Dropdowns** - localStorage persistence
3. ✅ **Icon Picker Fixes** - event delegation + clipboard API

---

## 🚀 TODO / Améliorations Futures (En Cours)
- [ ] Tests de bout en bout avec Laravel Dusk
- [ ] Recommandations basées sur les ratings
- [ ] Système de reviews détaillées
- [ ] Notifications utilisateur

---

## 📄 Licence
Ce projet est sous licence [MIT](https://opensource.org/licenses/MIT).
