<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('produits_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cuves_id')->constrained()->cascadeOnDelete();

            $table->double('quantite');

            $table->enum('statut_douanier', ['sous_douane', 'acquitte']);

            $table->enum('type_operation', ['depotage', 'chargement', 'cession']);

            $table->unsignedBigInteger('source_id')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
