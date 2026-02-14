<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Cafeteria POS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        h1, h2 {
            margin-bottom: 10px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .card {
            background: #fff;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }

        .card small {
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-bottom: 30px;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        .section {
            margin-bottom: 40px;
        }
    </style>
</head>
<body>

<h1>📊 Admin Dashboard</h1>

<!-- ================= STATS ================= -->
<div class="stats">
    <div class="card">
        <small>Today Revenue</small>
        <h2>₱<?= number_format($stats['today_revenue'], 2) ?></h2>
    </div>
    <div class="card">
        <small>Today Orders</small>
        <h2><?= $stats['today_orders'] ?></h2>
    </div>
    <div class="card">
        <small>Customers Today</small>
        <h2><?= $stats['today_customers'] ?></h2>
    </div>
    <div class="card">
        <small>Pending Orders</small>
        <h2><?= $stats['pending_orders'] ?></h2>
    </div>
    <div class="card">
        <small>Monthly Revenue</small>
        <h2>₱<?= number_format($stats['monthly_revenue'], 2) ?></h2>
    </div>
</div>

<!-- ================= RECENT ORDERS ================= -->
<div class="section">
    <h2>🧾 Recent Orders</h2>
    <table>
        <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Cashier</th>
            <th>Total</th>
            <th>Status</th>
        </tr>
        <?php foreach ($recentOrders as $order): ?>
        <tr>
            <td><?= htmlspecialchars($order['order_number']) ?></td>
            <td><?= htmlspecialchars($order['customer_name'] ?? '—') ?></td>
            <td><?= htmlspecialchars($order['cashier_name'] ?? '—') ?></td>
            <td>₱<?= number_format($order['total_amount'], 2) ?></td>
            <td><?= ucfirst($order['status']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- ================= TOP ITEMS ================= -->
<div class="section">
    <h2>🔥 Top Selling Items</h2>
    <table>
        <tr>
            <th>Item</th>
            <th>Sold</th>
            <th>Revenue</th>
        </tr>
        <?php foreach ($topItems as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['item_name']) ?></td>
            <td><?= $item['total_sold'] ?></td>
            <td>₱<?= number_format($item['total_revenue'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- ================= LOW STOCK ================= -->
<div class="section">
    <h2>⚠️ Low Stock Ingredients</h2>
    <table>
        <tr>
            <th>Ingredient</th>
            <th>Stock</th>
            <th>Alert Level</th>
        </tr>
        <?php foreach ($lowStock as $ing): ?>
        <tr>
            <td><?= htmlspecialchars($ing['ingredient_name']) ?></td>
            <td><?= $ing['stock_quantity'] ?> <?= $ing['unit'] ?></td>
            <td><?= $ing['low_stock_alert'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- ================= PROFIT ================= -->
<div class="section">
    <h2>💰 Monthly Profit Summary</h2>
    <div class="stats">
        <div class="card">
            <small>Total Revenue</small>
            <h2>₱<?= number_format($profitSummary['total_revenue'], 2) ?></h2>
        </div>
        <div class="card">
            <small>Total Cost</small>
            <h2>₱<?= number_format($profitSummary['total_cost'], 2) ?></h2>
        </div>
        <div class="card">
            <small>Total Profit</small>
            <h2>₱<?= number_format($profitSummary['total_profit'], 2) ?></h2>
        </div>
    </div>
</div>

</body>
</html>
