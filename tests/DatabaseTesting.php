<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait DatabaseTesting
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('depotages');
        Schema::dropIfExists('chargements');
        Schema::dropIfExists('produits');
        Schema::dropIfExists('cuves');
        Schema::dropIfExists('logs');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'gestionnaire', 'marketeur'])->default('marketeur');
            $table->string('telephone')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('company_name')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

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

        Schema::create('cuves', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');
            $table->foreignId('produit_id')->constrained('produits');
            $table->integer('capacite_totale')->default(0);
            $table->integer('niveau_actuel')->default(0);
            $table->integer('seuil_alerte_bas')->default(10000);
            $table->integer('seuil_alerte_haut')->default(450000);
            $table->enum('status', ['operationnel', 'maintenance', 'hors_service'])->default('operationnel');
            $table->enum('type_douane', ['sous_douane', 'acquitte'])->default('acquitte');
            $table->timestamps();
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignId('cuve_id')->constrained('cuves')->cascadeOnDelete();
            $table->decimal('quantite', 10, 2);
            $table->dateTime('date_stock');
            $table->enum('type_operation', ['entree', 'sortie']);
            $table->timestamps();
        });

        Schema::create('depotages', function (Blueprint $table) {
            $table->id();
            $table->string('numero_depotage')->unique();
            $table->dateTime('date_operation');
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignId('cuve_destination_id')->constrained('cuves')->cascadeOnDelete();
            $table->integer('volume_brut');
            $table->decimal('temperature', 5, 2)->default(15);
            $table->integer('volume_corrige');
            $table->string('fournisseur');
            $table->string('provenance');
            $table->string('numero_bon_chargement');
            $table->string('plaque_imm');
            $table->string('chauffeur_nom');
            $table->string('chauffeur_permis');
            $table->string('chauffeur_tel')->nullable();
            $table->string('declaration_douane')->nullable();
            $table->string('bureau_douane')->nullable();
            $table->enum('status', ['en_cours', 'termine', 'annule'])->default('en_cours');
            $table->foreignId('created_by')->constrained('users');
            $table->string('document_pdf')->nullable();
            $table->timestamps();
        });

        Schema::create('chargements', function (Blueprint $table) {
            $table->id();
            $table->string('numero_chargement')->unique();
            $table->dateTime('date_operation');
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignId('cuve_source_id')->constrained('cuves')->cascadeOnDelete();
            $table->integer('volume_brut');
            $table->decimal('temperature', 5, 2)->default(15);
            $table->integer('volume_corrige');
            $table->string('client_nom');
            $table->string('client_code')->nullable();
            $table->string('plaque_imm');
            $table->string('chauffeur_nom');
            $table->string('chauffeur_permis');
            $table->string('chauffeur_badge')->nullable();
            $table->integer('capacite_camion');
            $table->enum('status', ['en_cours', 'termine', 'annule'])->default('en_cours');
            $table->foreignId('created_by')->constrained('users');
            $table->string('document_pdf')->nullable();
            $table->timestamps();
        });

        Schema::create('marqueteurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('company_name');
            $table->string('company_registration')->nullable();
            $table->string('address')->nullable();
            $table->string('telephone')->nullable();
            $table->string('contact_person')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('cessions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_cession')->unique();
            $table->datetime('date_cession');
            $table->foreignId('cedant_id')->constrained('marqueteurs');
            $table->foreignId('beneficiaire_id')->constrained('marqueteurs');
            $table->foreignId('produit_id')->constrained('produits');
            $table->foreignId('cuve_id')->constrained('cuves');
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

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('cessions');
        Schema::dropIfExists('marqueteurs');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('depotages');
        Schema::dropIfExists('chargements');
        Schema::dropIfExists('produits');
        Schema::dropIfExists('cuves');
        Schema::dropIfExists('logs');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }
}
