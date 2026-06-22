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
}
