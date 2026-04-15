@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-900">
    <!-- Sidebar -->
    <x-admin-sidebar />

    <!-- Main Content -->
    <div class="flex-1">
        <!-- Header -->
        <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10 overflow-hidden">
            <div class="px-3 sm:px-6 lg:px-8 py-4">
                <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                    <span class="text-3xl sm:text-4xl">📊</span>
                    <span>{{ __('admin.exports_stats_title') }}</span>
                </h1>
                <p class="text-slate-400 mt-1">{{ __('admin.exports_stats_subtitle') }}</p>
            </div>
        </div>

        <!-- Page Content -->
        <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">
    <div class="w-full max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">{{ __('admin.exports_stats_header') }}</h1>
            <p class="text-slate-400">{{ __('admin.exports_stats_subtitle') }}</p>
        </div>

        <!-- Date Filter -->
        <div class="bg-slate-800 rounded-lg p-6 border border-slate-700 mb-8">
            <form method="GET" action="{{ route('admin.exports.stats') }}" class="flex items-center gap-4">
                <label for="days" class="text-slate-300 font-semibold">{{ __('admin.exports_stats_filter_label') }} <span id="daysLabel">30</span> {{ __('admin.exports_stats_filter_days') }}</label>
                <select name="days" id="days" class="bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600" onchange="document.getElementById('daysLabel').textContent = this.value">
                    <option value="7" @selected($days == 7)>7</option>
                    <option value="30" @selected($days == 30)>30</option>
                    <option value="90" @selected($days == 90)>90</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                    {{ __('admin.exports_stats_filter_btn') }}
                </button>
            </form>
        </div>

        <!-- Main Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
                <p class="text-slate-400 text-sm mb-2">{{ __('admin.exports_stats_total') }}</p>
                <p class="text-3xl font-bold text-white">{{ $totalExports }}</p>
                <p class="text-slate-500 text-xs mt-2">{{ __('admin.exports_stats_all_formats') }}</p>
            </div>
            <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
                <p class="text-slate-400 text-sm mb-2">{{ __('admin.exports_stats_formats') }}</p>
                <div class="space-y-1">
                    <p class="text-white">{{ __('admin.exports_stats_pdf') }} <span class="font-bold">{{ $pdfExports }}</span></p>
                    <p class="text-white">{{ __('admin.exports_stats_csv') }} <span class="font-bold">{{ $csvExports }}</span></p>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
                <p class="text-slate-400 text-sm mb-2">{{ __('admin.exports_stats_cache_title') }}</p>
                <div class="space-y-1">
                    <p class="text-white">{{ __('admin.exports_stats_cached') }} <span class="font-bold">{{ $cachedExports }}</span></p>
                    <p class="text-slate-300 text-xs">
                        ({{ $totalExports > 0 ? round(($cachedExports / $totalExports) * 100) : 0 }}%)
                    </p>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
                <p class="text-slate-400 text-sm mb-2">{{ __('admin.exports_stats_disk_space') }}</p>
                <p class="text-3xl font-bold text-white">{{ $totalDiskSizeMb }} MB</p>
            </div>
        </div>

        <!-- Performance Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
                <h3 class="text-lg font-semibold text-white mb-4">{{ __('admin.exports_stats_generation_time') }}</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-slate-400 text-sm">{{ __('admin.exports_stats_not_cached') }}</p>
                        <p class="text-2xl font-bold text-white">{{ $avgTimeNotCached }}ms</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-sm">{{ __('admin.exports_stats_cached_time') }}</p>
                        <p class="text-2xl font-bold text-green-400">{{ $avgTimeCached }}ms</p>
                    </div>
                    @if($avgTimeNotCached > 0 && $avgTimeCached > 0)
                        <div class="pt-2 border-t border-slate-700">
                            <p class="text-slate-300 text-xs">
                                {{ __('admin.exports_stats_cache_gain') }} <span class="font-bold">{{ round((($avgTimeNotCached - $avgTimeCached) / $avgTimeNotCached) * 100) }}%</span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
                <h3 class="text-lg font-semibold text-white mb-4">{{ __('admin.exports_stats_top_users') }}</h3>
                @if(count($topUsers) > 0)
                    <div class="space-y-3">
                        @foreach($topUsers as $index => $user)
                            <div class="flex items-center justify-between pb-3 @if(!$loop->last) border-b border-slate-700 @endif">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-purple-600 text-white text-xs font-bold">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <p class="text-white font-semibold">{{ $user->user->name ?? __('admin.exports_stats_deleted_user') }}</p>
                                        <p class="text-slate-400 text-xs">{{ $user->user->email ?? '' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-white font-bold">{{ $user->export_count }}</p>
                                    <p class="text-slate-400 text-xs">{{ __('admin.exports_stats_user_exports') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-400 text-sm">{{ __('admin.exports_stats_no_data') }}</p>
                @endif
            </div>
        </div>

        <!-- Daily Chart Data -->
        <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
            <h3 class="text-lg font-semibold text-white mb-6">{{ __('admin.exports_stats_per_day') }}</h3>
            @if(count($exportsPerDay) > 0)
                <div class="space-y-3">
                    @foreach($exportsPerDay as $day)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-24 text-slate-400 text-sm">
                                    {{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }}
                                </div>
                                <div class="flex-1 bg-slate-700 rounded-full h-8 flex items-center px-3">
                                    <div class="flex gap-1 flex-1">
                                        @if($day->pdf_count > 0)
                                            <div class="h-full bg-blue-500" style="width: {{ min(100, ($day->pdf_count / ($exportsPerDay->max('count') ?? 1)) * 100) }}%;" title="{{ $day->pdf_count }} PDF"></div>
                                        @endif
                                        @if($day->csv_count > 0)
                                            <div class="h-full bg-green-500" style="width: {{ min(100, ($day->csv_count / ($exportsPerDay->max('count') ?? 1)) * 100) }}%;" title="{{ $day->csv_count }} CSV"></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-right min-w-max ml-4">
                                <p class="text-white font-bold">{{ $day->count }}</p>
                                <p class="text-slate-400 text-xs">
                                    📄 {{ $day->pdf_count }} | 📋 {{ $day->csv_count }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-400 text-center py-8">{{ __('admin.exports_stats_no_exports') }}</p>
            @endif
        </div>
        </div>
    </div>
</div>
@endsection
