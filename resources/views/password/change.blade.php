<x-app-layout>
    <div class="min-h-screen bg-slate-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md mx-auto">
            <!-- Header -->
            <div class="mb-8 text-center">
                <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">{{ __('auth.password_reset_title') }}</h1>
                @if($mustChange)
                    <p class="text-red-400 text-sm font-semibold">{{ __('auth.password_must_change_warning') }}</p>
                @else
                    <p class="text-slate-400">{{ __('auth.password_change_subtitle') }}</p>
                @endif
            </div>

            <!-- Form Card -->
            <div class="bg-slate-800 rounded-lg shadow-lg p-6 border border-slate-700">
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-900/30 border border-red-600/50 rounded-lg">
                        <p class="text-red-400 font-semibold text-sm mb-2">{{ __('auth.errors_title') }}</p>
                        <ul class="text-red-300 text-xs space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('change-password.update') }}" class="space-y-4">
                    @csrf

                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-white font-semibold mb-2 text-sm">{{ __('auth.field_current') }}</label>
                        <input
                            type="password"
                            name="current_password"
                            id="current_password"
                            placeholder="{{ __('auth.placeholder_current') }}"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
                            required
                        >
                        @error('current_password')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-white font-semibold mb-2 text-sm">{{ __('auth.field_new') }}</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="{{ __('auth.placeholder_new') }}"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
                            required
                        >
                        <p class="text-slate-400 text-xs mt-1">
                            {{ __('auth.hint_length') }}<br>
                            {{ __('auth.hint_case') }}<br>
                            {{ __('auth.hint_symbol') }}
                        </p>
                        @error('password')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-white font-semibold mb-2 text-sm">{{ __('auth.field_confirm') }}</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            placeholder="{{ __('auth.placeholder_confirm') }}"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
                            required
                        >
                        @error('password_confirmation')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full mt-6 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition text-sm"
                    >
                        {{ __('auth.submit_btn') }}
                    </button>
                </form>

                @if(!$mustChange)
                    <div class="mt-4 text-center">
                        <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white text-xs transition">
                            {{ __('auth.back_to_dashboard') }}
                        </a>
                    </div>
                @endif
            </div>

            @if($mustChange)
                <p class="text-slate-500 text-center text-xs mt-4">
                    {{ __('auth.password_must_change_note') }}
                </p>
            @endif
        </div>
    </div>
</x-app-layout>