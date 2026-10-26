<?php
require_once __DIR__ . '/../core/GrandTotal.php';

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

    /**
     * Compute a day's figures WITHOUT writing anything.
     *
     * Both the X-reading (mid-shift, repeatable) and the Z-reading (end of day,
     * written once and locked) call this, so the two can never disagree.
     */
    public function summarize(string $date): array {
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


        return [
            'reading_date'      => $date,
            'transaction_count' => (int) $orders['transaction_count'],
            'has_transactions'  => (int) $orders['transaction_count'] > 0,
            'beginning_or'      => $orRange['beginning_or'] ?? null,
            'ending_or'         => $orRange['ending_or'] ?? null,
            'gross_sales'       => (float) $orders['gross_sales'],
            'vatable_sales'     => $vatableSales,
            'vat_amount'        => (float) $orders['vat_amount'],
            'vat_exempt_sales'  => (float) $orders['vat_exempt_sales'],
            'zero_rated_sales'  => (float) $orders['zero_rated_sales'],
            'total_discounts'   => (float) $orders['total_discounts'],
            'senior_discounts'  => (float) $discounts['senior_discounts'],
            'pwd_discounts'     => (float) $discounts['pwd_discounts'],
            'promo_discounts'   => (float) $discounts['promo_discounts'],
            'total_voids'       => (float) $voids['total_voids'],
            'void_count'        => (int) $voids['void_count'],
            'total_refunds'     => (float) $refunds['total_refunds'],
            'refund_count'      => (int) $refunds['refund_count'],
            'net_sales'         => $netSales,
        ];
    }

    public function generate(string $date, int $userId): array {
        if ($this->existsForDate($date)) {
            throw new RuntimeException("Z-reading for {$date} has already been generated and is locked.");
        }

        $this->db->beginTransaction();
        try {
            $sum = $this->summarize($date);

            if (!$sum['has_transactions']) {
                $this->db->rollBack();
                throw new RuntimeException("No completed transactions found for {$date}.");
            }

            // ── BIR accumulators ──────────────────────────────────────────────
            // Z-counter is sequential; the Grand Total carries across days so
            // that New GT - Old GT always reconciles to this day's gross sales.
            $zCounter = (int) $this->db->query(
                "SELECT COALESCE(MAX(z_counter), 0) + 1 FROM z_readings"
            )->fetchColumn();

            $resetCounter = (int) $this->db->query(
                "SELECT COALESCE(MAX(reset_counter), 0) FROM z_readings"
            )->fetchColumn();

            $newGt      = gt_current($this->db);
            $lastEntry  = gt_last_entry($this->db);
            $ledgerHash = $lastEntry ? (string) $lastEntry['entry_hash'] : str_repeat('0', 64);
            $oldGt = round($newGt - (float) $sum['gross_sales'], 2);
            if ($oldGt < 0) {
                $oldGt = 0.00;
            }

            $orders   = ['transaction_count' => $sum['transaction_count'],
                         'gross_sales'       => $sum['gross_sales'],
                         'vat_amount'        => $sum['vat_amount'],
                         'vat_exempt_sales'  => $sum['vat_exempt_sales'],
                         'zero_rated_sales'  => $sum['zero_rated_sales'],
                         'total_discounts'   => $sum['total_discounts']];
            $discounts = ['senior_discounts' => $sum['senior_discounts'],
                          'pwd_discounts'    => $sum['pwd_discounts'],
                          'promo_discounts'  => $sum['promo_discounts']];
            $voids     = ['void_count'   => $sum['void_count'],   'total_voids'   => $sum['total_voids']];
            $refunds   = ['refund_count' => $sum['refund_count'], 'total_refunds' => $sum['total_refunds']];
            $orRange   = ['beginning_or' => $sum['beginning_or'], 'ending_or'     => $sum['ending_or']];
            $vatableSales = $sum['vatable_sales'];
            $netSales     = $sum['net_sales'];

            // ── Insert locked record ──────────────────────────────────────────
            $stmt = $this->db->prepare("
                INSERT INTO z_readings (
                    reading_date, z_counter, reset_counter,
                    generated_by, generated_at,
                    beginning_or, ending_or, old_gt, new_gt, ledger_hash,
                    gross_sales, vatable_sales, vat_amount,
                    vat_exempt_sales, zero_rated_sales,
                    total_discounts, senior_discounts, pwd_discounts, promo_discounts,
                    total_voids, total_refunds, net_sales,
                    void_count, refund_count, transaction_count,
                    is_locked
                ) VALUES (
                    ?, ?, ?,
                    ?, NOW(),
                    ?, ?, ?, ?, ?,
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
                $zCounter,
                $resetCounter,
                $userId,
                $orRange['beginning_or'],
                $orRange['ending_or'],
                $oldGt,
                $newGt,
                $ledgerHash,
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

    /**
     * X-reading: a mid-shift snapshot. Reads only — writes nothing, resets
     * nothing, and may be run any number of times per day. Uses the same
     * summarize() as the Z-reading so the figures always agree.
     */
    public function xReading(string $date): array {
        $sum = $this->summarize($date);
        $sum['grand_total'] = gt_current($this->db);
        $sum['reading_type'] = 'X';
        $sum['generated_at'] = date('Y-m-d H:i:s');
        // Z-counter of the LAST completed Z — this day is not yet closed.
        $sum['last_z_counter'] = (int) $this->db->query(
            "SELECT COALESCE(MAX(z_counter), 0) FROM z_readings"
        )->fetchColumn();
        return $sum;
    }
}