<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupExportedPdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoyer les PDFs exportés expirés (plus de 7 jours)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $exportsDir = storage_path('app/exports');

        // Vérifier si le répertoire existe
        if (!is_dir($exportsDir)) {
            $this->info('📁 Répertoire exports n\'existe pas.');
            return Command::SUCCESS;
        }

        $sevenDaysInSeconds = 7 * 24 * 60 * 60;
        $now = time();
        $deletedCount = 0;
        $totalSize = 0;

        // Scanner tous les fichiers PDF
        $files = glob("{$exportsDir}/*.pdf");

        foreach ($files as $file) {
            $fileAge = $now - filemtime($file);

            // Si le fichier est plus vieux que 7 jours
            if ($fileAge > $sevenDaysInSeconds) {
                $fileSize = filesize($file);
                $totalSize += $fileSize;

                if (unlink($file)) {
                    $deletedCount++;
                    $this->line("  🗑️  Supprimé: " . basename($file));
                } else {
                    $this->error("  ❌ Erreur lors de la suppression: " . basename($file));
                }
            }
        }

        // Afficher le résumé
        if ($deletedCount > 0) {
            $sizeInMb = round($totalSize / (1024 * 1024), 2);
            $this->info("✅ Cleanup complété!");
            $this->line("   📊 {$deletedCount} fichier(s) supprimé(s) ({$sizeInMb} MB libérés)");
        } else {
            $this->info("✅ Aucun fichier expiré à supprimer.");
        }

        return Command::SUCCESS;
    }
}
