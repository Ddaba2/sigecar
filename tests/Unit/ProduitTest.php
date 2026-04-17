<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Produit;
use App\Models\Depotage;
use App\Models\Chargement;
use App\Models\Stock;
use Tests\DatabaseTesting;

class ProduitTest extends TestCase
{
    use DatabaseTesting;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $produit = Produit::create([
            'nom' => 'Essence Super',
            'code' => 'SUP-95',
            'type' => 'essence',
            'density' => 0.7500,
            'unit' => 'L',
            'status' => 'active'
        ]);

        $this->assertEquals('Essence Super', $produit->nom);
        $this->assertEquals('SUP-95', $produit->code);
        $this->assertEquals('essence', $produit->type);
        $this->assertEquals(0.7500, $produit->density);
        $this->assertEquals('L', $produit->unit);
        $this->assertEquals('active', $produit->status);
    }

    /** @test */
    public function it_has_many_depotages()
    {
        $produit = Produit::factory()->create();

        Depotage::factory()->create(['produit_id' => $produit->id]);
        Depotage::factory()->create(['produit_id' => $produit->id]);

        $this->assertCount(2, $produit->depotages);
    }

    /** @test */
    public function it_has_many_chargements()
    {
        $produit = Produit::factory()->create();

        Chargement::factory()->create(['produit_id' => $produit->id]);
        Chargement::factory()->create(['produit_id' => $produit->id]);

        $this->assertCount(2, $produit->chargements);
    }

    /** @test */
    public function it_has_many_stocks()
    {
        $produit = Produit::factory()->create();

        Stock::factory()->create(['produit_id' => $produit->id]);
        Stock::factory()->create(['produit_id' => $produit->id]);

        $this->assertCount(2, $produit->stocks);
    }
}