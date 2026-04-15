<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('setup.page_title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-800">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md bg-slate-800 shadow-md rounded-lg p-8">
            <h1 class="text-2xl font-bold text-white mb-2">{{ __('setup.app_name') }}</h1>
            <p class="text-slate-300 mb-6">{{ __('setup.subtitle') }}</p>

            <div class="mb-6 p-4 bg-blue-900/30 border border-blue-600 rounded">
                <h3 class="font-semibold text-blue-200 mb-2">{{ __('setup.installation_steps') }}</h3>
                <ul class="text-sm text-blue-200 space-y-1">
                    <li>{{ __('setup.step_1') }}</li>
                    <li>{{ __('setup.step_2') }}</li>
                    <li>{{ __('setup.step_3') }}</li>
                </ul>
            </div>

            <div class="mb-6 p-4 bg-amber-900/30 border border-amber-600 rounded">
                <h3 class="font-semibold text-amber-200 mb-2">{{ __('setup.credentials_title') }}</h3>
                <div class="text-sm text-amber-200 space-y-1">
                    <p><strong>{{ __('setup.credentials_email') }}</strong> {{ __('setup.email_value') }}</p>
                    <p><strong>{{ __('setup.credentials_password') }}</strong> {{ __('setup.password_value') }}</p>
                </div>
            </div>

            <form action="{{ route('setup.process') }}" method="POST">
                @csrf
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    {{ __('setup.submit_button') }}
                </button>
            </form>

            <p class="text-xs text-white mt-4 text-center">
                {{ __('setup.footer_text') }}
            </p>
        </div>
    </div>
</body>
</html>
