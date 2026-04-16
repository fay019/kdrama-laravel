# 🚀 Professional Async Queue Setup - KDrama Laravel

**Updated:** 2026-04-16  
**Queue Driver:** Database  
**Job Logging:** Isolated `storage/logs/jobs.log`

---

## 📋 Quick Start

### 1. Environment Configuration

Queue is configured to use **database driver** (persistent job storage):

```bash
# In .env
QUEUE_CONNECTION=database
```

Database tables already exist:
- `jobs` - Stores pending/available jobs
- `failed_jobs` - Stores failed job records

### 2. Running the Queue Worker

**In a separate terminal/process**, run:

```bash
php artisan queue:work --timeout=300
```

**Parameters:**
- `--timeout=300` - Max job execution time (5 minutes for SyncPopularActors)
- `--queue=default` - Default queue name (optional)
- `--sleep=3` - Seconds to wait between job checks (optional, default: 3)
- `--max-tries=3` - Max retry attempts on failure (optional)
- `--tries=3` - Same as `--max-tries`

### 3. Verify Queue is Running

**Option A: Admin Dashboard**
- Visit `/admin/jobs` to see:
  - ✅ Pending queue jobs in real-time
  - ✅ Failed jobs with retry/delete options
  - ✅ Live logs from `storage/logs/jobs.log`

**Option B: Database**
```bash
php artisan tinker
>>> DB::table('jobs')->count()
>>> DB::table('failed_jobs')->count()
```

**Option C: Watch Logs**
```bash
tail -f storage/logs/jobs.log
```

---

## 🎯 Complete Job Lifecycle

### Dispatch a Job (Admin Dashboard)

1. Visit `/admin/jobs` → "Sync Actors" card
2. Click "Lancer" (Run)
3. Job immediately appears in "Pending Queue" table
4. Watch real-time logs below

### Worker Processes the Job

1. Worker picks up job from `jobs` table
2. Executes `App\Jobs\SyncPopularActors::handle()`
3. Logs all activities to `storage/logs/jobs.log`
4. On success:
   - Job removed from `jobs` table
   - Log shows: "Actor sync completed successfully"
5. On failure:
   - Job moved to `failed_jobs` table with exception
   - Log shows: "Actor sync failed: {error}"

### Monitor & Manage

**Pending Jobs:**
- Real-time refresh every 2 seconds
- Shows: Queue, Attempts, Available Time
- Can't modify (worker owns them)

**Failed Jobs:**
- Shows exception preview (first 200 chars)
- Actions:
  - 🔄 **Retry** - Re-queues job (attempts reset to 0)
  - 🗑️ **Delete** - Permanently removes from failed_jobs

---

## 🔧 Configuration Details

### Job Configuration (`app/Jobs/SyncPopularActors.php`)

```php
class SyncPopularActors implements ShouldQueue
{
    use Queueable;
    
    public function handle(TmdbService $tmdbService): void
    {
        // Increase timeout for this specific job
        set_time_limit(300);  // 5 minutes
        
        Log::channel('jobs')->info('Starting Korean drama actors sync job');
        // ... job logic ...
        Log::channel('jobs')->info('Actor sync completed successfully');
    }
}
```

**Key Points:**
- Implements `ShouldQueue` - runs asynchronously
- `set_time_limit(300)` - ensures 5-minute execution window
- Uses `Log::channel('jobs')` - logs to isolated jobs.log file
- No database changes (reads TMDB API)

### Logging Configuration (`config/logging.php`)

```php
'jobs' => [
    'driver' => 'single',
    'path' => storage_path('logs/jobs.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'replace_placeholders' => true,
],
```

**Result:** All job output in separate file, never mixes with application logs

### Queue Configuration (`config/queue.php`)

```php
'default' => env('QUEUE_CONNECTION', 'database'),

'connections' => [
    'database' => [
        'driver' => 'database',
        'connection' => env('DB_QUEUE_CONNECTION'),
        'table' => env('DB_QUEUE_TABLE', 'jobs'),
        'queue' => env('DB_QUEUE', 'default'),
        'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
        'after_commit' => false,
    ],
    // ... other drivers ...
]
```

**Settings:**
- Driver: `database` (persistent, MySQL-backed)
- Table: `jobs` (auto-created by Laravel)
- Retry after: 90 seconds (if reserved but not completed)

---

## 🚨 Common Scenarios

### Job Doesn't Start

**Check 1:** Is worker running?
```bash
ps aux | grep "queue:work"
# Should show: php artisan queue:work --timeout=300
```

**Check 2:** Is queue connection correct?
```bash
php artisan config:show queue.default
# Should output: database
```

**Check 3:** Are jobs in the database?
```bash
php artisan tinker
>>> DB::table('jobs')->select('id', 'payload', 'available_at')->get()
```

**Check 4:** Watch worker output
```bash
# Terminal running worker should show:
# [2026-04-16 14:30:00] Processing: App\Jobs\SyncPopularActors
# [2026-04-16 14:35:15] Processed: App\Jobs\SyncPopularActors
```

---

### Job Takes Too Long (Timeout)

**Error:** `Maximum execution time of X seconds exceeded`

**Fix 1:** Increase in .env (if using shared hosting)
```bash
# In .user.ini or php.ini
max_execution_time = 600  # 10 minutes
```

**Fix 2:** Ensure worker has timeout set
```bash
php artisan queue:work --timeout=300
# NOT just: php artisan queue:work
```

**Fix 3:** Check job itself
```php
// In handle() method
set_time_limit(300);  // Must be present in SyncPopularActors
```

---

### Too Many Failed Jobs

**View failed jobs:**
```bash
php artisan queue:failed
```

**Retry all failed jobs:**
```bash
php artisan queue:retry all
```

