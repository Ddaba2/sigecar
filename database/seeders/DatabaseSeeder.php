<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Produit;
use App\Models\Cuve;
use App\Models\Depotage;
use App\Models\Chargement;
use App\Models\Cession;
use App\Models\Marketeur;

/**
 * Seeder principal de la base de données
 * Alimente la base avec des données de test et initiales
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Exécute le seeding de la base de données
     */
    public function run()
    {
        // Création ou mise à jour de l'utilisateur admin
        User::updateOrCreate(
            ['email' => 'admin@sigecar.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // Création ou mise à jour du gestionnaire
        User::updateOrCreate(
            ['email' => 'gestionnaire@sigecar.com'],
            [
                'name' => 'Gestionnaire',
                'password' => bcrypt('gest123'),
                'role' => 'gestionnaire',
                'status' => 'active',
            ]
        );

        // Marketeurs
        $user1 = User::updateOrCreate(
            ['email' => 'petrobama@sigecar.com'],
            [
                'name' => 'Petro Bama',
                'password' => bcrypt('mark123'),
                'role' => 'marketeur',
                'company_name' => 'Petro Bama',
                'status' => 'active',
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'corridor@sigecar.com'],
            [
                'name' => 'Corridor',
                'password' => bcrypt('mark123'),
                'role' => 'marketeur',
                'company_name' => 'Corridor Group',
                'status' => 'active',
            ]
        );

        // Marketeur profiles
        Marketeur::create([
            'user_id' => $user1->id,
            'company_name' => 'Petro Bama',
            'company_registration' => 'REG-001',
            'telephone' => '+223 70 00 00 01',
            'status' => 'active',
        ]);

        Marketeur::create([
            'user_id' => $user2->id,
            'company_name' => 'Corridor Group',
            'company_registration' => 'REG-002',
            'telephone' => '+223 70 00 00 02',
            'status' => 'active',
        ]);

        // Produits (déjà dans migration, mais au cas où)
        // Cuves
        Cuve::updateOrCreate(
            ['code' => 'BAC-01'],
            [
                'nom' => 'Cuve Essence Super',
                'produit_id' => 1,
                'capacite_totale' => 300000,
                'niveau_actuel' => 225000,
                'seuil_alerte_bas' => 30000,
                'seuil_alerte_haut' => 280000,
                'status' => 'operationnel',
                'type_douane' => 'acquitte',
            ]
        );

        Cuve::updateOrCreate(
            ['code' => 'BAC-02'],
            [
                'nom' => 'Cuve Gasoil Premium',
                'produit_id' => 2,
                'capacite_totale' => 300000,
                'niveau_actuel' => 36000,
                'seuil_alerte_bas' => 30000,
                'seuil_alerte_haut' => 280000,
                'status' => 'operationnel',
                'type_douane' => 'acquitte',
            ]
        );

        Cuve::updateOrCreate(
            ['code' => 'BAC-03'],
            [
                'nom' => 'Cuve Jet A1',
                'produit_id' => 3,
                'capacite_totale' => 500000,
                'niveau_actuel' => 485000,
                'seuil_alerte_bas' => 50000,
                'seuil_alerte_haut' => 480000,
                'status' => 'operationnel',
                'type_douane' => 'sous_douane',
            ]
        );

        Cuve::updateOrCreate(
            ['code' => 'BAC-04'],
            [
                'nom' => 'Cuve Gasoil Marine',
                'produit_id' => 4,
                'capacite_totale' => 400000,
                'niveau_actuel' => 180000,
                'seuil_alerte_bas' => 40000,
                'seuil_alerte_haut' => 380000,
                'status' => 'operationnel',
                'type_douane' => 'acquitte',
            ]
        );

        // Dépôts de test
        Depotage::updateOrCreate(
            ['numero_depotage' => 'DEP-2026-001'],
            [
                'date_operation' => now()->subDays(3),
                'produit_id' => 1,
                'cuve_destination_id' => 1,
                'volume_brut' => 90000,
                'temperature' => 25.5,
                'volume_corrige' => 90000,
                'fournisseur' => 'Petro Bama',
                'provenance' => 'Bamako',
                'numero_bon_chargement' => 'BC-001',
                'plaque_imm' => 'ML-123-AB',
                'chauffeur_nom' => 'Amadou Diallo',
                'chauffeur_permis' => 'P-123456',
                'chauffeur_tel' => '+223 70 00 00 01',
                'declaration_douane' => 'DEC-001',
                'bureau_douane' => 'Douane Bamako',
                'status' => 'acquitte',
                'created_by' => 1,
            ]
        );

        Depotage::updateOrCreate(
            ['numero_depotage' => 'DEP-2026-002'],
            [
                'date_operation' => now()->subDays(10),
                'produit_id' => 2,
                'cuve_destination_id' => 2,
                'volume_brut' => 50000,
                'temperature' => 24.0,
                'volume_corrige' => 50000,
                'fournisseur' => 'GYF',
                'provenance' => 'Kayes',
                'numero_bon_chargement' => 'BC-002',
                'plaque_imm' => 'ML-456-CD',
                'chauffeur_nom' => 'Fatoumata Traore',
                'chauffeur_permis' => 'P-234567',
                'chauffeur_tel' => '+223 70 00 00 02',
                'declaration_douane' => 'DEC-002',
                'bureau_douane' => 'Douane Kayes',
                'status' => 'acquitte',
                'created_by' => 1,
            ]
        );

        Depotage::updateOrCreate(
            ['numero_depotage' => 'DEP-2025-003'],
            [
                'date_operation' => now()->subDays(20),
                'produit_id' => 3,
                'cuve_destination_id' => 3,
                'volume_brut' => 200000,
                'temperature' => 26.0,
                'volume_corrige' => 200000,
                'fournisseur' => 'NDC',
                'provenance' => 'Sikasso',
                'numero_bon_chargement' => 'BC-003',
                'plaque_imm' => 'ML-789-EF',
                'chauffeur_nom' => 'Ibrahim Keita',
                'chauffeur_permis' => 'P-345678',
                'chauffeur_tel' => '+223 70 00 00 03',
                'declaration_douane' => 'DEC-003',
                'bureau_douane' => 'Douane Sikasso',
                'status' => 'sous_douane',
                'created_by' => 1,
            ]
        );

        Depotage::updateOrCreate(
            ['numero_depotage' => 'DEP-2025-004'],
            [
                'date_operation' => now()->subDays(30),
                'produit_id' => 2,
                'cuve_destination_id' => 4,
                'volume_brut' => 70000,
                'temperature' => 23.5,
                'volume_corrige' => 70000,
                'fournisseur' => 'Corridor',
                'provenance' => 'Mopti',
                'numero_bon_chargement' => 'BC-004',
                'plaque_imm' => 'ML-101-GH',
                'chauffeur_nom' => 'Aminata Coulibaly',
                'chauffeur_permis' => 'P-456789',
                'chauffeur_tel' => '+223 70 00 00 04',
                'declaration_douane' => 'DEC-004',
                'bureau_douane' => 'Douane Mopti',
                'status' => 'acquitte',
                'created_by' => 1,
            ]
        );

        Depotage::updateOrCreate(
            ['numero_depotage' => 'DEP-2025-005'],
            [
                'date_operation' => now()->subDays(40),
                'produit_id' => 1,
                'cuve_destination_id' => 1,
                'volume_brut' => 50000,
                'temperature' => 25.0,
                'volume_corrige' => 50000,
                'fournisseur' => 'Petro Golf',
                'provenance' => 'Segou',
                'numero_bon_chargement' => 'BC-005',
                'plaque_imm' => 'ML-202-IJ',
                'chauffeur_nom' => 'Moussa Diarra',
                'chauffeur_permis' => 'P-567890',
                'chauffeur_tel' => '+223 70 00 00 05',
                'declaration_douane' => 'DEC-005',
                'bureau_douane' => 'Douane Segou',
                'status' => 'sous_douane',
                'created_by' => 1,
            ]
        );

        // Chargements de test
        Chargement::updateOrCreate(
            ['numero_chargement' => 'CH-2026-001'],
            [
                'date_operation' => now()->subDays(2),
                'produit_id' => 1,
                'cuve_source_id' => 1,
                'volume_brut' => 90000,
                'temperature' => 25.5,
                'volume_corrige' => 90000,
                'client_nom' => 'Petro Bama',
                'client_code' => 'PB001',
                'plaque_imm' => 'ML-123-AB',
                'chauffeur_nom' => 'Amadou Diallo',
                'chauffeur_permis' => 'P-123456',
                'chauffeur_badge' => 'B-001',
                'capacite_camion' => 100000,
                'status' => 'acquitte',
                'created_by' => 1,
            ]
        );

        Chargement::updateOrCreate(
            ['numero_chargement' => 'CH-2026-002'],
            [
                'date_operation' => now()->subDays(9),
                'produit_id' => 2,
                'cuve_source_id' => 2,
                'volume_brut' => 50000,
                'temperature' => 24.0,
                'volume_corrige' => 50000,
                'client_nom' => 'GYF',
                'client_code' => 'GYF001',
                'plaque_imm' => 'ML-456-CD',
                'chauffeur_nom' => 'Fatoumata Traore',
                'chauffeur_permis' => 'P-234567',
                'chauffeur_badge' => 'B-002',
                'capacite_camion' => 60000,
                'status' => 'acquitte',
                'created_by' => 1,
            ]
        );

        Chargement::updateOrCreate(
            ['numero_chargement' => 'CH-2025-003'],
            [
                'date_operation' => now()->subDays(19),
                'produit_id' => 3,
                'cuve_source_id' => 3,
                'volume_brut' => 200000,
                'temperature' => 26.0,
                'volume_corrige' => 200000,
                'client_nom' => 'NDC',
                'client_code' => 'NDC001',
                'plaque_imm' => 'ML-789-EF',
                'chauffeur_nom' => 'Ibrahim Keita',
                'chauffeur_permis' => 'P-345678',
                'chauffeur_badge' => 'B-003',
                'capacite_camion' => 250000,
                'status' => 'annule',
                'created_by' => 1,
            ]
        );

        Chargement::updateOrCreate(
            ['numero_chargement' => 'CH-2025-004'],
            [
                'date_operation' => now()->subDays(29),
                'produit_id' => 2,
                'cuve_source_id' => 4,
                'volume_brut' => 70000,
                'temperature' => 23.5,
                'volume_corrige' => 70000,
                'client_nom' => 'Corridor',
                'client_code' => 'COR001',
                'plaque_imm' => 'ML-101-GH',
                'chauffeur_nom' => 'Aminata Coulibaly',
                'chauffeur_permis' => 'P-456789',
                'chauffeur_badge' => 'B-004',
                'capacite_camion' => 80000,
                'status' => 'acquitte',
                'created_by' => 1,
            ]
        );

        Chargement::updateOrCreate(
            ['numero_chargement' => 'CH-2025-005'],
            [
                'date_operation' => now()->subDays(39),
                'produit_id' => 1,
                'cuve_source_id' => 1,
                'volume_brut' => 50000,
                'temperature' => 25.0,
                'volume_corrige' => 50000,
                'client_nom' => 'Petro Golf',
                'client_code' => 'PG001',
                'plaque_imm' => 'ML-202-IJ',
                'chauffeur_nom' => 'Moussa Diarra',
                'chauffeur_permis' => 'P-567890',
                'chauffeur_badge' => 'B-005',
                'capacite_camion' => 60000,
                'status' => 'annule',
                'created_by' => 1,
            ]
        );

        // Cessions de test
        Cession::create([
            'numero_cession' => 'CES-2026-001',
            'date_cession' => now()->subDays(2),
            'cedant_id' => 1,
            'beneficiaire_id' => 2,
            'produit_id' => 1,
            'cuve_id' => 1,
            'volume' => 90000,
            'volume_corrige' => 90000,
            'temperature' => 25.5,
            'prix_unitaire' => 1.25,
            'montant_total' => 112500.00,
            'status' => 'confirmed',
            'created_by' => 1,
        ]);

        Cession::create([
            'numero_cession' => 'CES-2026-002',
            'date_cession' => now()->subDays(9),
            'cedant_id' => 2,
            'beneficiaire_id' => 1,
            'produit_id' => 2,
            'cuve_id' => 2,
            'volume' => 50000,
            'volume_corrige' => 50000,
            'temperature' => 24.0,
            'prix_unitaire' => 1.15,
            'montant_total' => 57500.00,
            'status' => 'confirmed',
            'created_by' => 1,
        ]);

        Cession::create([
            'numero_cession' => 'CES-2025-003',
            'date_cession' => now()->subDays(19),
            'cedant_id' => 1,
            'beneficiaire_id' => 2,
            'produit_id' => 1,
            'cuve_id' => 1,
            'volume' => 200000,
            'volume_corrige' => 200000,
            'temperature' => 26.0,
            'prix_unitaire' => 1.20,
            'montant_total' => 240000.00,
            'status' => 'confirmed',
            'created_by' => 1,
        ]);

        Cession::create([
            'numero_cession' => 'CES-2025-004',
            'date_cession' => now()->subDays(29),
            'cedant_id' => 2,
            'beneficiaire_id' => 1,
            'produit_id' => 2,
            'cuve_id' => 4,
            'volume' => 70000,
            'volume_corrige' => 70000,
            'temperature' => 23.5,
            'prix_unitaire' => 1.18,
            'montant_total' => 82600.00,
            'status' => 'confirmed',
            'created_by' => 1,
        ]);

        Cession::create([
            'numero_cession' => 'CES-2025-005',
            'date_cession' => now()->subDays(39),
            'cedant_id' => 1,
            'beneficiaire_id' => 2,
            'produit_id' => 1,
            'cuve_id' => 1,
            'volume' => 50000,
            'volume_corrige' => 50000,
            'temperature' => 25.0,
            'prix_unitaire' => 1.22,
            'montant_total' => 61000.00,
            'status' => 'confirmed',
            'created_by' => 1,
        ]);
    }
}
