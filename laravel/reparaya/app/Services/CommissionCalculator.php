<?php
declare(strict_types=1);

namespace App\Services;

/** Único punto de cálculo de commissions (admin y gestora tienen que ver las mismas cifras) 
 * Trabaja en enteros (céntimos) para evitar errores de coma flotante.
*/
final class CommissionCalculator
{
    /**
     * @param string $basePrice Precio base del servicio, p.ej. "35.00"
     * @param string $commissionRate Porcentaje de la gestora, p.ej. "12.50" (= 12,5 %)
     * @return string Importe redondeado a 2 decimales (medio céntimo arriba) p.ej. "4.38"
     */
    public static function amount(string $basePrice, string $commissionRate): string
    {
        $priceCents = (int) round((float) $basePrice * 100);
        $rateHundredths = (int) round((float) $commissionRate * 100);
        $amountCents = intdiv($priceCents * $rateHundredths + 5_000, 10_000);

        return number_format($amountCents / 100, 2, '.', '');
    }
    
}
