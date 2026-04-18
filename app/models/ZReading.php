<?php

class ZReading {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function existsForDate(string $date): bool {
        $stmt = $this->db->prepare("SELECT z_id FROM z_readings WHERE reading_date = ?");
        $stmt->execute([$date]);
        return (bool) $stmt->fetch();
    }

    public function getForDate(string $date): ?array {
        $stmt = $this->db->prepare("
            SELECT z.*, u.full_name AS generated_by_name
            FROM z_readings z
            JOIN users u ON u.user_id = z.generated_by
            WHERE z.reading_date = ?
        ");
        $stmt->execute([$date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function listAll(): array {
        $stmt = $this->db->query("
            SELECT z.z_id, z.reading_date, z.generated_at, z.gross_sales,
                   z.net_sales, z.transaction_count, z.void_count, z.refund_count,
                   u.full_name AS generated_by_name
            FROM z_readings z
            JOIN users u ON u.user_id = z.generated_by
            ORDER BY z.reading_date DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generate(string $date, int $userId): array {
        if ($this->existsForDate($date)) {
            throw new RuntimeException("Z-reading for {$date} has already been generated and is locked.");
        }

        $this->db->beginTransaction();
        try {
            // ── Completed + voided paid orders (gross sales) ──────────────────
            // Pay-first system: voided orders still collected cash.
            // Gross sales must include them so the void deduction below balances.
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*)                                        AS transaction_count,
                    COALESCE(SUM(total_amount), 0)                 AS gross_sales,
                    COALESCE(SUM(vat_amount), 0)                   AS vat_amount,
                    COALESCE(SUM(vat_exempt_amount), 0)            AS vat_exempt_sales,
                    COALESCE(SUM(zero_rated_amount), 0)            AS zero_rated_sales,
                    COALESCE(SUM(discount_amount), 0)              AS total_discounts
                FROM orders
                WHERE DATE(created_at) = ?
                  AND status IN ('completed', 'voided')
                  AND EXISTS (
                      SELECT 1 FROM payments p
                      WHERE p.order_id = orders.order_id
                        AND p.amount_paid > 0
                  )
            ");
            $stmt->execute([$date]);
            $orders = $stmt->fetch(PDO::FETCH_ASSOC);

            if ((int) $orders['transaction_count'] === 0) {
                $this->db->rollBack();
                throw new RuntimeException("No completed transactions found for {$date}.");
            }

            // Derive vatable sales from the gross total
            $vatableSales = max(
                0.0,
                round(
                    (float) $orders['gross_sales']
                    - (float) $orders['vat_amount']
                    - (float) $orders['vat_exempt_sales']
                    - (float) $orders['zero_rated_sales'],
                    2
                )
            );

            // ── Senior / PWD / promo discount breakdown ───────────────────────
            $stmt = $this->db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN od.discount_type = 'senior' THEN od.discount_amt ELSE 0 END), 0) AS senior_discounts,
                    COALESCE(SUM(CASE WHEN od.discount_type = 'pwd'    THEN od.discount_amt ELSE 0 END), 0) AS pwd_discounts,
                    COALESCE(SUM(CASE WHEN od.discount_type = 'promo'  THEN od.discount_amt ELSE 0 END), 0) AS promo_discounts
                FROM order_discounts od
                JOIN orders o ON o.order_id = od.order_id
                WHERE DATE(o.created_at) = ?
                  AND o.status IN ('completed', 'voided')
                  AND EXISTS (
                      SELECT 1 FROM payments p
                      WHERE p.order_id = o.order_id
                        AND p.amount_paid > 0
                  )
            ");
            $stmt->execute([$date]);
            $discounts = $stmt->fetch(PDO::FETCH_ASSOC);

            // ── Voids ─────────────────────────────────────────────────────────
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*)                       AS void_count,
                    COALESCE(SUM(total_amount), 0) AS total_voids
                FROM orders
                WHERE DATE(created_at) = ?
                  AND status = 'voided'
                  AND EXISTS (
                      SELECT 1 FROM payments p
                      WHERE p.order_id = orders.order_id
                        AND p.amount_paid > 0
                  )
            ");
            $stmt->execute([$date]);
            $voids = $stmt->fetch(PDO::FETCH_ASSOC);

            // ── Refunds ───────────────────────────────────────────────────────
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*)                   AS refund_count,
                    COALESCE(SUM(r.amount), 0) AS total_refunds
                FROM refunds r
                JOIN orders o ON o.order_id = r.order_id
                WHERE DATE(r.created_at) = ?
            ");
            $stmt->execute([$date]);
            $refunds = $stmt->fetch(PDO::FETCH_ASSOC);

            // ── OR range ──────────────────────────────────────────────────────
            $stmt = $this->db->prepare("
                SELECT MIN(or_number) AS beginning_or, MAX(or_number) AS ending_or
                FROM orders
                WHERE DATE(created_at) = ?
                  AND or_number IS NOT NULL
            ");
            $stmt->execute([$date]);
            $orRange = $stmt->fetch(PDO::FETCH_ASSOC);

            // ── Net sales ─────────────────────────────────────────────────────
            // gross_sales is already net of discounts at the order level,
            // so only refunds are deducted here.
            $netSales = round(
                (float) $orders['gross_sales'] - (float) $refunds['total_refunds'],
                2
            );

            // ── Insert locked record ──────────────────────────────────────────
            $stmt = $this->db->prepare("
                INSERT INTO z_readings (
                    reading_date, generated_by, generated_at,
                    beginning_or, ending_or,
                    gross_sales, vatable_sales, vat_amount,
                    vat_exempt_sales, zero_rated_sales,
                    total_discounts, senior_discounts, pwd_discounts, promo_discounts,
                    total_voids, total_refunds, net_sales,
                    void_count, refund_count, transaction_count,
                    is_locked
                ) VALUES (
                    ?, ?, NOW(),
                    ?, ?,
                    ?, ?, ?,
                    ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    1
                )
            ");
            $stmt->execute([
                $date,
                $userId,
                $orRange['beginning_or'],
                $orRange['ending_or'],
                $orders['gross_sales'],
                $vatableSales,
                $orders['vat_amount'],
                $orders['vat_exempt_sales'],
                $orders['zero_rated_sales'],
                $orders['total_discounts'],
                $discounts['senior_discounts'],
                $discounts['pwd_discounts'],
                $discounts['promo_discounts'],
                $voids['total_voids'],
                $refunds['total_refunds'],
                $netSales,
                $voids['void_count'],
                $refunds['refund_count'],
                $orders['transaction_count'],
            ]);

            $this->db->commit();
            return $this->getForDate($date);

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}