<?php
/**
 * VatHelper
 * All VAT math for Dine with Din POS.
 *
 * Rules (BIR RR 16-2005 / TRAIN Law):
 *  - Prices are VAT-inclusive (gross).
 *  - VAT-exclusive (net)  = gross / 1.12
 *  - VAT amount           = gross - net   (= gross × 12/112)
 *  - Senior/PWD discount  = remove VAT first, apply 20% on the
 *    VAT-exclusive amount, and keep the final sale VAT-exempt.
 *
 * Always call VatHelper::isVatRegistered() before computing VAT.
 * Never hardcode the VAT rate — read it from business_info.
 */

require_once __DIR__ . '/../../config/db.php';

class VatHelper
{
    /** Cached business_info row to avoid repeated DB hits per request. */
    private static ?array $info = null;

    // ------------------------------------------------------------------
    // Config loader
    // ------------------------------------------------------------------

    /**
     * Load (and cache) the business_info row.
     * Falls back to safe defaults if the table is empty.
     */
    private static function loadInfo(PDO $pdo): array
    {
        if (self::$info !== null) {
            return self::$info;
        }

        $stmt = $pdo->query("SELECT * FROM business_info WHERE info_id = 1 LIMIT 1");
        $row  = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

        self::$info = $row ?: [
            'vat_registered'      => 0,
            'vat_rate'            => 12.00,
            'min_pwd_discount'    => 20.00,
            'min_senior_discount' => 20.00,
        ];

        return self::$info;
    }

    /**
     * Force-reload the cache (call after saving BIR settings).
     */
    public static function resetCache(): void
    {
        self::$info = null;
    }

    // ------------------------------------------------------------------
    // Status checks
    // ------------------------------------------------------------------

    /**
     * Returns true if the business is VAT-registered.
     */
    public static function isVatRegistered(PDO $pdo): bool
    {
        return (bool) self::loadInfo($pdo)['vat_registered'];
    }

    /**
     * Returns the configured VAT rate (e.g. 12.00).
     */
    public static function getVatRate(PDO $pdo): float
    {
        return (float) self::loadInfo($pdo)['vat_rate'];
    }

    // ------------------------------------------------------------------
    // Core VAT math
    // ------------------------------------------------------------------

    /**
     * Break down a VAT-inclusive gross amount.
     *
     * Returns:
     *   vatable_sales  — VAT-exclusive amount (gross / 1+rate)
     *   vat_amount     — VAT portion (gross - vatable_sales)
     *   gross_amount   — original gross (unchanged)
     *
     * If not VAT-registered, all three reflect a zero-VAT breakdown.
     *
     * @param  float $grossAmount  VAT-inclusive price.
     * @param  PDO   $pdo
     * @return array
     */
    public static function breakdown(float $grossAmount, PDO $pdo): array
    {
        if (!self::isVatRegistered($pdo)) {
            return [
                'gross_amount'  => round($grossAmount, 2),
                'vatable_sales' => 0.00,
                'vat_amount'    => 0.00,
                'vat_exempt'    => round($grossAmount, 2),
                'zero_rated'    => 0.00,
            ];
        }

        $rate          = self::getVatRate($pdo) / 100;          // e.g. 0.12
        $divisor       = 1 + $rate;                              // 1.12
        $vatableSales  = round($grossAmount / $divisor, 2);
        $vatAmount     = round($grossAmount - $vatableSales, 2);

        return [
            'gross_amount'  => round($grossAmount, 2),
            'vatable_sales' => $vatableSales,
            'vat_amount'    => $vatAmount,
            'vat_exempt'    => 0.00,
            'zero_rated'    => 0.00,
        ];
    }

