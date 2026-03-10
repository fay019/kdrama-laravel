est obliger de changer le mot de passe que elle sois en mesure de le savoir, ext donc meme pour autre options que on as deja ajouter avant, donc controlle le project pour savoire se que claude dois savoir a la lecture de claude.md# 🧪 Guide des Tests - KDrama Laravel

Ce document explique comment exécuter les différents types de tests mis en place sur le projet KDrama Laravel.

## 📋 Types de Tests

Le projet utilise trois niveaux de tests :
1.  **Tests Unitaires (Unit)** : Tests de petits composants isolés (classes de services, helpers).
2.  **Tests de Fonctionnalités (Feature)** : Tests de routes, contrôleurs et interactions avec la base de données.
3.  **Tests de Bout en Bout (Browser/E2E)** : Tests simulés dans un navigateur réel avec Laravel Dusk.

---

## 🧪 Liste des Tests Disponibles

### 📦 Tests Unitaires (`tests/Unit`)
- **`ExampleTest.php`** : Test d'exemple de base.
  ```bash
  php artisan test tests/Unit/ExampleTest.php
  ```

### 🚀 Tests de Fonctionnalités (`tests/Feature`)
- **`ExampleTest.php`** : Vérifie que la page d'accueil répond correctement.
- **`ApiCacheTest.php`** : Valide la mise en cache des appels aux APIs TMDB et RapidAPI.
- **`ApiServiceTest.php`** : Vérifie la robustesse des appels API (gestion des succès, erreurs 500, timeouts et parsing des données).
- **`ProfileTest.php`** : Tests de la gestion du profil utilisateur (Breeze).
- **Sous-dossier `Auth/`** : Tests complets du système d'authentification (Breeze).
    - `AuthenticationTest.php` : Connexion utilisateur.
    - `EmailVerificationTest.php` : Vérification d'e-mail.
    - `PasswordConfirmationTest.php` : Confirmation de mot de passe.
    - `PasswordResetTest.php` : Réinitialisation de mot de passe.
    - `PasswordUpdateTest.php` : Mise à jour de mot de passe.
    - `RegistrationTest.php` : Inscription d'utilisateur.
  ```bash
  # Lancer tous les tests de fonctionnalité
  php artisan test --testsuite=Feature

  # Lancer un test spécifique (ex: Cache API)
  php artisan test tests/Feature/ApiCacheTest.php

  # Lancer les tests d'authentification
  php artisan test tests/Feature/Auth
  ```

### 🌐 Tests de Navigateur (`tests/Browser`)
- **`ExampleTest.php`** : Vérifie le rendu de la page d'accueil et la présence du titre "K-Dramas".
- **`AuthenticationTest.php`** : Teste le flux complet d'authentification (connexion d'un utilisateur créé par factory).
- **Dossier `Pages/`** : Contient les objets de page (Page Objects) pour faciliter la navigation.
    - `HomePage.php` : Représente la page d'accueil.
    - `Page.php` : Classe de base pour les pages Dusk.
  ```bash
  # Lancer tous les tests Dusk
  php artisan dusk

  # Lancer un test spécifique
  php artisan dusk tests/Browser/AuthenticationTest.php

  # Lancer les tests sans interface graphique (Headless)
  # (Configuration par défaut dans DuskTestCase.php)
  ```

---

## 🚀 Exécuter les Tests Standard (Unit & Feature)

Les tests unitaires et fonctionnels sont gérés par **PHPUnit**.

### Commande Rapide
```bash
composer run test
```
*Cette commande vide le cache de configuration et lance tous les tests PHPUnit.*

### Commandes Artisan
```bash
# Lancer tous les tests
php artisan test

# Lancer un fichier spécifique
php artisan test tests/Feature/ApiCacheTest.php

# Lancer avec un filtre sur une méthode
php artisan test --filter test_cache_is_working
```

---

## 🌐 Exécuter les Tests de Navigateur (Laravel Dusk)

Les tests Dusk simulent un utilisateur réel naviguant sur le site. Ils nécessitent que le serveur de développement soit lancé.

### 1. Prérequis
- Assurez-vous d'avoir un fichier `.env.dusk.local` configuré (généralement pointant vers `http://127.0.0.1:8000`).
- Le binaire `chromedriver` doit être présent dans `vendor/laravel/dusk/bin/`.

### 2. Lancer le serveur
Dans un terminal séparé :
```bash
php artisan serve
```

### 3. Exécuter Dusk
```bash
# Lancer tous les tests Dusk
php artisan dusk

# Lancer un test spécifique
php artisan dusk tests/Browser/AuthenticationTest.php
```

*Note : En cas d'échec, des captures d'écran sont générées automatiquement dans `tests/Browser/screenshots/`.*

---

## 🛠 Outils de Développement

- **Telescope** : Vous pouvez inspecter les requêtes, exceptions et logs générés pendant les tests via la route `/telescope`.
- **Logs** : Les logs de tests se trouvent dans `storage/logs/laravel.log`.

---

## 📝 Bonnes Pratiques

- **Base de données** : Les tests utilisent généralement une base de données en mémoire ou une base de test dédiée. Assurez-vous que vos migrations sont à jour.
- **Mocks** : Pour les appels aux APIs tierces (TMDB, RapidAPI), nous utilisons des Mocks ou du Cache pour éviter de consommer des crédits API réels. Voir `tests/Feature/ApiCacheTest.php` pour un exemple.
