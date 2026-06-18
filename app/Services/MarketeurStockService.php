<?php

namespace App\Services;

use App\Models\Cession;
use App\Models\Chargement;
use App\Models\Cuve;
use App\Models\Depotage;
use App\Models\MarketeurStock;
use App\Models\User;
use RuntimeException;

class MarketeurStockService
{
    public function findOperatorByName(string $companyName): ?User
    {
        $name = trim($companyName);

        if ($name === '') {
            return null;
        }

        return User::query()
            ->where('role', 'marketeur')
            ->where(function ($q) use ($name) {
                $q->whereRaw('LOWER(TRIM(company_name)) = ?', [mb_strtolower($name)])
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)]);
            })
            ->first();
    }

    public function resolveUserId(?int $userId, ?string $companyName): ?int
    {
        if ($userId) {
            return $userId;
        }

        return $this->findOperatorByName($companyName ?? '')?->id;
    }

    public function getQuantite(int $userId, int $produitId): int
    {
        return (int) MarketeurStock::query()
            ->where('user_id', $userId)
            ->where('produit_id', $produitId)
            ->value('quantite');
    }

    public function add(int $userId, int $produitId, int $volume): void
    {
        if ($volume <= 0) {
            return;
        }

        $stock = MarketeurStock::query()->firstOrCreate(
            ['user_id' => $userId, 'produit_id' => $produitId],
            ['quantite' => 0]
        );

        $stock->increment('quantite', $volume);
    }

    public function deduct(int $userId, int $produitId, int $volume): void
    {
        if ($volume <= 0) {
            return;
        }

        $stock = MarketeurStock::query()
            ->where('user_id', $userId)
            ->where('produit_id', $produitId)
            ->lockForUpdate()
            ->first();

        $available = $stock?->quantite ?? 0;

        if ($available < $volume) {
            $user = User::find($userId);
            $name = $user?->operatorName() ?? 'Opérateur';
            throw new RuntimeException("Stock insuffisant pour {$name} ({$available} L disponibles).");
        }

        $stock->decrement('quantite', $volume);
    }

    public function transfer(int $cedantUserId, int $beneficiaireUserId, int $produitId, int $volume): void
    {
        $this->deduct($cedantUserId, $produitId, $volume);
        $this->add($beneficiaireUserId, $produitId, $volume);
    }

    public function creditFromDepotage(Depotage $depotage): void
    {
        $userId = $this->resolveUserId($depotage->user_id, $depotage->fournisseur);

        if ($userId) {
            $this->add($userId, $depotage->produit_id, $depotage->volume_corrige);
        }
    }

    public function debitFromChargement(Chargement $chargement): void
    {
        $userId = $this->resolveUserId($chargement->user_id, $chargement->client_nom);

        if (! $userId && $chargement->cuve_source_id) {
            $userId = Cuve::whereKey($chargement->cuve_source_id)->value('user_id');
        }

        if ($userId) {
            $this->deduct($userId, $chargement->produit_id, $chargement->volume_corrige);
        }
    }

    public function allGroupedByOperator()
    {
        return MarketeurStock::query()
            ->with(['user', 'produit'])
            ->where('quantite', '>', 0)
            ->orderBy('user_id')
            ->orderBy('produit_id')
            ->get()
            ->groupBy(fn ($row) => $row->user?->operatorName() ?? '—');
    }

    public function forUser(int $userId)
    {
        return MarketeurStock::query()
            ->with('produit')
            ->where('user_id', $userId)
            ->where('quantite', '>', 0)
            ->get();
    }

    public function rebuildFromOperations(): void
    {
        MarketeurStock::query()->delete();

        Depotage::query()
            ->whereNotIn('status', ['annule'])
            ->orderBy('date_operation')
            ->each(fn (Depotage $d) => $this->creditFromDepotage($d));

        Chargement::query()
            ->whereNotIn('status', ['annule'])
            ->orderBy('date_operation')
            ->each(function (Chargement $c) {
                try {
                    $this->debitFromChargement($c);
                } catch (RuntimeException) {
                    // Historique incohérent.
                }
            });

        Cession::query()
            ->whereNotIn('status', ['cancelled', 'annule'])
            ->orderBy('date_cession')
            ->each(function (Cession $c) {
                try {
                    $this->transfer($c->cedant_id, $c->beneficiaire_id, $c->produit_id, $c->volume_corrige);
                } catch (RuntimeException) {
                    // Historique incohérent.
                }
            });
    }

    public function allocateUnassignedCuveStock(): void
    {
        $cuves = Cuve::query()
            ->whereNotNull('user_id')
            ->where('niveau_actuel', '>', 0)
            ->get()
            ->groupBy(fn (Cuve $c) => $c->user_id . '-' . $c->produit_id);

        foreach ($cuves as $group) {
            $first = $group->first();
            $userId = (int) $first->user_id;
            $produitId = (int) $first->produit_id;
            $physicalTotal = (int) $group->sum('niveau_actuel');
            $currentStock = $this->getQuantite($userId, $produitId);

            if ($physicalTotal > $currentStock) {
                $this->add($userId, $produitId, $physicalTotal - $currentStock);
            }
        }
    }

    public function syncAll(): void
    {
        $this->rebuildFromOperations();
        $this->allocateUnassignedCuveStock();
    }

    public function backfillOperationUserIds(): void
    {
        Depotage::query()
            ->whereNull('user_id')
            ->each(function (Depotage $depotage) {
                $user = $this->findOperatorByName($depotage->fournisseur);
                if ($user) {
                    $depotage->update(['user_id' => $user->id]);
                }
            });

        Chargement::query()
            ->whereNull('user_id')
            ->each(function (Chargement $chargement) {
                $user = $this->findOperatorByName($chargement->client_nom);
                if ($user) {
                    $chargement->update(['user_id' => $user->id]);
                }
            });
    }
}
