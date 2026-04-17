<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('depotages', function (Blueprint $table) {
            $table->id();
            $table->string('numero_depotage')->unique();
            $table->datetime('date_operation');
            $table->foreignId('produit_id')->constrained();
            $table->foreignId('cuve_destination_id')->constrained('cuves');
            $table->integer('volume_brut');
            $table->decimal('temperature', 5, 2);
            $table->integer('volume_corrige');
            $table->string('fournisseur');
            $table->string('provenance');
            $table->string('numero_bon_chargement')->nullable();
            $table->string('plaque_imm');
            $table->string('chauffeur_nom');
            $table->string('chauffeur_permis');
            $table->string('chauffeur_tel')->nullable();
            $table->string('declaration_douane')->nullable();
            $table->string('bureau_douane')->nullable();
            $table->enum('status', ['sous_douane', 'acquitte', 'annule'])->default('sous_douane');
            $table->foreignId('created_by')->constrained('users');
            $table->string('document_pdf')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('depotages');
    }
};
