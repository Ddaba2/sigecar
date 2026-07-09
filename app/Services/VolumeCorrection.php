<?php

namespace App\Services;

class VolumeCorrection
{
    /** Coefficient de dilatation ASTM 54B pour produits pétroliers légers */
    public const COEFFICIENT = 0.00095;
    public const TEMPERATURE_REFERENCE = 15;

    public static function corriger(float $volumeBrut, float $temperature): int
    {
        $correction = 1 + (self::COEFFICIENT * ($temperature - self::TEMPERATURE_REFERENCE));
        return (int) round($volumeBrut / $correction);
    }
}
