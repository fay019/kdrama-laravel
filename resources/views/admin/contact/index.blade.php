@extends('layouts.app')

@section('title', __('admin.contact_title') . ' - Admin')

@section('content')
<div class="flex min-h-screen bg-slate-900">
    <x-admin-sidebar />
    <div class="flex-1">
        <!-- Header -->
        <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10 overflow-hidden">
            <div class="px-3 sm:px-6 lg:px-8 py-4">
                <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                    <span class="text-3xl sm:text-4xl">📧</span>
                    <span class="truncate">{{ __('admin.contact_title') }}</span>
                </h1>
                <p class="text-slate-400 mt-1 text-sm">{{ __('admin.contact_subtitle') }}</p>
            </div>
        </div>
        <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">

<!-- Stats -->
<div class="max-w-7xl mx-auto mb-6">
    <div class="grid grid-cols-2 md:grid-cols-6 gap-2 sm:gap-4">
        <div class="card-dark p-2 sm:p-4 text-center">
            <div class="text-2xl font-bold text-white">{{ $totalMessages }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ __('admin.contact_stats_total') }}</div>
        </div>

        <div class="card-dark p-4 text-center">
            <div class="text-2xl font-bold text-yellow-400">{{ $pendingCount }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ __('admin.contact_stats_pending') }}</div>
        </div>

        <div class="card-dark p-4 text-center">
            <div class="text-2xl font-bold text-blue-400">{{ $readCount }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ __('admin.contact_stats_read') }}</div>
        </div>

        <div class="card-dark p-4 text-center">
            <div class="text-2xl font-bold text-green-400">{{ $resolvedCount }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ __('admin.contact_stats_resolved') }}</div>
        </div>

        <div class="card-dark p-4 text-center">
            <div class="text-2xl font-bold text-red-400">{{ $spamCount }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ __('admin.contact_stats_spam') }}</div>
        </div>

        <div class="card-dark p-4 text-center">
            <div class="text-2xl font-bold text-red-500">{{ $emailFailedCount }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ __('admin.contact_stats_failed') }}</div>
        </div>
    </div>
</div>

<!-- Filters & Search -->
<div class="max-w-7xl mx-auto mb-6">
    <div class="card-dark p-3 sm:p-4">
        <form action="{{ route('admin.contact.index') }}" method="GET" class="flex flex-col gap-2">
            <!-- Search Input -->
            <input
                type="text"
                name="search"
                value="{{ $searchQuery }}"
                placeholder="{{ __('admin.contact_search') }}"
                class="w-full px-2 sm:px-3 py-1 sm:py-2 bg-slate-900 border border-slate-700 rounded text-white placeholder-slate-500 focus:border-red-500 focus:ring-1 focus:ring-red-500 transition text-xs sm:text-sm"
            >

            <!-- Select + Buttons Row -->
            <div class="flex gap-1 sm:gap-2">
                <select
                    name="status"
                    class="flex-1 px-2 sm:px-3 py-1 sm:py-2 bg-slate-900 border border-slate-700 rounded text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 transition text-xs sm:text-sm"
                >
                    <option value="">{{ __('admin.contact_filter_all') }}</option>
                    <option value="pending" @selected($currentStatus === 'pending')>{{ __('admin.contact_filter_pending') }}</option>
                    <option value="read" @selected($currentStatus === 'read')>{{ __('admin.contact_filter_read') }}</option>
                    <option value="resolved" @selected($currentStatus === 'resolved')>{{ __('admin.contact_filter_resolved') }}</option>
                    <option value="spam" @selected($currentStatus === 'spam')>{{ __('admin.contact_filter_spam') }}</option>
                </select>

                <button type="submit" class="btn-primary px-2 sm:px-4 py-1 sm:py-2 text-xs sm:text-sm whitespace-nowrap">
                    {{ __('admin.contact_button_search') }}
                </button>

                @if($searchQuery || $currentStatus)
                    <a href="{{ route('admin.contact.index') }}" class="px-2 sm:px-3 py-1 sm:py-2 bg-slate-700 hover:bg-slate-600 text-white rounded text-center text-xs sm:text-sm whitespace-nowrap">
                        {{ __('admin.contact_button_clear') }}
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Messages List -->
<div class="max-w-7xl mx-auto mb-8">
    @if($messages->count() > 0)
        <div class="space-y-4">
            @foreach($messages as $message)
                <div class="card-dark p-2 sm:p-3 hover:bg-slate-700/50 transition border-l-4 @if($message->status === 'pending') border-yellow-400 @elseif($message->status === 'read') border-blue-400 @elseif($message->status === 'resolved') border-green-400 @else border-red-400 @endif">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex gap-1 mb-1 flex-wrap items-center">
                                <h3 class="font-bold text-white truncate text-xs sm:text-sm">{{ Str::limit($message->name, 15) }}</h3>
                                <span class="text-xs whitespace-nowrap @if($message->status === 'pending') bg-yellow-900/30 text-yellow-300 @elseif($message->status === 'read') bg-blue-900/30 text-blue-300 @elseif($message->status === 'in_progress') bg-purple-900/30 text-purple-300 @elseif($message->status === 'resolved') bg-green-900/30 text-green-300 @else bg-red-900/30 text-red-300 @endif px-1 py-0.5 rounded">
                                    @if($message->status === 'pending') ⏳ @elseif($message->status === 'read') 👀 @elseif($message->status === 'in_progress') 🔧 @elseif($message->status === 'resolved') ✅ @else 🚫 @endif
                                </span>
                            </div>

                            <p class="text-xs text-slate-400 line-clamp-1">{{ Str::limit($message->email, 20) }}</p>
                            <p class="text-xs text-slate-400 line-clamp-1">{{ Str::limit($message->subject, 25) }}</p>
                            <p class="text-xs text-slate-300 line-clamp-1">{{ Str::limit($message->message, 40) }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $message->created_at->format('d/m H:i') }} @if($message->attachment_path)📎@endif</p>
                        </div>

                        <div class="flex gap-2 sm:gap-3">
                            <a
                                href="{{ route('admin.contact.show', $message->id) }}"
                                class="px-2 py-1 sm:px-4 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm rounded-lg transition whitespace-nowrap"
                                title="{{ __('admin.contact_button_view') }}"
                            >
                                <span class="hidden sm:inline">{{ __('admin.contact_button_view') }}</span>
                                <span class="sm:hidden">{{ __('admin.contact_button_view_short') }}</span>
                            </a>

                            <form action="{{ route('admin.contact.destroy', $message->id) }}" method="POST" onsubmit="return confirm('{{ __('admin.contact_confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 sm:px-4 sm:py-2 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm rounded-lg transition whitespace-nowrap" title="{{ __('admin.contact_button_delete') }}">
                                    <span class="hidden sm:inline">{{ __('admin.contact_button_delete') }}</span>
                                    <span class="sm:hidden">{{ __('admin.contact_button_delete_short') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $messages->links('pagination::tailwind') }}
        </div>
    @else
        <div class="card-dark py-16 text-center">
            <div class="text-4xl mb-4">📭</div>
            <p class="text-slate-400 text-lg">
                {{ __('admin.contact_no_found') }}
            </p>
        </div>
    @endif
        </div>
    </div>
</div>
@endsection
