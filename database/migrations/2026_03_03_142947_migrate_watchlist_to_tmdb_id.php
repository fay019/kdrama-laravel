<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('watchlist_items', function (Blueprint $table) {
            $table->unsignedBigInteger('tmdb_id')->nullable()->after('content_id');
        });

        // Migrer les données
        $items = DB::table('watchlist_items')
            ->join('contents', 'watchlist_items.content_id', '=', 'contents.id')
            ->select('watchlist_items.id', 'contents.tmdb_id')
            ->get();

        foreach ($items as $item) {
            DB::table('watchlist_items')
                ->where('id', $item->id)
                ->update(['tmdb_id' => $item->tmdb_id]);
        }

        // Nettoyage robuste des index et colonnes
        // On sépare en plusieurs appels Schema::table pour isoler les erreurs

        try {
            Schema::table('watchlist_items', function (Blueprint $table) {
                $table->dropUnique('watchlist_items_user_id_content_id_unique');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('watchlist_items', function (Blueprint $table) {
                $table->dropForeign('watchlist_items_content_id_foreign');
            });
        } catch (\Exception $e) {}

        Schema::table('watchlist_items', function (Blueprint $table) {
            $table->unsignedBigInteger('tmdb_id')->nullable(false)->change();
            $table->dropColumn('content_id');
            $table->unique(['user_id', 'tmdb_id']);
        });
    }

    public function down(): void
    {
        Schema::table('watchlist_items', function (Blueprint $table) {
            $table->unsignedBigInteger('content_id')->nullable()->after('user_id');
        });

        // Tenter de remigrer vers content_id (si les contents existent encore)
        $items = DB::table('watchlist_items')
            ->join('contents', 'watchlist_items.tmdb_id', '=', 'contents.tmdb_id')
            ->select('watchlist_items.id', 'contents.id as content_id')
            ->get();

        foreach ($items as $item) {
            DB::table('watchlist_items')
                ->where('id', $item->id)
                ->update(['content_id' => $item->content_id]);
        }

        Schema::table('watchlist_items', function (Blueprint $table) {
            $table->dropColumn('tmdb_id');
        });
    }
};
