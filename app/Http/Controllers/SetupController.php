<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    public function index()
    {
        // Check if setup is needed
        if (!$this->isSetupNeeded()) {
            return redirect()->route('home');
        }

        return view('setup.index');
    }

    public function setup(Request $request)
    {
        if (!$this->isSetupNeeded()) {
            return redirect()->route('home');
        }

        // Run migrations
        Artisan::call('migrate', ['--force' => true]);

        // Run seeder
        Artisan::call('db:seed');

        // Create marker file to indicate setup is complete
        $this->markSetupComplete();

        return redirect()->route('login')->with('success', 'Installation réussie! Veuillez vous connecter avec admin@kdrama.local / password');
    }

    private function isSetupNeeded(): bool
    {
        // If marker file exists, setup is not needed
        if (file_exists(storage_path('.setup-complete'))) {
            return false;
        }

        try {
            // Check database and user count
            return User::count() === 0;
        } catch (\Exception $e) {
            return true;
        }
    }

    private function markSetupComplete(): void
    {
        file_put_contents(storage_path('.setup-complete'), date('Y-m-d H:i:s'));
    }
}
