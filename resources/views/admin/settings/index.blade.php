<x-app-layout>
    <div class="flex min-h-screen bg-slate-900">
        <x-admin-sidebar />
        <div class="flex-1">
            <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10 overflow-hidden">
                <div class="px-3 sm:px-6 lg:px-8 py-4">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                        <span class="text-3xl sm:text-4xl">⚙️</span>
                        <span>{{ __('admin.settings_title') }}</span>
                    </h1>
                    <p class="text-slate-400 mt-1">{{ __('admin.settings_subtitle') }}</p>
                </div>
            </div>
            <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">
        <div class="w-full max-w-2xl mx-auto">
            @if (session('success'))
                <div class="mb-4 bg-green-900/30 border border-green-600 text-green-200 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf

                        @foreach($settings as $group => $groupSettings)
                            <div class="mb-8">
                                <h3 class="font-semibold text-lg text-white mb-4">{{ ucfirst($group) }}</h3>

                                @foreach($groupSettings as $setting)
                                    <div class="mb-4">
                                        <label for="{{ $setting->key }}" class="block text-slate-200 font-semibold mb-2">
                                            {{ $setting->label ?? ucwords(str_replace('_', ' ', $setting->key)) }}
                                        </label>

                                        @if($setting->key === 'api_source_priority')
                                            <!-- Select Dropdown for API Source Priority -->
                                            <select name="{{ $setting->key }}" id="{{ $setting->key }}" class="w-full px-4 py-2 border border-slate-600 rounded focus:outline-none focus:border-blue-500 bg-slate-700 text-white">
                                                <option value="env_first" {{ old($setting->key, $setting->value) === 'env_first' ? 'selected' : '' }}>
                                                    {{ __('admin.settings_env_first') }}
                                                </option>
                                                <option value="db_first" {{ old($setting->key, $setting->value) === 'db_first' ? 'selected' : '' }}>
                                                    {{ __('admin.settings_db_first') }}
                                                </option>
                                            </select>
                                            <p class="text-xs text-slate-400 mt-1">
                                                {{ __('admin.settings_api_source_hint') }}
                                            </p>
                                        @else
                                            <!-- Text Input for other settings -->
                                            <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}" class="w-full px-4 py-2 border border-slate-600 rounded focus:outline-none focus:border-blue-500 bg-slate-700 text-white">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        <div class="flex gap-4">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ __('admin.settings_save') }}</button>
                            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-slate-600 text-white rounded hover:bg-slate-700">{{ __('common.back') }}</a>
                        </div>
                    </form>
                </div>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
