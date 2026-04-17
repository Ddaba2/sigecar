<?php

namespace Tests\Feature;

use App\Models\Chargement;
use App\Models\Cession;
use App\Models\Cuve;
use App\Models\Depotage;
use App\Models\Marketeur;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GestionnaireFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $gestionnaire;

    private Produit $produitEssence;

    private Cuve $cuveEssence;

    private Marketeur $cedant;

    private Marketeur $beneficiaire;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-04-13 10:30:00');

        $this->gestionnaire = User::create([
            'name' => 'Gestionnaire Test',
            'email' => 'gest@test.local',
            'password' => Hash::make('secret123'),
            'role' => 'gestionnaire',
            'status' => 'active',
        ]);

        $this->produitEssence = Produit::create([
            'nom' => 'Essence Super',
            'code' => 'ESS-TEST-01',
            'type' => 'essence',
            'density' => 0.75,
            'unit' => 'L',
            'status' => 'active',
        ]);

        $produitGasoil = Produit::create([
            'nom' => 'Gasoil Test',
            'code' => 'GAS-TEST-01',
            'type' => 'gasoil',
            'density' => 0.84,
            'unit' => 'L',
            'status' => 'active',
        ]);

        Produit::create([
            'nom' => 'Jet A1 Test',
            'code' => 'JET-TEST-01',
            'type' => 'jet_a1',
            'density' => 0.8,
            'unit' => 'L',
            'status' => 'active',
        ]);

        $this->cuveEssence = Cuve::create([
            'code' => 'BAC-TEST-01',
            'nom' => 'BAC-TEST-01',
            'produit_id' => $this->produitEssence->id,
            'capacite_totale' => 500_000,
            'niveau_actuel' => 200_000,
            'seuil_alerte_bas' => 10_000,
            'seuil_alerte_haut' => 480_000,
            'status' => 'operationnel',
            'type_douane' => 'acquitte',
        ]);

        Cuve::create([
            'code' => 'BAC-TEST-02',
            'nom' => 'BAC-TEST-02',
            'produit_id' => $produitGasoil->id,
            'capacite_totale' => 400_000,
            'niveau_actuel' => 100_000,
            'seuil_alerte_bas' => 10_000,
            'seuil_alerte_haut' => 380_000,
            'status' => 'operationnel',
            'type_douane' => 'sous_douane',
        ]);

        $this->cedant = Marketeur::create([
            'company_name' => 'Cédant SA',
            'company_registration' => 'RC-1',
            'status' => 'active',
        ]);

        $this->beneficiaire = Marketeur::create([
            'company_name' => 'Bénéficiaire SA',
            'company_registration' => 'RC-2',
            'status' => 'active',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_guest_is_redirected_from_gestionnaire_routes(): void
    {
        $this->get('/gestionnaire')->assertRedirect(route('login'));
        $this->get('/gestionnaire/rapports/export/csv')->assertRedirect(route('login'));
    }

    public function test_produit_name_accessor_reads_nom(): void
    {
        $this->assertSame('Essence Super', $this->produitEssence->name);
    }

    public function test_gestionnaire_can_open_all_main_pages(): void
    {
        $this->actingAs($this->gestionnaire);

        $this->get('/gestionnaire')->assertOk()->assertSee('BAC-TEST-01');
        $this->get('/gestionnaire/stocks')->assertOk();
        $this->get('/gestionnaire/stocks/tous')->assertOk();
        $this->get('/gestionnaire/operations')->assertOk();
        $this->get('/gestionnaire/depotage/create')->assertOk();
        $this->get('/gestionnaire/chargement/create')->assertOk();
        $this->get('/gestionnaire/cession/create')->assertOk();
        $this->get('/gestionnaire/rapports')->assertOk();
        $this->get('/gestionnaire/parametres')->assertOk();
    }

    public function test_store_depotage_updates_cuve_and_lists_in_operations(): void
    {
        $niveauAvant = $this->cuveEssence->fresh()->niveau_actuel;

        $response = $this->actingAs($this->gestionnaire)->post('/gestionnaire/depotage', [
            'date_operation' => '2026-04-13T11:00',
            'produit_id' => $this->produitEssence->id,
            'cuve_destination_id' => $this->cuveEssence->id,
            'volume_brut' => 10_000,
            'temperature' => 15,
            'fournisseur' => 'Fournisseur DB',
            'provenance' => 'Niger',
            'numero_bon_chargement' => 'PO-2026-001',
            'plaque_imm' => 'AA-1234-XX',
            'chauffeur_nom' => 'Ibrahim TEST',
            'chauffeur_permis' => 'P-999',
            'chauffeur_tel' => '+22370000000',
            'declaration_douane' => 'DC-1',
            'bureau_douane' => 'Faladiè',
            'creux' => [
                [
                    'numero' => 1,
                    'capacite' => 10_000,
                    'produit_id' => '',
                ],
            ],
        ]);

        $response->assertRedirect(route('gestionnaire.operations'));
        $this->assertDatabaseHas('depotages', [
            'fournisseur' => 'Fournisseur DB',
            'volume_brut' => 10_000,
            'created_by' => $this->gestionnaire->id,
        ]);

        $depotage = Depotage::first();
        $this->assertNotNull($depotage->document_pdf);

        $this->cuveEssence->refresh();
        $this->assertGreaterThan($niveauAvant, $this->cuveEssence->niveau_actuel);

        $this->actingAs($this->gestionnaire)->get('/gestionnaire/operations')
            ->assertSee('Fournisseur DB')
            ->assertSee('Essence Super');
    }

    public function test_store_chargement_decreases_cuve_stock(): void
    {
        $this->cuveEssence->update(['niveau_actuel' => 300_000]);
        $avant = 300_000;

        $response = $this->actingAs($this->gestionnaire)->post('/gestionnaire/chargement', [
            'date_operation' => '2026-04-13T12:00',
            'produit_id' => $this->produitEssence->id,
            'cuve_source_id' => $this->cuveEssence->id,
            'volume_brut' => 5_000,
            'temperature' => 15,
            'client_nom' => 'Client DB',
            'client_code' => 'CLI-01',
            'plaque_imm' => 'BB-0000-YY',
            'chauffeur_nom' => 'Chauffeur DB',
            'chauffeur_permis' => 'P-888',
            'capacite_camion' => 45_000,
        ]);

        $response->assertRedirect(route('gestionnaire.operations'));
        $this->assertDatabaseHas('chargements', [
            'client_nom' => 'Client DB',
            'volume_brut' => 5_000,
            'created_by' => $this->gestionnaire->id,
        ]);

        $this->cuveEssence->refresh();
        $this->assertLessThan($avant, $this->cuveEssence->niveau_actuel);

        $this->actingAs($this->gestionnaire)->get('/gestionnaire/rapports?date=2026-04-13')->assertSee('Client DB');
    }

    public function test_store_cession_persists_and_pdf_exists(): void
    {
        $response = $this->actingAs($this->gestionnaire)->post('/gestionnaire/cession', [
            'date_cession' => '2026-04-13T14:00',
            'cedant_id' => $this->cedant->id,
            'beneficiaire_id' => $this->beneficiaire->id,
            'produit_id' => $this->produitEssence->id,
            'cuve_id' => $this->cuveEssence->id,
            'volume' => 1_000,
            'temperature' => 15,
        ]);

        $response->assertRedirect(route('gestionnaire.operations'));
        $this->assertDatabaseHas('cessions', [
            'cedant_id' => $this->cedant->id,
            'beneficiaire_id' => $this->beneficiaire->id,
            'volume' => 1_000,
        ]);

        $cession = Cession::first();
        $this->assertNotNull($cession->document_pdf);
    }

    public function test_rapport_csv_contains_database_rows(): void
    {
        Depotage::create([
            'numero_depotage' => 'DEP-RPT-1',
            'date_operation' => Carbon::parse('2026-04-13 08:00'),
            'produit_id' => $this->produitEssence->id,
            'cuve_destination_id' => $this->cuveEssence->id,
            'volume_brut' => 2_000,
            'temperature' => 15,
            'volume_corrige' => 2_000,
            'fournisseur' => 'CSV-Fournisseur',
            'provenance' => 'Mali',
            'plaque_imm' => 'XX',
            'chauffeur_nom' => 'X',
            'chauffeur_permis' => 'Y',
            'status' => 'sous_douane',
            'created_by' => $this->gestionnaire->id,
        ]);

        ob_start();
        $response = $this->actingAs($this->gestionnaire)
            ->get('/gestionnaire/rapports/export/csv?date=2026-04-13');
        $response->assertOk();
        $response->assertHeader('content-disposition');
        $response->sendContent();
        $csv = ob_get_clean() ?: '';
        $this->assertStringContainsString('CSV-Fournisseur', $csv);
    }

    public function test_rapport_pdf_download_succeeds(): void
    {
        $this->actingAs($this->gestionnaire)
            ->get('/gestionnaire/rapports/export/pdf?date=2026-04-13')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_rapports_page_shows_volumes_from_database(): void
    {
        Depotage::create([
            'numero_depotage' => 'DEP-RPT-2',
            'date_operation' => Carbon::parse('2026-04-13 09:00'),
            'produit_id' => $this->produitEssence->id,
            'cuve_destination_id' => $this->cuveEssence->id,
            'volume_brut' => 3_000,
            'temperature' => 15,
            'volume_corrige' => 3_000,
            'fournisseur' => 'RptF',
            'provenance' => 'X',
            'plaque_imm' => 'XX',
            'chauffeur_nom' => 'X',
            'chauffeur_permis' => 'Y',
            'status' => 'sous_douane',
            'created_by' => $this->gestionnaire->id,
        ]);

        Chargement::create([
            'numero_chargement' => 'CHG-RPT-1',
            'date_operation' => Carbon::parse('2026-04-13 10:00'),
            'produit_id' => $this->produitEssence->id,
            'cuve_source_id' => $this->cuveEssence->id,
            'volume_brut' => 1_000,
            'temperature' => 15,
            'volume_corrige' => 1_000,
            'client_nom' => 'RptClient',
            'plaque_imm' => 'ZZ',
            'chauffeur_nom' => 'Z',
            'chauffeur_permis' => 'Z',
            'capacite_camion' => 10_000,
            'status' => 'acquitte',
            'created_by' => $this->gestionnaire->id,
        ]);

        $this->actingAs($this->gestionnaire)
            ->get('/gestionnaire/rapports?date=2026-04-13')
            ->assertOk()
            ->assertSee('3 000', false)
            ->assertSee('1 000', false)
            ->assertSee('RptF', false)
            ->assertSee('RptClient', false);
    }

    public function test_stocks_tous_page_lists_all_cuves(): void
    {
        $this->actingAs($this->gestionnaire)
            ->get('/gestionnaire/stocks/tous')
            ->assertOk()
            ->assertSee('BAC-TEST-01')
            ->assertSee('BAC-TEST-02');
    }

    public function test_acquitter_depotage_sets_acquitte_and_cuve_when_last_pending(): void
    {
        $this->cuveEssence->update(['type_douane' => 'sous_douane']);

        $dep = Depotage::create([
            'numero_depotage' => 'DEP-ACQ-1',
            'date_operation' => now(),
            'produit_id' => $this->produitEssence->id,
            'cuve_destination_id' => $this->cuveEssence->id,
            'volume_brut' => 1_000,
            'temperature' => 15,
            'volume_corrige' => 1_000,
            'fournisseur' => 'F',
            'provenance' => 'P',
            'plaque_imm' => 'X',
            'chauffeur_nom' => 'N',
            'chauffeur_permis' => 'P',
            'status' => 'sous_douane',
            'created_by' => $this->gestionnaire->id,
        ]);

        $this->actingAs($this->gestionnaire)
            ->post(route('gestionnaire.depotage.acquitter', $dep))
            ->assertRedirect();

        $dep->refresh();
        $this->assertSame('acquitte', $dep->status);
        $this->assertSame('acquitte', $this->cuveEssence->fresh()->type_douane);
    }

    public function test_cannot_acquitter_depotage_twice(): void
    {
        $dep = Depotage::create([
            'numero_depotage' => 'DEP-ACQ-2',
            'date_operation' => now(),
            'produit_id' => $this->produitEssence->id,
            'cuve_destination_id' => $this->cuveEssence->id,
            'volume_brut' => 500,
            'temperature' => 15,
            'volume_corrige' => 500,
            'fournisseur' => 'F2',
            'provenance' => 'P2',
            'plaque_imm' => 'X2',
            'chauffeur_nom' => 'N2',
            'chauffeur_permis' => 'P2',
            'status' => 'sous_douane',
            'created_by' => $this->gestionnaire->id,
        ]);

        $this->actingAs($this->gestionnaire)->post(route('gestionnaire.depotage.acquitter', $dep))->assertRedirect();
        $this->actingAs($this->gestionnaire)
            ->post(route('gestionnaire.depotage.acquitter', $dep))
            ->assertSessionHas('error');
    }

    public function test_cuve_stays_sous_douane_if_another_depotage_pending(): void
    {
        $this->cuveEssence->update(['type_douane' => 'sous_douane']);

        $d1 = Depotage::create([
            'numero_depotage' => 'DEP-M1',
            'date_operation' => now(),
            'produit_id' => $this->produitEssence->id,
            'cuve_destination_id' => $this->cuveEssence->id,
            'volume_brut' => 100,
            'temperature' => 15,
            'volume_corrige' => 100,
            'fournisseur' => 'A',
            'provenance' => 'X',
            'plaque_imm' => 'X',
            'chauffeur_nom' => 'N',
            'chauffeur_permis' => 'P',
            'status' => 'sous_douane',
            'created_by' => $this->gestionnaire->id,
        ]);

        $d2 = Depotage::create([
            'numero_depotage' => 'DEP-M2',
            'date_operation' => now(),
            'produit_id' => $this->produitEssence->id,
            'cuve_destination_id' => $this->cuveEssence->id,
            'volume_brut' => 200,
            'temperature' => 15,
            'volume_corrige' => 200,
            'fournisseur' => 'B',
            'provenance' => 'X',
            'plaque_imm' => 'X',
            'chauffeur_nom' => 'N',
            'chauffeur_permis' => 'P',
            'status' => 'sous_douane',
            'created_by' => $this->gestionnaire->id,
        ]);

        $this->actingAs($this->gestionnaire)->post(route('gestionnaire.depotage.acquitter', $d1))->assertRedirect();

        $this->assertSame('sous_douane', $this->cuveEssence->fresh()->type_douane);

        $this->actingAs($this->gestionnaire)->post(route('gestionnaire.depotage.acquitter', $d2))->assertRedirect();

        $this->assertSame('acquitte', $this->cuveEssence->fresh()->type_douane);
    }
}
