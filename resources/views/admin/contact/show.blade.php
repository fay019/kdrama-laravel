@extends('layouts.app')

@section('title', __('admin.contact_show_back') . ' - Admin')

@section('content')
<div class="flex min-h-screen bg-slate-900">
    <!-- Sidebar -->
    <x-admin-sidebar />

    <!-- Main Content -->
    <div class="flex-1">
        <!-- Header -->
        <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10 overflow-hidden">
            <div class="px-3 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between mb-4">
                    <a href="{{ route('admin.contact.index') }}" class="text-red-400 hover:text-red-300 transition flex items-center gap-2 text-sm sm:text-base">
                        {{ __('admin.contact_show_back') }}
                    </a>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                    <span class="text-3xl sm:text-4xl">📧</span>
                    <span class="truncate">{{ $message->subject }}</span>
                </h1>
            </div>
        </div>

        <!-- Content -->
        <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
    @if(session('success'))
        <div class="bg-green-900/20 border border-green-600/50 rounded-lg p-4 mb-6">
            <p class="text-green-400 font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Sender Info -->
    <div class="card-dark p-6 mb-6">
        <h2 class="text-xl font-bold text-white mb-4">{{ __('admin.contact_show_sender_info') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-slate-400 text-sm mb-1">{{ __('admin.contact_show_name') }}</p>
                <p class="text-white font-semibold">{{ $message->name }}</p>
            </div>
            <div>
                <p class="text-slate-400 text-sm mb-1">{{ __('admin.contact_show_email') }}</p>
                <p class="text-white font-semibold">
                    <a href="mailto:{{ $message->email }}" class="text-red-400 hover:text-red-300">{{ $message->email }}</a>
                </p>
            </div>
            <div>
                <p class="text-slate-400 text-sm mb-1">{{ __('admin.contact_show_date') }}</p>
                <p class="text-white">{{ $message->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div>
                <p class="text-slate-400 text-sm mb-1">{{ __('admin.contact_show_email_status') }}</p>
                <p class="text-white">
                    @if($message->email_sent)
                        <span class="text-green-400">{{ __('admin.contact_show_email_sent') }}</span>
                    @else
                        <span class="text-red-400">{{ __('admin.contact_show_email_error') }}</span>
                        @if($message->error_message)
                            <p class="text-xs text-red-300 mt-1">{{ $message->error_message }}</p>
                        @endif
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Message -->
    <div class="card-dark p-6 mb-6">
        <h2 class="text-xl font-bold text-white mb-4">{{ __('admin.contact_show_message') }}</h2>
        <div class="bg-slate-900 p-4 rounded-lg">
            <p class="text-slate-200 whitespace-pre-wrap leading-relaxed">{{ $message->message }}</p>
        </div>
    </div>

    <!-- Attachment -->
    @if($message->attachment_path)
        <div class="card-dark p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">{{ __('admin.contact_show_attachment') }}</h2>
            <div class="flex items-center justify-between p-4 bg-slate-900 rounded-lg">
                <div>
                    <p class="text-white font-semibold">{{ $message->attachment_original_name }}</p>
                    <p class="text-slate-400 text-sm mt-1">
                        {{ number_format($message->attachment_size / 1024, 2) }} KB
                    </p>
                </div>
                <a href="{{ route('admin.contact.download-attachment', $message->id) }}" class="btn-primary">
                    {{ __('admin.contact_show_download') }}
                </a>
            </div>
        </div>
    @endif

    <!-- Status Management -->
    <div class="card-dark p-6 mb-6">
        <h2 class="text-xl font-bold text-white mb-4">{{ __('admin.contact_show_status_management') }}</h2>

        <div class="mb-6 p-4 bg-slate-900 rounded-lg">
            <p class="text-slate-400 mb-2 text-sm">{{ __('admin.contact_show_current_status') }}</p>
            <span class="inline-block px-4 py-2 rounded-lg @if($message->status === 'pending') bg-yellow-900/30 text-yellow-300 @elseif($message->status === 'read') bg-blue-900/30 text-blue-300 @elseif($message->status === 'in_progress') bg-purple-900/30 text-purple-300 @elseif($message->status === 'resolved') bg-green-900/30 text-green-300 @else bg-red-900/30 text-red-300 @endif font-semibold">
                @if($message->status === 'pending') {{ __('admin.contact_show_status_pending') }}
                @elseif($message->status === 'read') {{ __('admin.contact_show_status_read') }}
                @elseif($message->status === 'in_progress') {{ __('admin.contact_show_status_in_progress') }}
                @elseif($message->status === 'resolved') {{ __('admin.contact_show_status_resolved') }}
                @else {{ __('admin.contact_show_status_spam') }}
                @endif
            </span>
        </div>

        <!-- Workflow Chart -->
        <div class="mb-6 p-3 bg-slate-900/50 rounded-lg text-xs text-slate-400">
            <p class="mb-2">{{ __('admin.contact_show_workflow') }}</p>
            <p>{{ __('admin.contact_show_workflow_path') }}</p>
        </div>

        <!-- Available Actions -->
        <div class="space-y-2">
            @php
                $allowedTransitions = [
                    'pending' => [
                        'read' => __('admin.contact_show_action_read'),
                        'spam' => __('admin.contact_show_action_spam')
                    ],
                    'read' => [
                        'in_progress' => __('admin.contact_show_action_in_progress'),
                        'resolved' => __('admin.contact_show_action_resolved'),
                        'spam' => __('admin.contact_show_action_spam')
                    ],
                    'in_progress' => [
                        'resolved' => __('admin.contact_show_action_resolved'),
                        'spam' => __('admin.contact_show_action_spam')
                    ],
                    'resolved' => [],
                    'spam' => [
                        'pending' => __('admin.contact_show_action_unspam')
                    ]
                ];

                $currentTransitions = $allowedTransitions[$message->status] ?? [];
            @endphp

            @if(empty($currentTransitions))
                <div class="p-3 bg-green-900/20 border border-green-600/50 rounded-lg text-green-300 text-center text-sm">
                    {{ __('admin.contact_show_finished') }}
                </div>
            @else
                @foreach($currentTransitions as $status => $label)
                    <form action="{{ route('admin.contact.update-status', $message->id) }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="status" value="{{ $status }}">
                        <button
                            type="submit"
                            class="w-full px-4 py-3 text-left rounded-lg transition @if($status === 'in_progress') bg-purple-600 hover:bg-purple-700 @elseif($status === 'resolved') bg-green-600 hover:bg-green-700 @elseif($status === 'spam') bg-red-600 hover:bg-red-700 @else bg-blue-600 hover:bg-blue-700 @endif text-white font-semibold"
                        >
                            {{ $label }}
                        </button>
                    </form>
                @endforeach
            @endif
        </div>

        <!-- Timeline -->
        <div class="mt-6 pt-4 border-t border-slate-700 space-y-2">
            @if($message->read_at)
                <p class="text-xs text-slate-400">
                    {{ __('admin.contact_show_marked_read') }} <strong>{{ $message->read_at->format('d/m/Y à H:i') }}</strong>
                </p>
            @endif

            @if($message->resolved_at)
                <p class="text-xs text-slate-400">
                    {{ __('admin.contact_show_marked_resolved') }} <strong>{{ $message->resolved_at->format('d/m/Y à H:i') }}</strong>
                </p>
            @endif
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="card-dark border border-red-600/50 p-6">
        <h2 class="text-xl font-bold text-red-400 mb-4">{{ __('admin.contact_show_danger_zone') }}</h2>
        <p class="text-slate-400 mb-4">
            {{ __('admin.contact_show_delete_warning') }}
        </p>
        <form action="{{ route('admin.contact.destroy', $message->id) }}" method="POST" onsubmit="return confirm('{{ __('admin.contact_show_confirm_delete') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                {{ __('admin.contact_show_delete_button') }}
            </button>
        </form>
            </div>
        </div>
    </div>
</div>
@endsection
