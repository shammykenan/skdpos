<?php
/**
 * OrNumberHelper
 * Generates BIR-compliant Official Receipt numbers.
 *
 * Format : OR-YYYYMMDD-XXXXX  (5-digit sequence, resets each day)
 * Example: OR-20260418-00001
 *
 * Uses SELECT ... FOR UPDATE inside a transaction to prevent
 * duplicate OR numbers under concurrent cashier sessions.
 */

/**
 * Atomically get the next OR number for today.
 * Call this inside the payment processing transaction.
 *
 * @param  PDO    $pdo   Must be an active PDO connection.
 * @param  string $date  Date string 'Y-m-d'. Defaults to today (Asia/Manila).
 * @return string        Formatted OR number, e.g. OR-20260418-00001
 * @throws Exception     If the sequence cannot be generated.
 */
function getNextOrNumber(PDO $pdo, string $date = ''): string
{
    if ($date === '') {
        $date = date('Y-m-d');
    }

    // Lock the sequence row for this date so concurrent requests queue up.
    $stmt = $pdo->prepare("
        SELECT seq_id, last_seq
        FROM or_sequences
        WHERE seq_date = :date
        FOR UPDATE
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $nextSeq = (int)$row['last_seq'] + 1;
        $pdo->prepare("
            UPDATE or_sequences SET last_seq = :seq WHERE seq_id = :id
        ")->execute([':seq' => $nextSeq, ':id' => $row['seq_id']]);
    } else {
        $nextSeq = 1;
        $pdo->prepare("
            INSERT INTO or_sequences (seq_date, last_seq) VALUES (:date, 1)
        ")->execute([':date' => $date]);
    }

    $datePart = str_replace('-', '', $date); // YYYYMMDD
    $seqPart  = str_pad($nextSeq, 5, '0', STR_PAD_LEFT);

    return "OR-{$datePart}-{$seqPart}";
}

/**
 * Get the beginning and ending OR numbers issued on a given date.
 * Used by Z-reading to populate the OR range in the daily report.
 *
 * @param  PDO    $pdo
 * @param  string $date  'Y-m-d'
 * @return array  ['beginning' => string|null, 'ending' => string|null, 'count' => int]
 */
function getOrRangeForDate(PDO $pdo, string $date): array
{
    // Pull the lowest and highest OR numbers assigned on this date from orders
    $stmt = $pdo->prepare("
        SELECT
            MIN(or_number) AS beginning_or,
            MAX(or_number) AS ending_or,
            COUNT(or_number) AS or_count
        FROM orders
        WHERE DATE(created_at) = :date
          AND or_number IS NOT NULL
          AND status NOT IN ('draft', 'cancelled')
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'beginning' => $row['beginning_or'] ?? null,
        'ending'    => $row['ending_or']    ?? null,
        'count'     => (int)($row['or_count'] ?? 0),
    ];
}

/**
 * Get the last OR sequence number issued for a date (for display only).
 *
 * @param  PDO    $pdo
 * @param  string $date  'Y-m-d'
 * @return int
 */
function getLastOrSequence(PDO $pdo, string $date): int
{
    $stmt = $pdo->prepare("
        SELECT last_seq FROM or_sequences WHERE seq_date = :date
    ");
    $stmt->execute([':date' => $date]);
    return (int)($stmt->fetchColumn() ?? 0);
}