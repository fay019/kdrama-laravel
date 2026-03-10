<x-app-layout>
    <div class="flex min-h-screen bg-slate-900">
        <!-- Sidebar -->
        <x-admin-sidebar />

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10 overflow-hidden">
                <div class="px-3 sm:px-6 lg:px-8 py-4">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                        <span class="text-3xl sm:text-4xl">👥</span>
                        <span>{{ __('admin.users_title') }}</span>
                    </h1>
                    <p class="text-slate-400 mt-1">{{ __('admin.users_subtitle') }}</p>
                </div>
            </div>

            <!-- Page Content -->
            <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">
        <div class="w-full max-w-6xl mx-auto">
            @if (session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Desktop Table View -->
            <div class="hidden md:block card p-6">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="border-b border-slate-700">
                            <th class="px-4 py-2 text-left text-white font-bold">{{ __('admin.users_table_id') }}</th>
                            <th class="px-4 py-2 text-left text-white font-bold">{{ __('admin.users_table_name') }}</th>
                            <th class="px-4 py-2 text-left text-white font-bold">{{ __('admin.users_table_email') }}</th>
                            <th class="px-4 py-2 text-left text-white font-bold">{{ __('admin.users_table_admin') }}</th>
                            <th class="px-4 py-2 text-left text-white font-bold">{{ __('admin.users_table_export') }}</th>
                            <th class="px-4 py-2 text-left text-white font-bold">{{ __('admin.users_table_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="border-b border-slate-700">
                                <td class="px-4 py-3 text-slate-200">{{ $user->id }}</td>
                                <td class="px-4 py-3 text-white">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-slate-200">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs font-bold {{ $user->is_admin ? 'bg-green-500/30 text-green-100 border border-green-500' : 'bg-slate-600/30 text-slate-200 border border-slate-600' }}">
                                        {{ $user->is_admin ? __('admin.users_admin_badge') : __('admin.users_user_badge') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 space-x-2">
                                    <button onclick="openAdminExportModal({{ $user->id }}, '{{ addslashes($user->name) }}')" class="text-purple-400 hover:text-purple-300 font-semibold text-xs" title="Export avec options">⚙️</button>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-400 hover:text-blue-300 font-semibold">{{ __('admin.users_edit') }}</a>
                                    @if(auth()->user()->id !== $user->id)
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300 ml-2 font-semibold" onclick="return confirm('{{ __('admin.users_confirm_delete') }}')">{{ __('admin.users_delete') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-center text-slate-400">{{ __('admin.users_no_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden space-y-4">
                @forelse($users as $user)
                    <div class="bg-slate-800 border border-slate-700 rounded-lg p-4 space-y-3">
                        <!-- User Info -->
                        <div>
                            <p class="text-xs text-slate-400">ID: {{ $user->id }}</p>
                            <p class="text-lg font-bold text-white">{{ $user->name }}</p>
                            <p class="text-sm text-slate-400">{{ $user->email }}</p>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            <span class="inline-block px-3 py-1 rounded text-xs font-bold {{ $user->is_admin ? 'bg-green-500/30 text-green-100 border border-green-500' : 'bg-slate-600/30 text-slate-200 border border-slate-600' }}">
                                {{ $user->is_admin ? __('admin.users_admin_badge') : __('admin.users_user_badge') }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 pt-2 border-t border-slate-700">
                            <button onclick="openAdminExportModal({{ $user->id }}, '{{ addslashes($user->name) }}')" class="flex-1 px-3 py-2 bg-purple-600/30 hover:bg-purple-600/50 text-purple-300 rounded font-semibold text-sm transition">
                                {{ __('admin.users_export') }}
                            </button>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="flex-1 px-3 py-2 bg-blue-600/30 hover:bg-blue-600/50 text-blue-300 rounded font-semibold text-sm transition text-center">
                                {{ __('admin.users_edit') }}
                            </a>
                            @if(auth()->user()->id !== $user->id)
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full px-3 py-2 bg-red-600/30 hover:bg-red-600/50 text-red-300 rounded font-semibold text-sm transition" onclick="return confirm('{{ __('admin.users_confirm_delete') }}')">
                                        {{ __('admin.users_delete') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400">
                        {{ __('admin.users_no_found') }}
                    </div>
                @endforelse
            </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Admin Export Modal -->
@include('admin.exports._admin-export-modal')
