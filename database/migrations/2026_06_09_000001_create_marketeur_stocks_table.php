<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Services\MarketeurStockService;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('marketeur_stocks');
        Schema::create('marketeur_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->unsignedBigInteger('quantite')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'produit_id'], 'marketeur_stocks_user_produit_unique');
        });

        if (Schema::hasTable('depotages')) {
            app(MarketeurStockService::class)->syncAll();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marketeur_stocks');
    }
};
