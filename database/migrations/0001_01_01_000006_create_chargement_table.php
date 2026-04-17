<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chargements', function (Blueprint $table) {
            $table->id();
            $table->string('numero_chargement')->unique();
            $table->datetime('date_operation');
            $table->foreignId('produit_id')->constrained();
            $table->foreignId('cuve_source_id')->constrained('cuves');
            $table->integer('volume_brut');
            $table->decimal('temperature', 5, 2);
            $table->integer('volume_corrige');
            $table->string('client_nom');
            $table->string('client_code')->nullable();
            $table->string('plaque_imm');
            $table->string('chauffeur_nom');
            $table->string('chauffeur_permis');
            $table->string('chauffeur_badge')->nullable();
            $table->integer('capacite_camion');
            $table->enum('status', ['acquitte', 'annule'])->default('acquitte');
            $table->foreignId('created_by')->constrained('users');
            $table->string('document_pdf')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chargements');
    }
};
