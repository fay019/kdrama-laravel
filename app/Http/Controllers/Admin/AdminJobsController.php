<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncPopularActors;
use App\Models\JobHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class AdminJobsController extends Controller
{
    public function index()
    {
        // Get pending jobs from queue
        $pendingJobs = DB::table('jobs')
            ->orderBy('available_at')
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);

                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'job' => $payload['displayName'] ?? 'Unknown',
                    'attempts' => $job->attempts,
                    'available_at' => $job->available_at,
                    'created_at' => $job->created_at,
                ];
            });

        // Get failed jobs
        $failedJobs = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                $exception = substr($job->exception, 0, 200);

                return [
                    'uuid' => $job->uuid,
                    'job' => $payload['displayName'] ?? 'Unknown',
                    'queue' => $job->queue,
                    'exception' => $exception,
                    'failed_at' => $job->failed_at,
                ];
            });

        // Available tasks
        $tasks = [
            [
                'key' => 'sync_actors',
                'type' => 'job',
                'label' => __('admin.jobs_task_sync_actors'),
                'icon' => '🎭',
                'desc' => __('admin.jobs_task_sync_actors_desc'),
            ],
            [
                'key' => 'cleanup_pdfs',
                'type' => 'command',
                'command' => 'exports:cleanup',
                'label' => __('admin.jobs_task_cleanup_pdfs'),
                'icon' => '🗑️',
                'desc' => __('admin.jobs_task_cleanup_pdfs_desc'),
            ],
            [
                'key' => 'update_production',
                'type' => 'command',
                'command' => 'app:update-kdramas-production-data',
                'label' => __('admin.jobs_task_update_production'),
                'icon' => '🎬',
                'desc' => __('admin.jobs_task_update_production_desc'),
            ],
        ];

        return view('admin.jobs.index', compact('pendingJobs', 'failedJobs', 'tasks'));
    }

    public function dispatchJob(Request $request)
    {
        $job = $request->validate(['job' => 'required|in:sync_actors'])['job'];

        if ($job === 'sync_actors') {
            dispatch(new SyncPopularActors);
        }

        return back()->with('success', __('admin.jobs_dispatched'));
    }

    public function runCommand(Request $request)
    {
        $command = $request->validate([
            'command' => 'required|in:exports:cleanup,app:update-kdramas-production-data',
        ])['command'];

        try {
            Artisan::call($command);

            return back()->with('success', __('admin.jobs_command_executed', ['command' => $command]));
        } catch (\Exception $e) {
            return back()->with('error', 'Command failed: '.$e->getMessage());
        }
    }

    public function retryFailed($uuid)
    {
        try {
            Artisan::call('queue:retry', ['id' => [$uuid]]);

            return back()->with('success', __('admin.jobs_requeued'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry job: '.$e->getMessage());
        }
    }

    public function deleteFailed($uuid)
    {
        try {
            DB::table('failed_jobs')->where('uuid', $uuid)->delete();

            return back()->with('success', __('admin.jobs_failed_deleted'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete job: '.$e->getMessage());
        }
    }

    public function getLogs()
    {
        $logFile = storage_path('logs/jobs.log');

        if (! file_exists($logFile)) {
            return response()->json(['logs' => 'No job logs yet. Launch a job to see logs here.']);
        }

        // Get last 100 lines from jobs log
        $lines = array_slice(file($logFile), -100);
        $logs = implode('', array_map(fn ($line) => htmlspecialchars($line), $lines));

        return response()->json(['logs' => $logs ?: 'No logs yet']);
    }

    public function clearLogs()
    {
        try {
            $logFile = storage_path('logs/jobs.log');

            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
            }

            return back()->with('success', __('admin.jobs_logs_cleared'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear logs: '.$e->getMessage());
        }
    }

    public function deletePending($jobId)
    {
        try {
            DB::table('jobs')->where('id', $jobId)->delete();

            return back()->with('success', __('admin.jobs_pending_deleted'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete job: '.$e->getMessage());
        }
    }

    public function history()
    {
        $jobHistory = JobHistory::orderByDesc('completed_at')
            ->paginate(20);

        return view('admin.jobs.history', compact('jobHistory'));
    }

    public function startWorker()
    {
        try {
            $logFile = storage_path('logs/queue-worker.log');
            $pidFile = storage_path('queue-worker.pid');
            $basePath = base_path();

            // Check if worker is already running
            if (file_exists($pidFile)) {
                $pidContent = file_get_contents($pidFile);
                if (preg_match('/^(\d+)/', $pidContent, $matches)) {
                    $pid = (int) $matches[1];
                    // Check if process is still alive
                    exec("ps -p $pid > /dev/null 2>&1", $output, $status);
                    if ($status === 0) {
                        return back()->with('success', __('admin.jobs_worker_already_running'));
                    }
                }
            }

            // Ensure log directory exists
            $logDir = dirname($logFile);
            if (! is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Use PHP_BINARY directly (works with Herd/Homebrew)
            $phpPath = PHP_BINARY;

            // Build command: launch in background without blocking
            $cmd = sprintf(
                'cd %s && nohup %s -d memory_limit=512M artisan queue:work --timeout=600 >> %s 2>&1 & echo $! > %s',
                escapeshellarg($basePath),
                escapeshellarg($phpPath),
                escapeshellarg($logFile),
                escapeshellarg($pidFile)
            );

            // Execute in background (non-blocking)
            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0) {
                return back()->with('error', 'Failed to start worker. Exit code: '.$exitCode);
            }

            return back()->with('success', __('admin.jobs_worker_started'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start worker: '.$e->getMessage());
        }
    }
}
