<?php

namespace App\Services;

class GeoService
{
    /**
     * Great-circle distance between two coordinates, in kilometers.
     * Returns null if any coordinate is missing.
     */
    public static function distanceInKm($lat1, $lng1, $lat2, $lng2): ?float
    {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return null;
        }

        $earthRadiusKm = 6371;

        $latDelta = deg2rad((float) $lat2 - (float) $lat1);
        $lngDelta = deg2rad((float) $lng2 - (float) $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad((float) $lat1)) * cos(deg2rad((float) $lat2)) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }
}