    /**
     * Break down a final order total into the values stored on orders.
     *
     * Senior/PWD totals are VAT-exempt after the VAT removal and discount,
     * so no VAT is added back to the final amount.
     *
     * @param  float  $grossTotal
     * @param  PDO    $pdo
     * @param  string $discountType
     * @return array
     */
    public static function orderBreakdown(float $grossTotal, PDO $pdo, string $discountType = 'none'): array
    {
        $grossTotal = round($grossTotal, 2);

        if (!self::isVatRegistered($pdo)) {
            return [
                'vat_amount'        => 0.00,
                'vat_exempt_amount' => $grossTotal,
                'zero_rated_amount' => 0.00,
                'vatable_sales'     => 0.00,
            ];
        }

        if (in_array($discountType, ['senior', 'pwd'], true)) {
            return [
                'vat_amount'        => 0.00,
                'vat_exempt_amount' => $grossTotal,
                'zero_rated_amount' => 0.00,
                'vatable_sales'     => 0.00,
            ];
        }

        $bd = self::breakdown($grossTotal, $pdo);

        return [
            'vat_amount'        => $bd['vat_amount'],
            'vat_exempt_amount' => $bd['vat_exempt'],
            'zero_rated_amount' => $bd['zero_rated'],
            'vatable_sales'     => $bd['vatable_sales'],
        ];
    }

    // ------------------------------------------------------------------
    // Senior / PWD discount (BIR RA 9994 / RR 1-2009)
    // ------------------------------------------------------------------

    /**
     * Compute Senior Citizen or PWD discount.
     *
     * BIR rule:
     *  1. Strip VAT from the tagged price to get the VAT-exclusive amount.
     *  2. Apply discount % on that VAT-exclusive amount.
     *  3. The final amount becomes VAT-exempt. Do NOT add VAT back.
     *
     * For non-VAT businesses the discount simply applies to the gross price.
     *
     * @param  float  $grossAmount   VAT-inclusive order total BEFORE discount.
     * @param  float  $discountPct   Discount percentage (e.g. 20.0).
     * @param  PDO    $pdo
     * @return array {
     *   gross_before_discount,
     *   vat_exclusive_before,
     *   discount_amount,        ← the peso amount deducted
     *   discounted_vat_exclusive,
     *   vat_removed_amount,
     *   total_after_discount,
     *   vatable_sales,
     *   vat_amount,
     *   vat_exempt_amount,
     *   zero_rated_amount
     * }
     */
    public static function applyScDiscount(float $grossAmount, float $discountPct, PDO $pdo): array
    {
        $grossAmount = round($grossAmount, 2);
        $rate        = self::getVatRate($pdo) / 100;
        $divisor     = 1 + $rate;

        if (self::isVatRegistered($pdo)) {
            $vatExclusive          = round($grossAmount / $divisor, 2);
            $discountAmount        = round($vatExclusive * ($discountPct / 100), 2);
            $discountedVatExcl     = max(0.00, round($vatExclusive - $discountAmount, 2));
            $vatRemovedAmount      = round($grossAmount - $vatExclusive, 2);
            $vatOnDiscounted       = 0.00;
            $totalAfterDiscount    = $discountedVatExcl;
        } else {
            $vatExclusive          = $grossAmount;
            $discountAmount        = round($grossAmount * ($discountPct / 100), 2);
            $discountedVatExcl     = max(0.00, round($grossAmount - $discountAmount, 2));
            $vatRemovedAmount      = 0.00;
            $vatOnDiscounted       = 0.00;
            $totalAfterDiscount    = $discountedVatExcl;
        }

        return [
            'gross_before_discount'    => round($grossAmount, 2),
            'vat_exclusive_before'     => round($vatExclusive, 2),
            'discount_amount'          => $discountAmount,
            'discounted_vat_exclusive' => $discountedVatExcl,
            'vat_removed_amount'       => $vatRemovedAmount,
            'vat_on_discounted'        => $vatOnDiscounted,
            'total_after_discount'     => $totalAfterDiscount,
            'vatable_sales'            => 0.00,
            'vat_amount'               => 0.00,
            'vat_exempt_amount'        => $totalAfterDiscount,
            'zero_rated_amount'        => 0.00,
        ];
    }

