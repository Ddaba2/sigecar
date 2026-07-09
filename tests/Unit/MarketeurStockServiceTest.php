<?php

namespace Tests\Unit;

use App\Models\Chargement;
use App\Models\Cession;
use App\Models\Cuve;
use App\Models\Depotage;
use App\Models\MarketeurStock;
use App\Models\Produit;
use App\Models\User;
use App\Services\MarketeurStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class MarketeurStockServiceTest extends TestCase
{
    use RefreshDatabase;

    private MarketeurStockService $service;
    private User $operateur;
    private User $operateur2;
    private Produit $produit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(MarketeurStockService::class);

        $this->operateur = User::create([
            'name'         => 'Op A',
            'email'        => 'opa@test.local',
            'password'     => Hash::make('secret'),
            'role'         => 'marketeur',
            'company_name' => 'Société A',
            'status'       => 'active',
        ]);

        $this->operateur2 = User::create([
            'name'         => 'Op B',
            'email'        => 'opb@test.local',
            'password'     => Hash::make('secret'),
            'role'         => 'marketeur',
            'company_name' => 'Société B',
            'status'       => 'active',
        ]);

        $this->produit = Produit::create([
            'nom'     => 'Essence Test',
            'code'    => 'ESS-UNIT-01',
            'type'    => 'essence',
            'density' => 0.75,
            'unit'    => 'L',
            'status'  => 'active',
        ]);
    }

    public function test_add_cree_un_stock_si_inexistant(): void
    {
        $this->assertSame(0, $this->service->getQuantite($this->operateur->id, $this->produit->id));

        $this->service->add($this->operateur->id, $this->produit->id, 5000);

        $this->assertSame(5000, $this->service->getQuantite($this->operateur->id, $this->produit->id));
    }

    public function test_add_incremente_un_stock_existant(): void
    {
        $this->service->add($this->operateur->id, $this->produit->id, 3000);
        $this->service->add($this->operateur->id, $this->produit->id, 2000);

        $this->assertSame(5000, $this->service->getQuantite($this->operateur->id, $this->produit->id));
    }

    public function test_add_ignore_volume_zero_ou_negatif(): void
    {
        $this->service->add($this->operateur->id, $this->produit->id, 0);
        $this->service->add($this->operateur->id, $this->produit->id, -100);

        $this->assertSame(0, $this->service->getQuantite($this->operateur->id, $this->produit->id));
    }

    public function test_deduct_reduit_le_stock(): void
    {
        $this->service->add($this->operateur->id, $this->produit->id, 10_000);
        $this->service->deduct($this->operateur->id, $this->produit->id, 3_000);

        $this->assertSame(7_000, $this->service->getQuantite($this->operateur->id, $this->produit->id));
    }

    public function test_deduct_leve_exception_si_stock_insuffisant(): void
    {
        $this->service->add($this->operateur->id, $this->produit->id, 1_000);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Stock insuffisant/');

        $this->service->deduct($this->operateur->id, $this->produit->id, 5_000);
    }

    public function test_deduct_leve_exception_si_stock_inexistant(): void
    {
        $this->expectException(RuntimeException::class);

        $this->service->deduct($this->operateur->id, $this->produit->id, 100);
    }

    public function test_transfer_deplace_stock_entre_operateurs(): void
    {
        $this->service->add($this->operateur->id, $this->produit->id, 10_000);

        $this->service->transfer(
            $this->operateur->id,
            $this->operateur2->id,
            $this->produit->id,
            4_000
        );

        $this->assertSame(6_000, $this->service->getQuantite($this->operateur->id, $this->produit->id));
        $this->assertSame(4_000, $this->service->getQuantite($this->operateur2->id, $this->produit->id));
    }

    public function test_transfer_leve_exception_si_cedant_insuffisant(): void
    {
        $this->service->add($this->operateur->id, $this->produit->id, 500);

        $this->expectException(RuntimeException::class);

        $this->service->transfer($this->operateur->id, $this->operateur2->id, $this->produit->id, 1_000);
    }

    public function test_credit_from_depotage_credite_operateur(): void
    {
        $cuve = Cuve::create([
            'code' => 'BAC-UNIT-01', 'nom' => 'BAC-UNIT-01',
            'produit_id' => $this->produit->id,
            'capacite_totale' => 100_000, 'niveau_actuel' => 0,
            'seuil_alerte_bas' => 1_000, 'seuil_alerte_haut' => 95_000,
            'status' => 'operationnel', 'type_douane' => 'acquitte',
        ]);

        $gestionnaire = User::create([
            'name' => 'Gest', 'email' => 'gest@test.local',
            'password' => Hash::make('s'), 'role' => 'gestionnaire', 'status' => 'active',
        ]);

        $depotage = Depotage::create([
            'numero_depotage' => 'DEP-UNIT-01',
            'date_operation'  => now(),
            'produit_id'      => $this->produit->id,
            'cuve_destination_id' => $cuve->id,
            'volume_brut'     => 10_000,
            'volume_corrige'  => 9_950,
            'temperature'     => 15,
            'fournisseur'     => 'Société A',
            'user_id'         => $this->operateur->id,
            'provenance'      => 'Test',
            'plaque_imm'      => 'AA-0000',
            'chauffeur_nom'   => 'Driver',
            'chauffeur_permis' => 'P-000',
            'status'          => 'acquitte',
            'created_by'      => $gestionnaire->id,
        ]);

        $this->service->creditFromDepotage($depotage);

        $this->assertSame(9_950, $this->service->getQuantite($this->operateur->id, $this->produit->id));
    }

    public function test_rebuild_from_operations_recalcule_les_stocks(): void
    {
        // Seed initial (sera supprimé par rebuildFromOperations)
        MarketeurStock::create([
            'user_id'    => $this->operateur->id,
            'produit_id' => $this->produit->id,
            'quantite'   => 99_999,
        ]);

        // Les opérations réelles (aucune ici) doivent aboutir à 0
        $this->service->rebuildFromOperations();

        $this->assertSame(0, $this->service->getQuantite($this->operateur->id, $this->produit->id));
    }

    public function test_for_user_retourne_uniquement_stocks_positifs(): void
    {
        $this->service->add($this->operateur->id, $this->produit->id, 5_000);

        // Crée un second produit avec stock 0 (ne doit pas apparaître)
        $produit2 = Produit::create([
            'nom' => 'Gasoil Test', 'code' => 'GAS-UNIT-01', 'type' => 'gasoil',
            'density' => 0.84, 'unit' => 'L', 'status' => 'active',
        ]);
        MarketeurStock::create(['user_id' => $this->operateur->id, 'produit_id' => $produit2->id, 'quantite' => 0]);

        $stocks = $this->service->forUser($this->operateur->id);

        $this->assertCount(1, $stocks);
        $this->assertSame($this->produit->id, $stocks->first()->produit_id);
    }
}
