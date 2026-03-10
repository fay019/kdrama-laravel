<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - KDrama Hub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-800">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md bg-slate-800 shadow-md rounded-lg p-8">
            <h1 class="text-2xl font-bold text-white mb-2">KDrama Hub</h1>
            <p class="text-slate-300 mb-6">Installation initiale de l'application</p>

            <div class="mb-6 p-4 bg-blue-900/30 border border-blue-600 rounded">
                <h3 class="font-semibold text-blue-200 mb-2">Étapes d'installation :</h3>
                <ul class="text-sm text-blue-200 space-y-1">
                    <li>✓ Création des tables de base de données</li>
                    <li>✓ Insertion des données par défaut</li>
                    <li>✓ Création de l'utilisateur administrateur</li>
                </ul>
            </div>

            <div class="mb-6 p-4 bg-amber-900/30 border border-amber-600 rounded">
                <h3 class="font-semibold text-amber-200 mb-2">Identifiants d'accès :</h3>
                <div class="text-sm text-amber-200 space-y-1">
                    <p><strong>Email :</strong> admin@kdrama.local</p>
                    <p><strong>Mot de passe :</strong> password</p>
                </div>
            </div>

            <form action="{{ route('setup.process') }}" method="POST">
                @csrf
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    Démarrer l'installation
                </button>
            </form>

            <p class="text-xs text-white mt-4 text-center">
                Cette page disparaîtra après l'installation complète.
            </p>
        </div>
    </div>
</body>
</html>
