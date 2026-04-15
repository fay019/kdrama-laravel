<x-app-layout>
<div class="flex min-h-screen bg-slate-900">
    <x-admin-sidebar />
    <div class="flex-1">
        <!-- Header -->
        <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10 overflow-hidden">
            <div class="px-3 sm:px-6 lg:px-8 py-4">
                <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                    <span class="text-3xl sm:text-4xl">✏️</span>
                    <span class="truncate">{{ __('admin.user_edit_title') }} {{ $user->name }}</span>
                </h1>
                <p class="text-slate-400 mt-1 text-sm">{{ __('admin.user_edit_subtitle') }}</p>
            </div>
        </div>

        <!-- Page Content -->
        <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">
            <div class="w-full max-w-2xl mx-auto">
                <div class="bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="block text-slate-200 font-semibold mb-2 text-sm">{{ __('admin.user_edit_name_label') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full px-2 sm:px-4 py-1 sm:py-2 bg-slate-900 border border-slate-700 rounded text-white text-xs sm:text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                            @error('name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-slate-200 font-semibold mb-2 text-sm">{{ __('admin.user_edit_email_label') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full px-2 sm:px-4 py-1 sm:py-2 bg-slate-900 border border-slate-700 rounded text-white text-xs sm:text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                            @error('email')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="is_admin" class="flex items-center">
                                <input type="checkbox" name="is_admin" id="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }} class="mr-2 w-4 h-4 rounded border-slate-700">
                                <span class="text-slate-200 font-semibold text-sm">{{ __('admin.user_edit_admin_label') }}</span>
                            </label>
                        </div>

                        <div class="flex gap-2 sm:gap-4">
                            <button type="submit" class="flex-1 px-3 sm:px-4 py-1 sm:py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs sm:text-sm font-semibold transition">{{ __('admin.user_edit_save_btn') }}</button>
                            <a href="{{ route('admin.users.index') }}" class="flex-1 px-3 sm:px-4 py-1 sm:py-2 bg-slate-600 text-white rounded hover:bg-slate-700 text-center text-xs sm:text-sm font-semibold transition">{{ __('admin.user_edit_cancel_btn') }}</a>
                        </div>
                    </form>
                    </div>
                </div>

                <!-- Reset Password Card (Separate Form) -->
                <div class="mt-6 bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="mb-4 p-3 sm:p-4 bg-red-900/20 border border-red-600/50 rounded-lg">
                            <h3 class="text-red-400 font-semibold mb-2 text-sm">{{ __('admin.user_reset_password_title') }}</h3>
                            <p class="text-red-300 text-xs mb-3">{{ __('admin.user_reset_password_desc') }}</p>
                        </div>

                        <form id="resetPasswordForm" action="{{ route('admin.users.reset-password', $user->id) }}" method="POST">
                            @csrf
                            <button type="button" onclick="openResetPasswordModal()" class="w-full px-3 sm:px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-xs sm:text-sm font-semibold transition">
                                {{ __('admin.user_reset_password_btn') }}
                            </button>
                        </form>

                        <!-- Reset Password Confirmation Modal -->
                        <div id="resetPasswordModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                            <div class="bg-slate-800 rounded-lg shadow-xl border border-slate-700 max-w-sm w-full">
                                <div class="p-6">
                                    <!-- Header -->
                                    <h3 class="text-xl font-bold text-white mb-2">{{ __('admin.user_reset_password_modal_title') }}</h3>
                                    <p class="text-slate-400 text-sm mb-4">
                                        {{ __('admin.user_reset_password_modal_text') }} <span class="font-semibold text-white">{{ $user->name }}</span> ({{ $user->email }}).
                                    </p>

                                    <!-- Info Box -->
                                    <div class="bg-amber-900/30 border border-amber-600/50 rounded-lg p-3 mb-6">
                                        <p class="text-amber-300 text-xs">
                                            {{ __('admin.user_reset_password_modal_info') }}
                                        </p>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="flex gap-3">
                                        <button type="button" onclick="closeResetPasswordModal()" class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded font-semibold text-sm transition">
                                            {{ __('admin.user_reset_password_modal_cancel') }}
                                        </button>
                                        <button type="button" onclick="confirmResetPassword()" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-semibold text-sm transition">
                                            {{ __('admin.user_reset_password_modal_confirm') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

<script>
function openResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.remove('hidden');
}

function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
}

function confirmResetPassword() {
    closeResetPasswordModal();
    document.getElementById('resetPasswordForm').submit();
}
</script>
