<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Désactiver les contraintes FK pour permettre la suppression de la table
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('marqueteurs');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Recreate the table structure (basic) for rollback purposes
        Schema::create('marqueteurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company_name');
            $table->string('company_registration')->nullable();
            $table->string('address')->nullable();
            $table->string('telephone')->nullable();
            $table->string('contact_person')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }
};
