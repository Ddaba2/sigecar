<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Création de la table produits
     */
    public function up()
    {
        // Création de la table
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code')->unique();
            $table->enum('type', ['essence', 'gasoil', 'jet_a1', 'marine'])->default('essence');
            $table->decimal('density', 5, 4)->default(0.7500);
            $table->string('unit')->default('L');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Insertion des produits par défaut
        DB::table('produits')->insert([
            ['nom' => 'Essence Super', 'code' => 'SUP-95', 'type' => 'essence', 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Gasoil Premium', 'code' => 'GSP-50', 'type' => 'gasoil', 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Jet A1', 'code' => 'JET-A1', 'type' => 'jet_a1', 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Gasoil Marine', 'code' => 'MAR-180', 'type' => 'marine', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('produits');
    }
};
