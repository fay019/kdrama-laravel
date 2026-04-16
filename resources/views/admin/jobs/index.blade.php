<x-app-layout>
    <div class="flex min-h-screen bg-slate-900">
        <x-admin-sidebar />

        <div class="flex-1">
            <!-- Sticky header -->
            <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10">
                <div class="px-3 sm:px-6 lg:px-8 py-4">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                        <span class="text-3xl sm:text-4xl">⚙️</span>
                        <span>{{ __('admin.nav_jobs_monitor') }}</span>
                    </h1>
                    <p class="text-slate-400 mt-1">{{ __('admin.jobs_subtitle') }}</p>
                </div>
            </div>

            <!-- Content -->
            <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">
                <div class="w-full max-w-6xl mx-auto space-y-6">
                    <!-- Success/Error messages -->
                    @if (session('success'))
                        <div class="bg-green-900/30 border border-green-500/50 text-green-200 px-4 py-3 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-900/30 border border-red-500/50 text-red-200 px-4 py-3 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Section 0: Live Logs -->
                    <div>
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            📝 Live Logs
                            <span id="logs-status" class="text-xs text-slate-400 ml-auto">⏸️ Auto-refresh disabled</span>
                        </h2>
                        <div class="bg-slate-900 rounded-lg border border-slate-700 p-4 font-mono text-xs text-slate-300 h-64 overflow-y-auto">
                            <div id="logs-container" class="whitespace-pre-wrap">{{ __('admin.jobs_no_logs') }}</div>
                        </div>

                        <!-- Color Legend -->
                        <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-500">●</span>
                                <span class="text-slate-300">Timestamps</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-blue-400">●</span>
                                <span class="text-slate-300">INFO Level</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-red-400">●</span>
                                <span class="text-slate-300">ERROR Level</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-amber-400">●</span>
                                <span class="text-slate-300">WARNING Level</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-green-400">●</span>
                                <span class="text-slate-300">Success/Progress</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-yellow-400">●</span>
                                <span class="text-slate-300">Numbers/Stats</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-cyan-400">●</span>
                                <span class="text-slate-300">Key Info</span>
                            </div>
                        </div>

                        <div class="mt-2 flex gap-2 flex-wrap">
                            <button onclick="startLiveLogsRefresh()" id="auto-refresh-btn" class="px-3 py-1 text-xs bg-green-600 hover:bg-green-700 text-white rounded transition">
                                ▶️ Auto-refresh
                            </button>
                            <button onclick="stopLiveLogsRefresh()" class="px-3 py-1 text-xs bg-amber-600 hover:bg-amber-700 text-white rounded transition">
                                ⏸️ Stop
                            </button>
                            <button onclick="refreshLogs()" class="px-3 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded transition">
                                🔄 Refresh Now
                            </button>
                            <form method="POST" action="{{ route('admin.jobs.logs.clear') }}" class="inline" onsubmit="return confirm('{{ __('admin.jobs_confirm_clear_logs') }}')">
                                @csrf
                                <button type="submit" class="px-3 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded transition">
                                    {{ __('admin.jobs_clear_logs') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Section 1: Quick Actions -->
                    <div>
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            {{ __('admin.jobs_quick_actions') }}
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach ($tasks as $task)
                                <div class="bg-slate-800 rounded-lg border border-slate-700 p-5 hover:border-slate-600 transition">
                                    <div class="flex items-start gap-3 mb-3">
                                        <span class="text-3xl">{{ $task['icon'] }}</span>
                                        <div class="flex-1">
                                            <h3 class="font-bold text-white">{{ $task['label'] }}</h3>
                                            <p class="text-sm text-slate-400">{{ $task['desc'] }}</p>
                                        </div>
                                    </div>

                                    <!-- Run button -->
                                    <form
                                        method="POST"
                                        action="{{ $task['type'] === 'job' ? route('admin.jobs.dispatch') : route('admin.jobs.run-command') }}"
                                        class="mt-4">
                                        @csrf
                                        @if ($task['type'] === 'job')
                                            <input type="hidden" name="job" value="{{ $task['key'] }}">
                                        @else
                                            <input type="hidden" name="command" value="{{ $task['command'] }}">
                                        @endif
                                        <button type="submit"
                                            class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
                                            {{ __('admin.jobs_run') }}
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Section 2: Pending Queue -->
                    <div>
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            {{ __('admin.jobs_pending') }} ({{ count($pendingJobs) }})
                        </h2>
                        @if (count($pendingJobs) > 0)
                            <!-- Desktop table -->
                            <div class="hidden md:block bg-slate-800 rounded-lg border border-slate-700 overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-900 border-b border-slate-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left font-semibold text-slate-300">{{ __('admin.jobs_name') }}</th>
                                            <th class="px-6 py-3 text-left font-semibold text-slate-300">{{ __('admin.jobs_queue') }}</th>
                                            <th class="px-6 py-3 text-left font-semibold text-slate-300">{{ __('admin.jobs_attempts') }}</th>
                                            <th class="px-6 py-3 text-left font-semibold text-slate-300">{{ __('admin.jobs_available_at') }}</th>
                                            <th class="px-6 py-3 text-left font-semibold text-slate-300">{{ __('admin.jobs_actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-700">
                                        @foreach ($pendingJobs as $job)
                                            <tr class="hover:bg-slate-700/50 transition">
                                                <td class="px-6 py-4 text-slate-200">{{ $job['job'] }}</td>
                                                <td class="px-6 py-4 text-slate-400">{{ $job['queue'] }}</td>
                                                <td class="px-6 py-4 text-slate-400">{{ $job['attempts'] }}</td>
                                                <td class="px-6 py-4 text-slate-400 text-xs">
                                                    {{ \Carbon\Carbon::createFromTimestamp($job['available_at'])->format('Y-m-d H:i:s') }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <form method="POST"
                                                        action="{{ route('admin.jobs.pending.delete', $job['id']) }}"
                                                        class="inline"
                                                        onsubmit="return confirm('{{ __('admin.jobs_confirm_delete_pending') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition">
                                                            {{ __('admin.jobs_delete') }}
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile list -->
                            <div class="md:hidden space-y-3">
                                @foreach ($pendingJobs as $job)
                                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-4">
                                        <div class="font-semibold text-white mb-2">{{ $job['job'] }}</div>
                                        <div class="space-y-1 text-sm text-slate-400 mb-3">
                                            <div><span class="text-slate-500">Queue:</span> {{ $job['queue'] }}</div>
                                            <div><span class="text-slate-500">Attempts:</span> {{ $job['attempts'] }}</div>
                                            <div><span class="text-slate-500">Available:</span>
                                                {{ \Carbon\Carbon::createFromTimestamp($job['available_at'])->format('Y-m-d H:i:s') }}
                                            </div>
                                        </div>
                                        <form method="POST"
                                            action="{{ route('admin.jobs.pending.delete', $job['id']) }}"
                                            onsubmit="return confirm('{{ __('admin.jobs_confirm_delete_pending') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-full px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition">
                                                {{ __('admin.jobs_delete') }}
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6 text-center text-slate-400">
                                {{ __('admin.jobs_queue_empty') }}
                            </div>
                        @endif
                    </div>

                    <!-- Section 3: Failed Jobs -->
                    <div>
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            {{ __('admin.jobs_failed') }} ({{ count($failedJobs) }})
                        </h2>
                        @if (count($failedJobs) > 0)
                            <!-- Desktop table -->
                            <div class="hidden md:block bg-slate-800 rounded-lg border border-slate-700 overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-900 border-b border-slate-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left font-semibold text-slate-300">{{ __('admin.jobs_name') }}</th>
                                            <th class="px-6 py-3 text-left font-semibold text-slate-300">{{ __('admin.jobs_exception') }}</th>
                                            <th class="px-6 py-3 text-left font-semibold text-slate-300">{{ __('admin.jobs_failed_at') }}</th>
                                            <th class="px-6 py-3 text-left font-semibold text-slate-300">{{ __('admin.jobs_actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-700">
                                        @foreach ($failedJobs as $job)
                                            <tr class="hover:bg-slate-700/50 transition">
                                                <td class="px-6 py-4 text-slate-200">{{ $job['job'] }}</td>
                                                <td class="px-6 py-4 text-slate-400 text-xs font-mono">{{ $job['exception'] }}...</td>
                                                <td class="px-6 py-4 text-slate-400 text-xs">{{ $job['failed_at'] }}</td>
                                                <td class="px-6 py-4">
                                                    <div class="flex gap-2">
                                                        <form method="POST"
                                                            action="{{ route('admin.jobs.failed.retry', $job['uuid']) }}"
                                                            class="inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white text-xs rounded transition">
                                                                {{ __('admin.jobs_retry') }}
                                                            </button>
                                                        </form>
                                                        <form method="POST"
                                                            action="{{ route('admin.jobs.failed.delete', $job['uuid']) }}"
                                                            class="inline"
                                                            onsubmit="return confirm('{{ __('admin.jobs_confirm_delete') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition">
                                                                {{ __('admin.jobs_delete') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile list -->
                            <div class="md:hidden space-y-3">
                                @foreach ($failedJobs as $job)
                                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-4">
                                        <div class="font-semibold text-white mb-2">{{ $job['job'] }}</div>
                                        <div class="space-y-2">
                                            <div class="text-sm text-slate-400 font-mono">{{ $job['exception'] }}...</div>
                                            <div class="text-xs text-slate-500">{{ $job['failed_at'] }}</div>
                                            <div class="flex gap-2 mt-3">
                                                <form method="POST"
                                                    action="{{ route('admin.jobs.failed.retry', $job['uuid']) }}"
                                                    class="flex-1">
                                                    @csrf
                                                    <button type="submit"
                                                        class="w-full px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white text-xs rounded transition">
                                                        {{ __('admin.jobs_retry') }}
                                                    </button>
                                                </form>
                                                <form method="POST"
                                                    action="{{ route('admin.jobs.failed.delete', $job['uuid']) }}"
                                                    class="flex-1"
                                                    onsubmit="return confirm('{{ __('admin.jobs_confirm_delete') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-full px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition">
                                                        {{ __('admin.jobs_delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6 text-center text-slate-400">
                                {{ __('admin.jobs_no_failed') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let logsRefreshInterval = null;
        const logsUrl = "{{ route('admin.jobs.logs') }}";

        function colorizeLog(text) {
            // Split by newlines to preserve structure
            return text.split('\n').map(line => {
                if (!line.trim()) return '';

                // Timestamp pattern: [2026-04-16 HH:MM:SS]
                line = line.replace(/(\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\])/g,
                    '<span class="text-slate-500">$1</span>');

                // Log levels
                line = line.replace(/local\.INFO:/g, '<span class="text-blue-400">local.INFO:</span>');
                line = line.replace(/local\.ERROR:/g, '<span class="text-red-400">local.ERROR:</span>');
                line = line.replace(/local\.WARNING:/g, '<span class="text-amber-400">local.WARNING:</span>');

                // Start message - highlight in bright green
                if (line.includes('Starting')) {
                    line = line.replace(/(Starting.*)/g, '<span class="text-green-300 font-semibold">▶ $1</span>');
                }

                // End/Success message - highlight in bright cyan
                if (line.includes('completed successfully')) {
                    line = line.replace(/(.*completed successfully)/g, '<span class="text-cyan-300 font-semibold">✓ $1</span>');
                }

                // Failed message - highlight in bright red
                if (line.includes('failed:')) {
                    line = line.replace(/(.*failed:.*)/g, '<span class="text-red-300 font-semibold">✗ $1</span>');
                }

                // Keywords - success/progress
                line = line.replace(/(Processed|Synced|extracted:)/g,
                    '<span class="text-green-400">$1</span>');

                // Keywords - important
                line = line.replace(/(Total|batch)/g,
                    '<span class="text-cyan-400">$1</span>');

                // Numbers (but avoid replacing already colored ones)
                if (!line.includes('<span')) {
                    line = line.replace(/(\d+)/g,
                        '<span class="text-yellow-400">$1</span>');
                }

                return line;
            }).join('\n');
        }

        function refreshLogs() {
            fetch(logsUrl)
                .then(r => r.json())
                .then(data => {
                    const logsContainer = document.getElementById('logs-container');
                    const colorized = colorizeLog(data.logs || 'No logs');
                    logsContainer.innerHTML = colorized;
                    logsContainer.parentElement.scrollTop = logsContainer.parentElement.scrollHeight;
                })
                .catch(e => console.error('Failed to refresh logs:', e));
        }

        function startLiveLogsRefresh() {
            if (logsRefreshInterval) return;

            document.getElementById('auto-refresh-btn').style.display = 'none';
            document.getElementById('logs-status').textContent = '🟢 Auto-refreshing...';

            logsRefreshInterval = setInterval(refreshLogs, 2000);
            refreshLogs(); // Initial load
        }

        function stopLiveLogsRefresh() {
            if (logsRefreshInterval) {
                clearInterval(logsRefreshInterval);
                logsRefreshInterval = null;
                document.getElementById('auto-refresh-btn').style.display = 'block';
                document.getElementById('logs-status').textContent = '⏸️ Auto-refresh disabled';
            }
        }

        // Load logs on page load
        refreshLogs();
    </script>
</x-app-layout>
