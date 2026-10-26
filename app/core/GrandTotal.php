<?php
/**
 * Accumulated Grand Total (BIR).
 *
 * The GT is an odometer for the register: the sum of every sale ever recorded,
 * which only ever increases. Each Z-reading prints Old GT and New GT, and
 * New GT - Old GT must equal that day's sales. That arithmetic is what makes a
 * day's figures checkable years later instead of merely asserted.
 *
 * Implementation notes:
 *  - `sales_ledger` is APPEND-ONLY. Nothing here updates or deletes a row.
 *  - Each row stores a SHA-256 of (previous hash + this row's fields), so
 *    editing or removing any historical sale invalidates every hash after it.
 *    gt_verify() walks the chain and reports the first break.
 *  - No software counter is truly "non-resettable" — anyone with database
 *    access can write anything. The chain does not prevent tampering; it makes
 *    tampering detectable, which is what the requirement is actually for.
 */

/**
 * Canonical hash for one ledger entry.
 */
function gt_hash(
    string $prevHash,
    int    $orderId,
    string $orNumber,
    string $gross,
    string $net,
    string $runningGt,
    string $recordedAt
): string {
    return hash('sha256', implode('|', [
        $prevHash, $orderId, $orNumber, $gross, $net, $runningGt, $recordedAt,
    ]));
}

/**
 * Newest ledger row, or null when the ledger is empty.
 */
function gt_last_entry(PDO $pdo, bool $forUpdate = false): ?array
{
    $sql = "SELECT * FROM sales_ledger ORDER BY entry_id DESC LIMIT 1";
    if ($forUpdate) {
        $sql .= " FOR UPDATE";
    }
    $row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * Current accumulated Grand Total.
 */
function gt_current(PDO $pdo): float
{
    $last = gt_last_entry($pdo);
    return $last ? (float) $last['running_gt'] : 0.00;
}

/**
 * Append one sale to the ledger and return the new Grand Total.
 *
 * MUST be called inside the checkout transaction, immediately after the OR
 * number is assigned, so the GT and the OR sequence advance together or not
 * at all. Locks the tail of the ledger so two concurrent sales cannot build on
 * the same previous hash.
 *
 * Idempotent per order: a second call for the same order_id is ignored and the
 * existing running total is returned.
 */
function gt_append(PDO $pdo, int $orderId, string $orNumber, float $grossAmount, float $netAmount): float
{
    // Already recorded? (unique key on order_id also enforces this)
    $stmt = $pdo->prepare("SELECT running_gt FROM sales_ledger WHERE order_id = ? LIMIT 1");
    $stmt->execute([$orderId]);
    $existing = $stmt->fetchColumn();
    if ($existing !== false) {
        return (float) $existing;
    }

    $last      = gt_last_entry($pdo, true);
    $prevHash  = $last ? (string) $last['entry_hash'] : str_repeat('0', 64);
    $prevTotal = $last ? (float) $last['running_gt'] : 0.00;

    // Money is compared and hashed as fixed-scale strings so the stored value
    // and the hashed value can never disagree through float formatting.
    $gross     = number_format($grossAmount, 2, '.', '');
    $net       = number_format($netAmount, 2, '.', '');
    $runningGt = number_format($prevTotal + (float) $gross, 2, '.', '');
    $recordedAt = date('Y-m-d H:i:s');

    $hash = gt_hash($prevHash, $orderId, $orNumber, $gross, $net, $runningGt, $recordedAt);

    $stmt = $pdo->prepare("
        INSERT INTO sales_ledger
            (order_id, or_number, gross_amount, net_amount, running_gt,
             recorded_at, prev_hash, entry_hash)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$orderId, $orNumber, $gross, $net, $runningGt, $recordedAt, $prevHash, $hash]);

    return (float) $runningGt;
}

/**
 * Walk the whole chain and confirm nothing has been altered.
 *
 * @return array{ok:bool, entries:int, grand_total:float, broken_at:?int, reason:?string}
 */
function gt_verify(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM sales_ledger ORDER BY entry_id ASC");

    $prevHash  = str_repeat('0', 64);
    $prevTotal = 0.00;
    $count     = 0;

    foreach ($stmt as $row) {
        $count++;

        if ($row['prev_hash'] !== $prevHash) {
            return [
                'ok' => false, 'entries' => $count, 'grand_total' => $prevTotal,
                'broken_at' => (int) $row['entry_id'],
                'reason' => 'previous-hash mismatch (an earlier entry was altered or removed)',
            ];
        }

        $expectedTotal = number_format($prevTotal + (float) $row['gross_amount'], 2, '.', '');
        if ($expectedTotal !== (string) $row['running_gt']) {
            return [
                'ok' => false, 'entries' => $count, 'grand_total' => $prevTotal,
                'broken_at' => (int) $row['entry_id'],
                'reason' => 'running total does not follow from the previous entry',
            ];
        }

        $expectedHash = gt_hash(
            $row['prev_hash'], (int) $row['order_id'], (string) $row['or_number'],
            (string) $row['gross_amount'], (string) $row['net_amount'],
            (string) $row['running_gt'], (string) $row['recorded_at']
        );
        if (!hash_equals($expectedHash, (string) $row['entry_hash'])) {
            return [
                'ok' => false, 'entries' => $count, 'grand_total' => $prevTotal,
                'broken_at' => (int) $row['entry_id'],
                'reason' => 'entry hash does not match its contents (this row was edited)',
            ];
        }

        $prevHash  = (string) $row['entry_hash'];
        $prevTotal = (float) $row['running_gt'];
    }

    return [
        'ok' => true, 'entries' => $count, 'grand_total' => $prevTotal,
        'broken_at' => null, 'reason' => null,
    ];
}
