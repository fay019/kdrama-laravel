<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('auth.password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-600 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-slate-300">{{ __('auth.remember_me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-slate-300 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-slate-900 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('auth.forgot_password') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('auth.log_in') }}
            </x-primary-button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="mt-6 text-center text-sm text-slate-400">
            {{ __('auth.no_account') }}
            <a class="underline text-slate-300 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-slate-900 focus:ring-indigo-500" href="{{ route('register') }}">
                {{ __('auth.register') }}
            </a>
        </div>
    @endif
</x-guest-layout>
