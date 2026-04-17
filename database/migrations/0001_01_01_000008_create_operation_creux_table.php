<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operations_creux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depotage_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('chargement_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('numero_creux');
            $table->foreignId('produit_id')->constrained();
            $table->integer('capacite');
            $table->integer('volume')->default(0);
            $table->decimal('temperature', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('operations_creux');
    }
};
