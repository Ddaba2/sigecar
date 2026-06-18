<?php

use App\Services\MarketeurStockService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('depotages', function (Blueprint $table) {
            $table->foreignId('marketeur_id')->nullable()->after('fournisseur')->constrained('marqueteurs')->nullOnDelete();
        });

        Schema::table('chargements', function (Blueprint $table) {
            $table->foreignId('marketeur_id')->nullable()->after('client_nom')->constrained('marqueteurs')->nullOnDelete();
        });

        Schema::table('cuves', function (Blueprint $table) {
            $table->foreignId('marketeur_id')->nullable()->after('produit_id')->constrained('marqueteurs')->nullOnDelete();
        });

        $service = app(MarketeurStockService::class);
        $service->backfillOperationUserIds();
        $service->syncAll();
    }

    public function down(): void
    {
        Schema::table('depotages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marketeur_id');
        });

        Schema::table('chargements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marketeur_id');
        });

        Schema::table('cuves', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marketeur_id');
        });
    }
};
