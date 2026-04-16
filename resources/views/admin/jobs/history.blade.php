<x-app-layout>
    <div class="flex min-h-screen bg-slate-900">
        <x-admin-sidebar />

        <div class="flex-1">
            <!-- Sticky header -->
            <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10">
                <div class="px-3 sm:px-6 lg:px-8 py-4">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                        <span class="text-3xl sm:text-4xl">📜</span>
                        <span>{{ __('admin.jobs_history_title') }}</span>
                    </h1>
                    <p class="text-slate-400 mt-1">{{ __('admin.jobs_history_subtitle') }}</p>
                </div>
            </div>

            <!-- Content -->
            <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">
                <div class="w-full max-w-6xl mx-auto">
                    @if ($jobHistory->count() > 0)
                        <!-- Desktop table -->
                        <div class="hidden md:block bg-slate-800 rounded-lg border border-slate-700 overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-900 border-b border-slate-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-300">Job</th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-300">Status</th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-300">Duration</th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-300">Output</th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-300">{{ __('admin.jobs_completed_at') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700">
                                    @foreach ($jobHistory as $record)
                                        <tr class="hover:bg-slate-700/50 transition">
                                            <td class="px-6 py-4 text-slate-200 font-mono text-xs">
                                                {{ class_basename($record->job_class) }}
                                            </td>
                                            <td class="px-6 py-4">
                                                @if ($record->status === 'completed')
                                                    <span class="inline-block px-3 py-1 bg-green-900/50 border border-green-500/50 text-green-300 text-xs rounded-full">
                                                        ✓ {{ __('admin.jobs_status_completed') }}
                                                    </span>
                                                @else
                                                    <span class="inline-block px-3 py-1 bg-red-900/50 border border-red-500/50 text-red-300 text-xs rounded-full">
                                                        ✗ {{ __('admin.jobs_status_failed') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-slate-400 text-xs">
                                                @if ($record->duration_seconds)
                                                    {{ $record->duration_seconds }}s
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-slate-400 text-xs max-w-xs truncate">
                                                @if ($record->status === 'completed' && $record->output)
                                                    <span title="{{ $record->output }}">{{ $record->output }}</span>
                                                @elseif ($record->status === 'failed' && $record->exception)
                                                    <span title="{{ $record->exception }}" class="text-red-400">
                                                        {{ substr($record->exception, 0, 50) }}...
                                                    </span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-slate-400 text-xs whitespace-nowrap">
                                                {{ $record->completed_at?->format('Y-m-d H:i:s') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile list -->
                        <div class="md:hidden space-y-3">
                            @foreach ($jobHistory as $record)
                                <div class="bg-slate-800 rounded-lg border border-slate-700 p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="font-semibold text-white">{{ class_basename($record->job_class) }}</div>
                                        @if ($record->status === 'completed')
                                            <span class="inline-block px-2 py-1 bg-green-900/50 border border-green-500/50 text-green-300 text-xs rounded">
                                                ✓ Done
                                            </span>
                                        @else
                                            <span class="inline-block px-2 py-1 bg-red-900/50 border border-red-500/50 text-red-300 text-xs rounded">
                                                ✗ Failed
                                            </span>
                                        @endif
                                    </div>
                                    <div class="space-y-1 text-sm text-slate-400">
                                        <div><span class="text-slate-500">Duration:</span>
                                            @if ($record->duration_seconds)
                                                {{ $record->duration_seconds }}s
                                            @else
                                                —
                                            @endif
                                        </div>
                                        <div><span class="text-slate-500">Completed:</span>
                                            {{ $record->completed_at?->format('Y-m-d H:i:s') }}
                                        </div>
                                        @if ($record->output)
                                            <div class="text-xs text-slate-500 pt-2 border-t border-slate-700 mt-2">{{ $record->output }}</div>
                                        @endif
                                        @if ($record->metadata)
                                            <div class="text-xs text-cyan-400 pt-2">
                                                @foreach ($record->metadata as $key => $value)
                                                    <div>{{ ucfirst(str_replace('_', ' ', $key)) }}: <span class="text-yellow-400">{{ $value }}</span></div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $jobHistory->links() }}
                        </div>
                    @else
                        <div class="bg-slate-800 rounded-lg border border-slate-700 p-12 text-center text-slate-400">
                            <p class="text-lg">📜 {{ __('admin.jobs_no_history') }}</p>
                            <p class="text-sm mt-2">{{ __('admin.jobs_history_hint') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
