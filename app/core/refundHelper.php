<?php

function refund_money_to_cents(float $amount): int
{
    return (int) round($amount * 100);
}

function refund_cents_to_money(int $amount): float
{
    return round($amount / 100, 2);
}

function refund_allocate_amount_by_weights(float $amount, array $weights): array
{
    $keys = array_keys($weights);
    $allocations = [];

    foreach ($keys as $key) {
        $allocations[$key] = 0.0;
        $weights[$key] = max(0.0, (float) $weights[$key]);
    }

    $totalWeight = array_sum($weights);
    $amountCents = refund_money_to_cents($amount);

    if ($amountCents <= 0 || $totalWeight <= 0) {
        return $allocations;
    }

    $baseAllocations = [];
    $remainders = [];
    $allocatedCents = 0;

    foreach ($keys as $index => $key) {
        $rawShare = ($amountCents * $weights[$key]) / $totalWeight;
        $baseCents = (int) floor($rawShare);

        $baseAllocations[$key] = $baseCents;
        $remainders[] = [
            'key'       => $key,
            'remainder' => $rawShare - $baseCents,
            'index'     => $index,
        ];
        $allocatedCents += $baseCents;
    }

    usort($remainders, static function (array $a, array $b): int {
        $cmp = $b['remainder'] <=> $a['remainder'];
        if ($cmp !== 0) {
            return $cmp;
        }

        return $a['index'] <=> $b['index'];
    });

    $remainingCents = max(0, $amountCents - $allocatedCents);
    for ($i = 0; $i < $remainingCents; $i++) {
        $key = $remainders[$i % count($remainders)]['key'];
        $baseAllocations[$key]++;
    }

    foreach ($keys as $key) {
        $allocations[$key] = refund_cents_to_money($baseAllocations[$key] ?? 0);
    }

    return $allocations;
}

function refund_hydrate_order_items(PDO $pdo, array $order, array $items): array
{
    $orderId = (int) ($order['order_id'] ?? 0);
    if ($orderId <= 0 || empty($items)) {
        return $items;
    }

    $refundStmt = $pdo->prepare("
        SELECT
            order_item_id,
            COALESCE(SUM(amount), 0) AS refunded_amount,
            COALESCE(SUM(COALESCE(quantity, 0)), 0) AS refunded_quantity
        FROM refunds
        WHERE order_id = ?
        GROUP BY order_item_id
    ");
    $refundStmt->execute([$orderId]);

    $itemRefunds = [];
    $unassignedRefundTotal = 0.0;

    foreach ($refundStmt->fetchAll(PDO::FETCH_ASSOC) as $refundRow) {
        $itemId = $refundRow['order_item_id'];
        $amount = round((float) ($refundRow['refunded_amount'] ?? 0), 2);
        $quantity = (int) ($refundRow['refunded_quantity'] ?? 0);

        if ($itemId === null) {
            $unassignedRefundTotal = $amount;
            continue;
        }

        $itemRefunds[(int) $itemId] = [
            'amount'   => $amount,
            'quantity' => $quantity,
        ];
    }

    $discountImpact = max(
        0.0,
        round(
            (float) ($order['subtotal'] ?? 0) - (float) ($order['total_amount'] ?? 0),
            2
        )
    );

    $discountWeights = [];
    foreach ($items as $item) {
        $status = (string) ($item['item_status'] ?? '');
        if (in_array($status, ['cancelled', 'voided'], true)) {
            continue;
        }

        $addonTotal = 0.0;
        if (!empty($item['addons']) && is_array($item['addons'])) {
            foreach ($item['addons'] as $addon) {
                $addonTotal += (float) ($addon['price'] ?? 0) * (int) ($addon['quantity'] ?? 0);
            }
        } else {
            $addonTotal = (float) ($item['addon_total'] ?? 0);
        }

        $lineTotal = round((float) ($item['subtotal'] ?? 0) + $addonTotal, 2);
        if ($lineTotal <= 0) {
            continue;
        }

        $discountWeights[(int) $item['order_item_id']] = $lineTotal;
    }

    $discountShares = refund_allocate_amount_by_weights($discountImpact, $discountWeights);

    $remainingWeights = [];
    foreach ($items as $item) {
        $itemId = (int) ($item['order_item_id'] ?? 0);
        $status = (string) ($item['item_status'] ?? '');

        $addonTotal = 0.0;
        if (!empty($item['addons']) && is_array($item['addons'])) {
            foreach ($item['addons'] as $addon) {
                $addonTotal += (float) ($addon['price'] ?? 0) * (int) ($addon['quantity'] ?? 0);
            }
        } else {
            $addonTotal = (float) ($item['addon_total'] ?? 0);
        }

        $lineTotal = round((float) ($item['subtotal'] ?? 0) + $addonTotal, 2);
        $discountShare = (float) ($discountShares[$itemId] ?? 0);
        $paidTotal = max(0.0, round($lineTotal - $discountShare, 2));
        $itemSpecificRefund = (float) ($itemRefunds[$itemId]['amount'] ?? 0);
        $remainingAfterSpecificRefund = max(0.0, round($paidTotal - $itemSpecificRefund, 2));

        if (!in_array($status, ['cancelled', 'voided'], true) && $remainingAfterSpecificRefund > 0) {
            $remainingWeights[$itemId] = $remainingAfterSpecificRefund;
        }
    }

    $unassignedRefundShares = refund_allocate_amount_by_weights($unassignedRefundTotal, $remainingWeights);

    foreach ($items as &$item) {
        $itemId = (int) ($item['order_item_id'] ?? 0);
        $quantity = max(1, (int) ($item['quantity'] ?? 1));
        $quantityVoided = max(0, (int) ($item['quantity_voided'] ?? 0));

        $addonTotal = 0.0;
        if (!empty($item['addons']) && is_array($item['addons'])) {
            foreach ($item['addons'] as $addon) {
                $addonTotal += (float) ($addon['price'] ?? 0) * (int) ($addon['quantity'] ?? 0);
            }
        } else {
            $addonTotal = (float) ($item['addon_total'] ?? 0);
        }

        $lineTotal = round((float) ($item['subtotal'] ?? 0) + $addonTotal, 2);
        $discountShare = (float) ($discountShares[$itemId] ?? 0);
        $paidTotal = max(0.0, round($lineTotal - $discountShare, 2));
        $refundedAmount = round(
            (float) ($itemRefunds[$itemId]['amount'] ?? 0) + (float) ($unassignedRefundShares[$itemId] ?? 0),
            2
        );
        $remainingRefundable = max(0.0, round($paidTotal - $refundedAmount, 2));

        $item['addon_total'] = round($addonTotal, 2);
        $item['line_total'] = $lineTotal;
        $item['discount_share'] = round($discountShare, 2);
        $item['paid_total'] = $paidTotal;
        $item['refunded_amount'] = $refundedAmount;
        $item['refunded_quantity'] = (int) ($itemRefunds[$itemId]['quantity'] ?? 0);
        $item['remaining_refundable'] = $remainingRefundable;
        $item['refundable_cap'] = $remainingRefundable;
        $item['remaining_active_qty'] = max(0, $quantity - $quantityVoided);
        $item['effective_unit_paid'] = round($paidTotal / $quantity, 4);
    }
    unset($item);

    return $items;
}
