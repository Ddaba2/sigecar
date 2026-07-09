<?php

namespace App\Http\Controllers\Concerns;

trait FiltersOperations
{
    protected function applyDateFilter($query, string $column, string $periode): void
    {
        $days = match ($periode) {
            '7'  => 7,
            '30' => 30,
            '90' => 90,
            default => null,
        };

        if ($days) {
            $query->where($column, '>=', now()->subDays($days));
        }
    }

    protected function applyDateRangeFilter($query, string $column, ?string $debut, ?string $fin): void
    {
        if ($debut) {
            $query->whereDate($column, '>=', $debut);
        }
        if ($fin) {
            $query->whereDate($column, '<=', $fin);
        }
    }
}
