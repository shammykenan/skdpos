<?php
require_once __DIR__ . '/../core/init.php';

requireRole(['admin', 'cashier', 'kitchen']);

$order_id = (int) ($_GET['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
    exit;
}

try {
    // ── Order header ─────────────────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT o.order_id, o.order_number, o.status, o.order_type,
               o.customer_name, o.created_at
        FROM orders o
        WHERE o.order_id = ?
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    // ── Order items ───────────────────────────────────────────────────────────
    // Pricing rule:
    //   • If the order item has a variant_id  → use variant_name_snapshot (stored at order time)
    //     as the display name suffix. The unit_price already reflects the variant price.
    //   • If no variant_id                    → item has no variant; display item_name only.
    //
    // We use item_name_snapshot + variant_name_snapshot (both stored at order time) so that
    // renaming a menu item or variant later never corrupts historical orders.
    $stmt = $pdo->prepare("
        SELECT
            oi.order_item_id,
            oi.item_id,
            oi.variant_id,
            oi.item_name_snapshot,
            oi.variant_name_snapshot,
            oi.quantity,
            oi.unit_price,
            oi.subtotal,
            oi.status        AS item_status,
            oi.item_notes,

            -- Live fallback names (used only if snapshots are somehow empty)
            mi.item_name     AS live_item_name,
            mv.variant_name  AS live_variant_name,
            
            -- Current variant pricing (for price comparison)
            mv.price         AS current_variant_price,
            mv.cost_price    AS current_variant_cost_price

        FROM order_items oi
        JOIN menu_items  mi ON oi.item_id    = mi.item_id
        LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id

        WHERE oi.order_id = ?
        ORDER BY oi.order_item_id ASC
    ");
    $stmt->execute([$order_id]);
    $rawItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Normalise each item ───────────────────────────────────────────────────
    $items = [];
    foreach ($rawItems as $row) {
        // Resolve display name
        $itemName    = !empty($row['item_name_snapshot'])
                        ? $row['item_name_snapshot']
                        : $row['live_item_name'];

        $variantName = null;
        if ($row['variant_id'] !== null) {
            // Prefer snapshot; fall back to live name if snapshot is missing
            $variantName = !empty($row['variant_name_snapshot'])
                            ? $row['variant_name_snapshot']
                            : $row['live_variant_name'];
        }

        // Fetch addons for this item
        $stmtA = $pdo->prepare("
            SELECT oia.order_item_addon_id,
                   oia.quantity,
                   oia.price         AS unit_price,
                   a.addon_name
            FROM order_item_addons oia
            JOIN addons a ON oia.addon_id = a.addon_id
            WHERE oia.order_item_id = ?
        ");
        $stmtA->execute([$row['order_item_id']]);
        $addons = $stmtA->fetchAll(PDO::FETCH_ASSOC);

        $items[] = [
            'order_item_id'       => (int) $row['order_item_id'],
            'item_id'             => (int) $row['item_id'],
            'variant_id'          => $row['variant_id'] !== null ? (int) $row['variant_id'] : null,
            'item_name'           => $itemName,
            'variant_name'        => $variantName,           // null if no variant
            'has_variant'         => $row['variant_id'] !== null,
            'quantity'            => (int) $row['quantity'],
            'unit_price'          => (float) $row['unit_price'],
            'subtotal'            => (float) $row['subtotal'],
            'item_status'         => $row['item_status'] ?? 'pending',
            'item_notes'          => $row['item_notes'] ?? '',
            'addons'              => $addons,
            'current_variant_price' => $row['current_variant_price'] ? (float) $row['current_variant_price'] : null,
            'current_variant_cost_price' => $row['current_variant_cost_price'] ? (float) $row['current_variant_cost_price'] : null,
        ];
    }

    echo json_encode([
        'success' => true,
        'order'   => $order,
        'items'   => $items,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}