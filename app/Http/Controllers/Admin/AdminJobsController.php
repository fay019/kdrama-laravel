<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncPopularActors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

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
            dispatch(new SyncPopularActors());
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

        if (!file_exists($logFile)) {
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
        $jobHistory = \App\Models\JobHistory::orderByDesc('completed_at')
            ->paginate(20);

        return view('admin.jobs.history', compact('jobHistory'));
    }
}
