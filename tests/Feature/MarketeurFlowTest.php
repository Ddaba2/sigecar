<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\DatabaseTesting;
use App\Models\User;
use App\Models\Marketeur;
use App\Models\Produit;
use App\Models\Cuve;
use App\Models\Depotage;
use App\Models\Chargement;
use App\Models\Cession;
use Illuminate\Support\Facades\Hash;

class MarketeurFlowTest extends TestCase
{
    use DatabaseTesting;

    /** @test */
    public function marketeur_can_access_dashboard_operations_and_cessions_with_data()
    {
        $user = User::create([
            'name' => 'Marketeur Test',
            'email' => 'marketeur-test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'marketeur',
            'status' => 'active',
            'company_name' => 'Petro Test',
        ]);

        $marketeur = Marketeur::create([
            'user_id' => $user->id,
            'company_name' => 'Petro Test',
            'company_registration' => 'MT-001',
            'address' => 'Bamako',
            'telephone' => '+22370000000',
            'contact_person' => 'Hamadou',
            'status' => 'active',
        ]);

        $produit = Produit::create([
            'nom' => 'Essence',
            'code' => 'ESS-95',
            'type' => 'essence',
            'density' => 0.7400,
            'unit' => 'L',
            'status' => 'active',
        ]);

        $cuve = Cuve::create([
            'code' => 'BAC-01',
            'nom' => 'Cuve 1',
            'produit_id' => $produit->id,
            'capacite_totale' => 300000,
            'niveau_actuel' => 225000,
            'seuil_alerte_bas' => 50000,
            'seuil_alerte_haut' => 290000,
            'status' => 'operationnel',
            'type_douane' => 'acquitte',
        ]);

        Depotage::create([
            'numero_depotage' => 'DEP-0001',
            'date_operation' => now(),
            'produit_id' => $produit->id,
            'cuve_destination_id' => $cuve->id,
            'volume_brut' => 90000,
            'temperature' => 15.0,
            'volume_corrige' => 90000,
            'fournisseur' => 'Petro Bama',
            'provenance' => 'Bamako',
            'numero_bon_chargement' => 'BLC-0001',
            'plaque_imm' => 'ABC-123-ML',
            'chauffeur_nom' => 'Amadou Diallo',
            'chauffeur_permis' => 'PERM-123',
            'chauffeur_tel' => '+22370000001',
            'declaration_douane' => 'DEC-001',
            'bureau_douane' => 'Douane Bamako',
            'status' => 'termine',
            'created_by' => $user->id,
        ]);

        Chargement::create([
            'numero_chargement' => 'CHG-0001',
            'date_operation' => now(),
            'produit_id' => $produit->id,
            'cuve_source_id' => $cuve->id,
            'volume_brut' => 32500,
            'temperature' => 15.0,
            'volume_corrige' => 32500,
            'client_nom' => 'Petro Test',
            'client_code' => 'PT-001',
            'plaque_imm' => 'DEF-456-ML',
            'chauffeur_nom' => 'Fatoumata Traore',
            'chauffeur_permis' => 'PERM-456',
            'chauffeur_badge' => 'BADGE-001',
            'capacite_camion' => 40000,
            'status' => 'termine',
            'created_by' => $user->id,
        ]);

        $cession = Cession::create([
            'numero_cession' => 'CES-20260414-0001',
            'date_cession' => now(),
            'cedant_id' => $marketeur->id,
            'beneficiaire_id' => $marketeur->id,
            'produit_id' => $produit->id,
            'cuve_id' => $cuve->id,
            'volume' => 50000,
            'volume_corrige' => 50000,
            'temperature' => 15.00,
            'prix_unitaire' => 650,
            'montant_total' => 32500000,
            'status' => 'confirmed',
            'created_by' => $user->id,
        ]);

        $dashboardResponse = $this->actingAs($user)->get('/marketeur');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Tableau de bord');
        $dashboardResponse->assertSee('Niveaux de stock par produit');

        $operationsResponse = $this->actingAs($user)->get('/marketeur/operations');
        $operationsResponse->assertStatus(200);
        $operationsResponse->assertSee('Historiques des dépôtages');
        $operationsResponse->assertSee('Historiques des chargements');

        $cessionsResponse = $this->actingAs($user)->get('/marketeur/cessions');
        $cessionsResponse->assertStatus(200);
        $cessionsResponse->assertSee('Cessions envoyées');
        $cessionsResponse->assertSee('Cessions reçues');

        $detailResponse = $this->actingAs($user)->get('/marketeur/cessions/' . $cession->id);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Détail de la cession');
        $detailResponse->assertSee('Petro Test');
    }
}