    /**
     * Apply a regular promo/manual percentage discount directly on the tagged price.
     * VAT treatment is then computed from the final discounted total.
     */
    public static function applyRegularDiscount(float $grossAmount, float $discountPct, PDO $pdo): array
    {
        $grossAmount        = round($grossAmount, 2);
        $discountAmount     = round($grossAmount * ($discountPct / 100), 2);
        $totalAfterDiscount = max(0.00, round($grossAmount - $discountAmount, 2));
        $orderBreakdown     = self::orderBreakdown($totalAfterDiscount, $pdo, 'promo');
        $beforeBreakdown    = self::orderBreakdown($grossAmount, $pdo, 'none');

        return [
            'gross_before_discount'    => $grossAmount,
            'vat_exclusive_before'     => self::isVatRegistered($pdo)
                ? round($grossAmount - $beforeBreakdown['vat_amount'], 2)
                : $grossAmount,
            'discount_amount'          => $discountAmount,
            'discounted_vat_exclusive' => self::isVatRegistered($pdo)
                ? round($totalAfterDiscount - $orderBreakdown['vat_amount'], 2)
                : $totalAfterDiscount,
            'vat_removed_amount'       => 0.00,
            'vat_on_discounted'        => $orderBreakdown['vat_amount'],
            'total_after_discount'     => $totalAfterDiscount,
            'vatable_sales'            => $orderBreakdown['vatable_sales'],
            'vat_amount'               => $orderBreakdown['vat_amount'],
            'vat_exempt_amount'        => $orderBreakdown['vat_exempt_amount'],
            'zero_rated_amount'        => $orderBreakdown['zero_rated_amount'],
        ];
    }

    /**
     * Unified discount entry point used by AJAX preview and order submission.
     */
    public static function applyDiscount(string $discountType, float $grossAmount, float $discountPct, PDO $pdo): array
    {
        if ($discountType === 'none' || $discountPct <= 0) {
            $orderBreakdown = self::orderBreakdown($grossAmount, $pdo, 'none');

            return [
                'gross_before_discount'    => round($grossAmount, 2),
                'vat_exclusive_before'     => self::isVatRegistered($pdo)
                    ? round($grossAmount - $orderBreakdown['vat_amount'], 2)
                    : round($grossAmount, 2),
                'discount_amount'          => 0.00,
                'discounted_vat_exclusive' => $orderBreakdown['vatable_sales'],
                'vat_removed_amount'       => 0.00,
                'vat_on_discounted'        => $orderBreakdown['vat_amount'],
                'total_after_discount'     => round($grossAmount, 2),
                'vatable_sales'            => $orderBreakdown['vatable_sales'],
                'vat_amount'               => $orderBreakdown['vat_amount'],
                'vat_exempt_amount'        => $orderBreakdown['vat_exempt_amount'],
                'zero_rated_amount'        => $orderBreakdown['zero_rated_amount'],
            ];
        }

        if (in_array($discountType, ['senior', 'pwd'], true)) {
            return self::applyScDiscount($grossAmount, $discountPct, $pdo);
        }

        return self::applyRegularDiscount($grossAmount, $discountPct, $pdo);
    }

    // ------------------------------------------------------------------
    // Convenience: get discount rates from config
    // ------------------------------------------------------------------

    public static function getSeniorDiscountPct(PDO $pdo): float
    {
        return (float) self::loadInfo($pdo)['min_senior_discount'];
    }

    public static function getPwdDiscountPct(PDO $pdo): float
    {
        return (float) self::loadInfo($pdo)['min_pwd_discount'];
    }

    // ------------------------------------------------------------------
    // Receipt line builder (used by print-receipt.php)
    // ------------------------------------------------------------------

    /**
     * Build the VAT summary lines for a receipt given an order row.
     * Returns an array ready for the receipt template to iterate.
     *
     * @param  array $order   Row from orders table (must have vat_amount, total_amount, discount_amount, discount_type)
     * @param  PDO   $pdo
     * @return array
     */
    public static function receiptLines(array $order, PDO $pdo): array
    {
        $isVat       = self::isVatRegistered($pdo);
        $vatRate     = self::getVatRate($pdo);
        $gross       = (float)$order['total_amount'];
        $vatAmount   = (float)($order['vat_amount'] ?? 0);
        $vatExempt   = (float)($order['vat_exempt_amount'] ?? 0);
        $zeroRated   = (float)($order['zero_rated_amount'] ?? 0);
        $vatableSales = $isVat
            ? max(0.00, round($gross - $vatAmount - $vatExempt - $zeroRated, 2))
            : 0.00;

        return [
            'is_vat_registered' => $isVat,
            'vat_rate'          => $vatRate,
            'vatable_sales'     => $vatableSales,
            'vat_amount'        => $vatAmount,
            'vat_exempt'        => $vatExempt,
            'zero_rated'        => $zeroRated,
        ];
    }
}
