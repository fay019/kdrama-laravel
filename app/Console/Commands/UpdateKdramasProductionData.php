<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kdrama;
use App\Services\TmdbService;

class UpdateKdramasProductionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-kdramas-production-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Récupère et met à jour les données de production et réseaux pour tous les kdramas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new TmdbService();
        $kdramas = Kdrama::all();
        $total = $kdramas->count();

        $this->info("🎬 Mise à jour de $total kdramas...\n");

        $updated = 0;
        $failed = 0;

        foreach ($kdramas as $kdrama) {
            try {
                $this->line("Récupération: {$kdrama->name}...", 'comment');

                $details = $service->getContentDetails($kdrama->tmdb_id, 'tv');

                if ($details) {
                    $kdrama->update([
                        'production_companies' => $details['production_companies'] ?? null,
                        'networks' => $details['networks'] ?? null,
                    ]);

                    $companies = count($details['production_companies'] ?? []);
                    $networks = count($details['networks'] ?? []);

                    $this->line("  ✅ " . $companies . " studios, " . $networks . " réseaux", 'info');
                    $updated++;
                } else {
                    $this->line("  ⚠️ Pas de données reçues", 'warn');
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->line("  ❌ Erreur: " . $e->getMessage(), 'error');
                $failed++;
            }

            // Pause pour respecter les limits API
            sleep(1);
        }

        $this->newLine();
        $this->info("✅ Mise à jour terminée!");
        $this->line("  • $updated kdramas mis à jour");
        $this->line("  • $failed erreurs");
    }
}
