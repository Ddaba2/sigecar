<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cuves', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');
            $table->foreignId('produit_id')->constrained();
            $table->integer('capacite_totale')->default(0);
            $table->integer('niveau_actuel')->default(0);
            $table->integer('seuil_alerte_bas')->default(10000);
            $table->integer('seuil_alerte_haut')->default(450000);
            $table->enum('status', ['operationnel', 'maintenance', 'hors_service'])->default('operationnel');
            $table->enum('type_douane', ['sous_douane', 'acquitte'])->default('acquitte');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cuves');
    }
};
