<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cessions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_cession')->unique();
            $table->datetime('date_cession');
            $table->foreignId('cedant_id')->constrained('marqueteurs');
            $table->foreignId('beneficiaire_id')->constrained('marqueteurs');
            $table->foreignId('produit_id')->constrained();
            $table->foreignId('cuve_id')->constrained();
            $table->integer('volume');
            $table->integer('volume_corrige');
            $table->decimal('temperature', 5, 2)->default(15);
            $table->decimal('prix_unitaire', 12, 2)->default(0);
            $table->decimal('montant_total', 15, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->constrained('users');
            $table->string('document_pdf')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cessions');
    }
};
