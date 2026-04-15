<x-app-layout>
    <div class="flex min-h-screen bg-slate-900">
        <x-admin-sidebar />
        <div class="flex-1">
            <!-- Header -->
            <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10 overflow-hidden">
                <div class="px-3 sm:px-6 lg:px-8 py-4">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                        <span class="text-3xl sm:text-4xl">⚙️</span>
                        <span>{{ __('admin.settings_title') }}</span>
                    </h1>
                    <p class="text-slate-400 mt-1">{{ __('admin.settings_subtitle') }}</p>
                </div>
            </div>

            <!-- Content -->
            <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">
                <div class="w-full max-w-5xl mx-auto">
                    <!-- Success Message (Toast) -->
                    @if (session('success'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                showToast('{{ session('success') }}', 'success');
                            });
                        </script>
                    @endif

                    <!-- Add New Setting Button -->
                    <div class="mb-8">
                        <button type="button" onclick="openAddSettingModal()"
                            class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl flex items-center gap-2">
                            <span class="text-xl">{{ __('admin.settings_add_new') }}</span>
                        </button>
                    </div>

                    <!-- Update Existing Settings Form -->
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-8">
                        @csrf

                        @foreach($settings as $group => $groupSettings)
                            @php
                                // Sort settings by order within each group
                                $groupSettings = collect($groupSettings)->sortBy('order');
                            @endphp
                            <!-- Group Card -->
                            <div class="bg-slate-800/50 border border-slate-700 rounded-lg overflow-hidden hover:border-slate-600 transition">
                                <!-- Group Header -->
                                <div class="bg-gradient-to-r from-slate-800 to-slate-700 border-b border-slate-700 px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($group === 'site')
                                            <span class="text-2xl">🌐</span>
                                        @elseif($group === 'api_tmdb')
                                            <span class="text-2xl">🎬</span>
                                        @elseif($group === 'api_streaming')
                                            <span class="text-2xl">🔄</span>
                                        @elseif($group === 'api_watchmode')
                                            <span class="text-2xl">⏱️</span>
                                        @elseif($group === 'api_general')
                                            <span class="text-2xl">🔌</span>
                                        @else
                                            <span class="text-2xl">⚙️</span>
                                        @endif
                                        <div>
                                            <h3 class="font-bold text-lg text-white">
                                                @switch($group)
                                                    @case('site')
                                                        {{ __('admin.settings_group_site') }}
                                                    @break
                                                    @case('api_tmdb')
                                                        {{ __('admin.settings_group_tmdb') }}
                                                    @break
                                                    @case('api_streaming')
                                                        {{ __('admin.settings_group_streaming') }}
                                                    @break
                                                    @case('api_watchmode')
                                                        {{ __('admin.settings_group_watchmode') }}
                                                    @break
                                                    @case('api_general')
                                                        {{ __('admin.settings_group_general') }}
                                                    @break
                                                    @default
                                                        {{ ucfirst($group) }}
                                                @endswitch
                                            </h3>
                                            <p class="text-xs text-slate-400 mt-1">
                                                @switch($group)
                                                    @case('site')
                                                        {{ __('admin.settings_group_site_desc') }}
                                                    @break
                                                    @case('api_tmdb')
                                                        {{ __('admin.settings_group_tmdb_desc') }}
                                                    @break
                                                    @case('api_streaming')
                                                        {{ __('admin.settings_group_streaming_desc') }}
                                                    @break
                                                    @case('api_watchmode')
                                                        {{ __('admin.settings_group_watchmode_desc') }}
                                                    @break
                                                    @case('api_general')
                                                        {{ __('admin.settings_group_general_desc') }}
                                                    @break
                                                    @default
                                                        {{ ucfirst($group) }} settings group
                                                @endswitch
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Group Fields -->
                                <div class="p-6 space-y-5">
                                    @foreach($groupSettings as $setting)
                                        <div class="pb-5 border-b border-slate-700/50 last:border-b-0 last:pb-0">
                                            <label for="{{ $setting->key }}" class="block text-slate-200 font-semibold mb-2 flex items-center justify-between">
                                                <span>{{ $setting->label ?? ucwords(str_replace('_', ' ', $setting->key)) }}</span>
                                                <div class="flex items-center gap-2">
                                                    @if($setting->is_sensitive)
                                                        <span class="text-xs font-normal text-yellow-400 bg-yellow-900/30 px-2 py-1 rounded">🔐 Sensitive</span>
                                                    @endif

                                                    <!-- Move Up Button -->
                                                    @if($loop->index > 0)
                                                        <button type="button" onclick="moveSetting({{ $setting->id }}, 'up')" title="{{ __('admin.settings_move_up') }}" class="text-slate-400 hover:text-blue-400 transition text-sm">
                                                            ↑
                                                        </button>
                                                    @endif

                                                    <!-- Move Down Button -->
                                                    @if($loop->index < $groupSettings->count() - 1)
                                                        <button type="button" onclick="moveSetting({{ $setting->id }}, 'down')" title="{{ __('admin.settings_move_down') }}" class="text-slate-400 hover:text-blue-400 transition text-sm">
                                                            ↓
                                                        </button>
                                                    @endif

                                                    <!-- Edit Button -->
                                                    <button type="button" onclick="openEditModal({{ $setting->id }}, '{{ $setting->label }}', {{ json_encode($setting->value) }}, {{ $setting->is_sensitive ? 'true' : 'false' }})"
                                                        title="{{ __('admin.settings_edit_btn') }}" class="text-slate-400 hover:text-green-400 transition text-sm">
                                                        ✏️
                                                    </button>

                                                    <!-- Delete Button -->
                                                    @if($setting->is_deletable)
                                                        <button type="button" onclick="openDeleteModal({{ $setting->id }}, '{{ addslashes($setting->label) }}')"
                                                            title="{{ __('admin.settings_delete_btn') }}" class="text-slate-400 hover:text-red-400 transition text-sm">
                                                            🗑️
                                                        </button>
                                                    @endif
                                                </div>
                                            </label>

                                            @if($setting->key === 'api_source_priority')
                                                <!-- Select: API Source Priority -->
                                                <select name="{{ $setting->key }}" id="{{ $setting->key }}" class="w-full px-4 py-3 border border-slate-600 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 bg-slate-700 text-white transition">
                                                    <option value="env_first" {{ old($setting->key, $setting->value) === 'env_first' ? 'selected' : '' }}>
                                                        {{ __('admin.settings_env_first') }}
                                                    </option>
                                                    <option value="db_first" {{ old($setting->key, $setting->value) === 'db_first' ? 'selected' : '' }}>
                                                        {{ __('admin.settings_db_first') }}
                                                    </option>
                                                </select>
                                                <p class="text-xs text-slate-400 mt-2">{{ __('admin.settings_api_source_hint') }}</p>

                                            @elseif($setting->key === 'streaming_provider_priority')
                                                <!-- Select: Streaming Provider Priority -->
                                                <select name="{{ $setting->key }}" id="{{ $setting->key }}" class="w-full px-4 py-3 border border-slate-600 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 bg-slate-700 text-white transition">
                                                    <option value="rapidapi" {{ old($setting->key, $setting->value) === 'rapidapi' ? 'selected' : '' }}>
                                                        {{ __('admin.settings_provider_rapidapi') }}
                                                    </option>
                                                    <option value="watchmode" {{ old($setting->key, $setting->value) === 'watchmode' ? 'selected' : '' }}>
                                                        {{ __('admin.settings_provider_watchmode') }}
                                                    </option>
                                                </select>
                                                <p class="text-xs text-slate-400 mt-2">{{ __('admin.settings_provider_hint') }}</p>

                                            @elseif($setting->key === 'watchmode_enabled')
                                                <!-- Toggle: Enable Watchmode -->
                                                <div class="flex items-center gap-3">
                                                    <input type="hidden" name="{{ $setting->key }}" value="false">
                                                    <input type="checkbox" name="{{ $setting->key }}" id="{{ $setting->key }}" value="true"
                                                        {{ old($setting->key, $setting->value) === 'true' || old($setting->key, $setting->value) === '1' ? 'checked' : '' }}
                                                        class="w-6 h-6 rounded border-slate-600 text-blue-600 focus:ring-2 focus:ring-blue-500/20">
                                                    <label for="{{ $setting->key }}" class="text-slate-300 font-medium cursor-pointer">
                                                        {{ __('admin.settings_watchmode_enable_label') }}
                                                    </label>
                                                </div>
                                                <p class="text-xs text-slate-400 mt-2">{{ __('admin.settings_watchmode_enable_hint') }}</p>

                                            @else
                                                @php
                                                    $isApiKey = $setting->is_sensitive;
                                                    $displayValue = $isApiKey ? maskApiKey(old($setting->key, $setting->value)) : old($setting->key, $setting->value);
                                                @endphp

                                                <!-- Text Input (with API key masking) -->
                                                <div class="relative">
                                                    @if($isApiKey)
                                                        <!-- Hidden input for actual value -->
                                                        <input type="hidden" id="{{ $setting->key }}_actual" value="{{ old($setting->key, $setting->value) }}">

                                                        <!-- Display input (masked by default) -->
                                                        <input
                                                            type="text"
                                                            id="{{ $setting->key }}_display"
                                                            value="{{ $displayValue }}"
                                                            placeholder="••••••••••••••••"
                                                            class="w-full px-4 py-3 border border-slate-600 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 bg-slate-700 text-white placeholder-slate-500 transition pr-10"
                                                            readonly>

                                                        <!-- Actual input (hidden) -->
                                                        <input
                                                            type="hidden"
                                                            name="{{ $setting->key }}"
                                                            id="{{ $setting->key }}"
                                                            value="{{ old($setting->key, $setting->value) }}">

                                                        <!-- Toggle visibility button -->
                                                        <button type="button" onclick="toggleApiKeyVisibility('{{ $setting->key }}')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-200 transition">
                                                            <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <input
                                                            type="text"
                                                            name="{{ $setting->key }}"
                                                            id="{{ $setting->key }}"
                                                            value="{{ old($setting->key, $setting->value) }}"
                                                            placeholder="{{ $setting->label ?? ucwords(str_replace('_', ' ', $setting->key)) }}"
                                                            class="w-full px-4 py-3 border border-slate-600 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 bg-slate-700 text-white placeholder-slate-500 transition">
                                                    @endif
                                                </div>

                                                <!-- Help text for cache settings -->
                                                @if(str_contains($setting->key, 'cache'))
                                                    <p class="text-xs text-slate-400 mt-2">{{ __('admin.settings_cache_hint') }}</p>
                                                @endif
                                            @endif

                                            @error($setting->key)
                                                <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <!-- Action Buttons -->
                        <div class="flex gap-4 pt-4 border-t border-slate-700">
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl">
                                ✅ {{ __('admin.settings_save') }}
                            </button>
                            <a href="{{ route('admin.dashboard') }}" class="px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg transition">
                                {{ __('common.back') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Setting Modal -->
    <div id="addSettingModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-lg max-w-2xl w-full border border-slate-700 shadow-2xl">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-700 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">➕</span>
                    <div>
                        <h2 class="font-bold text-lg text-white">{{ __('admin.settings_add_new_title') }}</h2>
                        <p class="text-xs text-slate-400 mt-1">{{ __('admin.settings_add_new_description') }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddSettingModal()" class="text-slate-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form action="{{ route('admin.settings.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Key -->
                    <div>
                        <label for="modal_key" class="block text-slate-200 font-semibold mb-2">{{ __('admin.settings_add_key_label') }}</label>
                        <input type="text" name="key" id="modal_key" placeholder="{{ __('admin.settings_add_key_placeholder') }}"
                            value="{{ old('key') }}"
                            class="w-full px-4 py-3 border border-slate-600 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 bg-slate-700 text-white transition"
                            required>
                        @error('key')
                            <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Group -->
                    <div>
                        <label for="modal_group" class="block text-slate-200 font-semibold mb-2">{{ __('admin.settings_add_group_label') }}</label>
                        <input type="text" name="group" id="modal_group" list="groups_list" placeholder="{{ __('admin.settings_add_group_placeholder') }}"
                            value="{{ old('group') }}"
                            class="w-full px-4 py-3 border border-slate-600 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 bg-slate-700 text-white placeholder-slate-500 transition"
                            required>
                        <datalist id="groups_list">
                            @php
                                $existingGroups = $settings->keys()->unique()->sort();
                            @endphp
                            @foreach($existingGroups as $existingGroup)
                                <option value="{{ $existingGroup }}">{{ ucfirst($existingGroup) }}</option>
                            @endforeach
                        </datalist>
                        <p class="text-xs text-slate-400 mt-1">{{ __('admin.settings_add_group_hint') }}</p>
                        @error('group')
                            <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Label -->
                    <div class="md:col-span-2">
                        <label for="modal_label" class="block text-slate-200 font-semibold mb-2">{{ __('admin.settings_add_label_label') }}</label>
                        <input type="text" name="label" id="modal_label" placeholder="{{ __('admin.settings_add_label_placeholder') }}"
                            value="{{ old('label') }}"
                            class="w-full px-4 py-3 border border-slate-600 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 bg-slate-700 text-white transition"
                            required>
                        @error('label')
                            <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Value -->
                    <div class="md:col-span-2">
                        <label for="modal_value" class="block text-slate-200 font-semibold mb-2">{{ __('admin.settings_add_value_label') }}</label>
                        <textarea name="value" id="modal_value" rows="3" placeholder="{{ __('admin.settings_add_value_placeholder') }}"
                            class="w-full px-4 py-3 border border-slate-600 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 bg-slate-700 text-white placeholder-slate-500 transition">{{ old('value') }}</textarea>
                        @error('value')
                            <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sensitive Toggle -->
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative inline-block">
                                <input type="checkbox" name="is_sensitive" value="true" id="modal_is_sensitive" {{ old('is_sensitive') ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-600 peer-checked:bg-blue-600 rounded-full transition peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:h-5 after:w-5 after:rounded-full after:transition"></div>
                            </div>
                            <span class="text-slate-300 font-medium">{{ __('admin.settings_add_sensitive') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex gap-3 pt-4 border-t border-slate-700">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl">
                        {{ __('admin.settings_add_btn') }}
                    </button>
                    <button type="button" onclick="closeAddSettingModal()" class="px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg transition">
                        {{ __('admin.settings_add_close') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Setting Modal -->
    <div id="editSettingModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-lg max-w-2xl w-full border border-slate-700 shadow-2xl">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-700 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-lg text-white">{{ __('admin.settings_edit_btn') }}</h2>
                <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="editSettingForm" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="space-y-4">
                    <!-- Label -->
                    <div>
                        <label for="edit_label" class="block text-slate-200 font-semibold mb-2">{{ __('admin.settings_add_label_label') }}</label>
                        <input type="text" name="label" id="edit_label"
                            class="w-full px-4 py-3 border border-slate-600 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 bg-slate-700 text-white transition"
                            required>
                    </div>

                    <!-- Value -->
                    <div>
                        <label for="edit_value" class="block text-slate-200 font-semibold mb-2">{{ __('admin.settings_add_value_label') }}</label>
                        <textarea name="value" id="edit_value" rows="3"
                            class="w-full px-4 py-3 border border-slate-600 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 bg-slate-700 text-white placeholder-slate-500 transition"></textarea>
                    </div>

                    <!-- Sensitive Toggle -->
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative inline-block">
                                <input type="checkbox" name="is_sensitive" value="true" id="edit_is_sensitive" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-600 peer-checked:bg-blue-600 rounded-full transition peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:h-5 after:w-5 after:rounded-full after:transition"></div>
                            </div>
                            <span class="text-slate-300 font-medium">{{ __('admin.settings_add_sensitive') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex gap-3 pt-4 border-t border-slate-700">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl">
                        ✅ {{ __('common.save') }}
                    </button>
                    <button type="button" onclick="closeEditModal()" class="px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg transition">
                        {{ __('admin.settings_add_close') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Setting Confirmation Modal -->
    <div id="deleteSettingModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-lg max-w-md w-full border border-slate-700 shadow-2xl">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-700 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-lg text-white">⚠️ {{ __('admin.settings_delete_btn') }}</h2>
                <button type="button" onclick="closeDeleteModal()" class="text-slate-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <p class="text-slate-300 mb-6">
                    {{ __('admin.settings_confirm_delete') }}
                    <span id="delete_setting_label" class="font-bold text-white block mt-2 text-center text-lg"></span>
                </p>

                <!-- Modal Footer -->
                <form id="deleteSettingForm" method="POST" class="flex gap-3">
                    @csrf
                    <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl">
                        {{ __('admin.settings_delete_btn') }}
                    </button>
                    <button type="button" onclick="closeDeleteModal()" class="flex-1 px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg transition">
                        {{ __('common.cancel') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showToast(message, type = 'success') {
            // Remove existing toast
            const existingToast = document.querySelector('.toast');
            if (existingToast) {
                existingToast.remove();
            }

            const toast = document.createElement('div');
            toast.className = `toast fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-semibold shadow-lg z-50`;

            if (type === 'success') {
                toast.classList.add('bg-green-600');
            } else if (type === 'error') {
                toast.classList.add('bg-red-600');
            } else {
                toast.classList.add('bg-blue-600');
            }

            toast.textContent = message;
            document.body.appendChild(toast);

            // Auto-remove after 3 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function togglePassword(button) {
            const input = button.previousElementSibling;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            // Update icon
            const svg = button.querySelector('svg');
            svg.classList.toggle('opacity-50');
        }

        function openAddSettingModal() {
            document.getElementById('addSettingModal').classList.remove('hidden');
            document.getElementById('modal_key').focus();
        }

        function closeAddSettingModal() {
            document.getElementById('addSettingModal').classList.add('hidden');
        }

        function openEditModal(settingId, label, value, isSensitive = false) {
            const form = document.getElementById('editSettingForm');
            form.action = `/admin/settings/${settingId}/edit`;
            document.getElementById('edit_label').value = label;
            document.getElementById('edit_value').value = value;
            document.getElementById('edit_is_sensitive').checked = isSensitive;

            // Add data attribute to track visibility
            document.getElementById('edit_value').setAttribute('data-visible', 'false');

            document.getElementById('editSettingModal').classList.remove('hidden');
            document.getElementById('edit_label').focus();
        }

        function closeEditModal() {
            document.getElementById('editSettingModal').classList.add('hidden');
        }

        function openDeleteModal(settingId, label) {
            const form = document.getElementById('deleteSettingForm');
            form.action = `/admin/settings/${settingId}/delete`;
            document.getElementById('delete_setting_label').textContent = label;
            document.getElementById('deleteSettingModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteSettingModal').classList.add('hidden');
        }

        function moveSetting(settingId, direction) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                         document.querySelector('input[name="_token"]')?.value;

            fetch(`/admin/settings/${settingId}/move`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ direction: direction })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.moved) {
                    showToast('✅ {{ __("admin.settings_save") }}', 'success');
                    // Reload page after short delay to show updated order
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast('⚠️ {{ __("admin.settings_save") }}', 'info');
                }
            })
            .catch(error => {
                showToast('❌ {{ __("common.error") }}', 'error');
                console.error('Error:', error);
            });
        }

        function toggleApiKeyVisibility(settingKey) {
            const displayInput = document.getElementById(`${settingKey}_display`);
            const actualInput = document.getElementById(settingKey);
            const isVisible = displayInput.getAttribute('data-visible') === 'true';

            if (isVisible) {
                // Hide: show masked version
                const actualValue = actualInput.value;
                const length = actualValue.length;
                const lastFour = actualValue.substring(length - 4);
                const masked = '•'.repeat(Math.max(0, length - 4)) + lastFour;

                displayInput.value = masked;
                displayInput.setAttribute('data-visible', 'false');
            } else {
                // Show: show full value
                displayInput.value = actualInput.value;
                displayInput.setAttribute('data-visible', 'true');
            }
        }

        // Close modals on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAddSettingModal();
                closeEditModal();
                closeDeleteModal();
            }
        });

        // Close modals when clicking outside
        document.getElementById('addSettingModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'addSettingModal') {
                closeAddSettingModal();
            }
        });

        document.getElementById('editSettingModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'editSettingModal') {
                closeEditModal();
            }
        });

        // Auto-open add modal if there are validation errors
        document.addEventListener('DOMContentLoaded', () => {
            const addModal = document.getElementById('addSettingModal');
            const hasErrors = addModal?.querySelector('.text-red-400');

            if (hasErrors) {
                openAddSettingModal();
                showToast('❌ Erreurs de validation - corrigez-les', 'error');
            }
        });
    </script>

    <style>
        input[type="checkbox"]:focus {
            outline: none;
        }
    </style>
</x-app-layout>