**Forget specific failed job:**
```bash
php artisan queue:forget {id}
```

---

## 📊 Admin Dashboard Overview

### URL: `/admin/jobs`

**Three Main Sections:**

#### 1. Quick Actions
- **Sync Actors** 🎭
  - Scans 100 pages of TMDB Korean dramas
  - Extracts unique actors by ID
  - Bulk upserts to `actors` table
  - Timeout: 5 minutes
  - Logs to: `storage/logs/jobs.log`

- **Cleanup PDFs** 🗑️
  - Artisan command: `exports:cleanup`
  - Deletes export PDFs older than 7 days
  - Runs synchronously (quick command)

- **Update Production** 🎬
  - Artisan command: `app:update-kdramas-production-data`
  - Updates production companies/networks from TMDB
  - Runs synchronously (quick command)

#### 2. Pending Queue (Real-Time)
- Shows all jobs currently in database `jobs` table
- Auto-refreshes every 2 seconds
- Columns:
  - `ID` - Job ID in database
  - `Queue` - default | named queue
  - `Job` - Class name
  - `Attempts` - Current attempt number
  - `Available At` - Unix timestamp when job becomes available
- Empty when: No pending jobs or worker is active

#### 3. Failed Jobs
- Shows all jobs in `failed_jobs` table
- Columns:
  - `UUID` - Unique job identifier
  - `Job` - Class name
  - `Queue` - Queue name
  - `Exception` - First 200 chars of error
  - `Failed At` - When it failed
  - `Actions` - Retry or Delete buttons

#### 4. Live Logs
- Shows last 100 lines from `storage/logs/jobs.log`
- Auto-refresh toggle (on by default)
- Manual refresh button
- Scrollable 64px height container
- Updates every 2 seconds when auto-refresh enabled

---

## 🔄 Advanced Usage

### Run Multiple Workers (Load Balancing)

**Terminal 1:**
```bash
php artisan queue:work --timeout=300 --queue=default
```

**Terminal 2:**
```bash
php artisan queue:work --timeout=300 --queue=default
```

**Result:** Both workers pull from same `jobs` table, processing in parallel

### Named Queues

```bash
# Dispatch to specific queue
dispatch(new SyncPopularActors())->onQueue('imports');

# Process only that queue
php artisan queue:work --timeout=300 --queue=imports
```

### Production Setup (Supervisor)

**Install Supervisor:**
```bash
# macOS
brew install supervisor

# Ubuntu/Debian
sudo apt-get install supervisor
```

**Create config file `/etc/supervisor/conf.d/kdrama-queue.conf`:**
```ini
[program:kdrama-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/kdrama-laravel/artisan queue:work --timeout=300 --queue=default
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/kdrama-laravel/storage/logs/queue-worker.log
stopwaitsecs=300
```

**Start workers:**
```bash
supervisorctl reread
supervisorctl update
supervisorctl start kdrama-queue:*
```

**Monitor:**
```bash
supervisorctl status kdrama-queue:*
```

---

## 🎯 Commands Reference

```bash
# Start worker (main command)
php artisan queue:work --timeout=300

# View pending jobs
php artisan queue:list

# View failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Retry specific failed job
php artisan queue:retry {uuid}

# Forget (delete) failed job
php artisan queue:forget {uuid}

# Flush all failed jobs
php artisan queue:flush

# Flush all pending jobs
php artisan queue:clear

# Listen for jobs (verbose output)
php artisan queue:work --timeout=300 -v

# Process single job then exit
php artisan queue:work --timeout=300 --max-jobs=1

# Stop processing after 1 hour
php artisan queue:work --timeout=300 --max-time=3600

# Only process 10 jobs per worker cycle
php artisan queue:work --timeout=300 --backoff=3
```

---

## 📝 Logs Location

**Job Logs:**
```
storage/logs/jobs.log
```

**Application Logs:**
```
storage/logs/laravel.log
```

**Queue Worker Output (when running in terminal):**
```
[2026-04-16 14:30:00] Processing: App\Jobs\SyncPopularActors
[2026-04-16 14:30:15] Processed: App\Jobs\SyncPopularActors
[2026-04-16 14:30:15] Processing: App\Jobs\SendExportEmail
[2026-04-16 14:30:20] Failed: App\Jobs\SendExportEmail
```

---

## ✅ Verification Checklist

Before using in production:

- [ ] `QUEUE_CONNECTION=database` in `.env`
- [ ] `jobs` table exists in database
- [ ] `failed_jobs` table exists in database
- [ ] `php artisan queue:work --timeout=300` runs without errors
- [ ] Job appears in pending queue immediately after dispatch
- [ ] Worker processes job successfully
- [ ] Job disappears from pending after completion
- [ ] Logs appear in `storage/logs/jobs.log`
- [ ] Admin dashboard shows job lifecycle correctly
- [ ] Failed job retry functionality works

---

## 📞 Troubleshooting

**Worker exits immediately:**
```bash
# Run with verbose output
php artisan queue:work --timeout=300 -vvv
# Check for database connection errors
```

**Jobs stuck in pending:**
- Check worker is running: `ps aux | grep queue:work`
- Check timeout setting in worker command
- Check job's `available_at` timestamp (may be in future)

**Missing jobs.log file:**
```bash
# Create it manually
touch storage/logs/jobs.log
chmod 666 storage/logs/jobs.log
```

**Database locked errors:**
- Reduce number of workers
- Check for long-running transactions
- Use `retry_after` setting in `config/queue.php`

---

**Status:** ✅ Production Ready  
**Last Verified:** 2026-04-16  
**Queue Driver:** Database (MySQL-backed)  
**Timeout:** 300 seconds (5 minutes)
