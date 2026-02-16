<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Manage Orders - Cafeteria POS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/manage_orders.css">
</head>
<body>

<!-- ════════════════════════════════════════
     TOPBAR
════════════════════════════════════════ -->
<div class="topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <button class="mobile-menu-btn d-lg-none" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-none d-sm-flex">
            <i class="fas fa-utensils"></i>
            <span>Dine with Din - Cafeteria</span>
        </a>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-sm-none">
            <i class="fas fa-utensils"></i>
        </a>
    </div>

    <div class="dropdown">
        <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center"
                type="button" id="userDropdown" data-bs-toggle="dropdown"
                style="color:var(--navy);font-weight:600;">
            <i class="far fa-user-circle fa-xl me-2" style="font-size:1.6rem;color:var(--secondary);"></i>
            <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════ -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-nav">
        <?php if (hasRole('admin')): ?>
        <div class="nav-section-title">DASHBOARD</div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>
        <div class="nav-section-title">MANAGEMENT</div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="nav-link">
                <i class="fas fa-utensils"></i> Menu Items
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_recipes" class="nav-link">
                <i class="fas fa-tags"></i> Recipes
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link">
                <i class="fas fa-boxes"></i> Ingredients
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link">
                <i class="fas fa-plus-circle"></i> Add-ons
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link">
                <i class="fas fa-users"></i> User Management
            </a>
        </div>
        <?php endif; ?>
        <?php if (hasRole('admin') || hasRole('cashier')): ?>
        <div class="nav-section-title">SALES</div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link">
                <i class="fas fa-cash-register"></i> POS System
            </a>
        </div>
        <?php endif; ?>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link active">
                <i class="fas fa-receipt"></i> Orders
            </a>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════
     MAIN CONTENT
════════════════════════════════════════ -->
<div class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1><i class="fas fa-receipt me-2"></i> Manage Orders</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Orders</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Filter Tabs & Search -->
    <div class="card fade-in mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8 col-md-12">
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-status="all">
                            <i class="fas fa-th me-1"></i> All Orders
                            <span class="filter-count"><?= count($orders) ?></span>
                        </button>
                        <button class="filter-tab" data-status="pending">
                            <i class="fas fa-clock me-1"></i> Pending
                            <span class="filter-count"><?= count(array_filter($orders, fn($o) => $o['status'] === 'pending')) ?></span>
                        </button>
                        <button class="filter-tab" data-status="preparing">
                            <i class="fas fa-utensils me-1"></i> Preparing
                            <span class="filter-count"><?= count(array_filter($orders, fn($o) => $o['status'] === 'preparing')) ?></span>
                        </button>
                        <button class="filter-tab" data-status="completed">
                            <i class="fas fa-check-circle me-1"></i> Completed
                            <span class="filter-count"><?= count(array_filter($orders, fn($o) => $o['status'] === 'completed')) ?></span>
                        </button>
                        <button class="filter-tab" data-status="cancelled">
                            <i class="fas fa-times-circle me-1"></i> Cancelled
                            <span class="filter-count"><?= count(array_filter($orders, fn($o) => $o['status'] === 'cancelled')) ?></span>
                        </button>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="searchInput"
                               placeholder="Search orders by #, customer, table...">
                    </div>
                </div>
            </div>
        </div>
    </div>
            
    <!-- Orders Table -->
    <div class="card fade-in">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2" style="color:var(--primary);"></i>
                <span id="currentFilterLabel">All Orders</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-container">
                <?php if (!empty($orders)): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Order Type</th>
                            <th>Items</th>
                            <th>Notes</th>
                            <th>Subtotal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        <?php foreach ($orders as $order):
                            $orderType      = isset($order['order_type']) && $order['order_type'] !== null ? $order['order_type'] : null;
                            $orderTypeLabel = '';
                            $orderTypeIcon  = '';
                            if ($orderType === 'dine_in')  { $orderTypeLabel = 'Dine In'; $orderTypeIcon = 'fa-utensils'; }
                            elseif ($orderType === 'takeout') { $orderTypeLabel = 'Takeout'; $orderTypeIcon = 'fa-bag-shopping'; }
                        ?>
                        <tr data-status="<?= strtolower($order['status']) ?>"
                            data-search-text="<?= strtolower(
                                $order['order_number'] . ' ' .
                                ($order['customer_name'] ?? 'walk-in') . ' ' .
                                ($order['table_number'] ?? '')
                            ) ?>"
                            data-order-total="<?= htmlspecialchars($order['total_amount']) ?>">

                            <!-- Order # -->
                            <td data-label="Order #">
                                <span class="order-number">#<?= htmlspecialchars($order['order_number']) ?></span>
                            </td>

                            <!-- Customer -->
                            <td data-label="Customer">
                                <div class="fw-medium" style="color:var(--navy);">
                                    <?= htmlspecialchars($order['customer_name'] ?? 'Walk-in') ?>
                                </div>
                                <small class="text-muted">
                                    <?= date('M d, Y h:i A', strtotime($order['created_at'] ?? 'now')) ?>
                                </small>
                                <?php if (!empty($order['table_number'])): ?>
                                    <div>
                                        <small class="text-muted">
                                            <i class="fas fa-chair me-1"></i>
                                            Table <?= htmlspecialchars($order['table_number']) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Order Type -->
                            <td data-label="Order Type">
                                <?php if ($orderType): ?>
                                    <span class="order-type-badge <?= $orderType ?>">
                                        <i class="fas <?= $orderTypeIcon ?>"></i>
                                        <?= $orderTypeLabel ?>
                                    </span>
                                <?php else: ?>
                                    <span class="order-type-missing">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- Items -->
                            <td data-label="Items">
                                <ul class="order-items">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li>
                                            <span class="item-name"><?= htmlspecialchars($item['item_name']) ?></span>
                                            <span class="item-qty">×<?= (int)$item['quantity'] ?></span>
                                            <?php if (!empty($item['addons'])): ?>
                                                <ul class="addon-list">
                                                    <?php foreach ($item['addons'] as $addon): ?>
                                                        <li>
                                                            <?= htmlspecialchars($addon['addon_name']) ?>
                                                            ×<?= (int)$addon['quantity'] ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                                <td data-label="Notes">
                                    <?php
                                    if (isset($order['items']) && is_array($order['items'])) {
                                        $hasNotes = false;
                                        foreach ($order['items'] as $item) {
                                            if (!empty($item['item_notes'])) {
                                                $hasNotes = true;
                                                ?>
                                                <div class="order-item-note">
                                                    <span class="note-item-label"><?= htmlspecialchars($item['item_name']) ?>:</span>
                                                    <span class="note-item-text"><?= htmlspecialchars($item['item_notes']) ?></span>
                                                </div>
                                                <?php
                                            }
                                        }
                                        if (!$hasNotes) {
                                            echo '<span class="text-muted">—</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">—</span>';
                                    }
                                    ?>
                                </td>
                            <!-- Subtotal -->
                            <td data-label="Subtotal">
                                <span class="price">₱<?= number_format($order['subtotal'], 2) ?></span>
                            </td>

                            <!-- Total -->
                            <td data-label="Total">
                                <span class="price">₱<?= number_format($order['total_amount'], 2) ?></span>
                            </td>

                            <!-- Status -->
                            <td data-label="Status">
                                <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                </span>
                            </td>

                            <!-- Actions -->
                            <td data-label="Actions">
                                <div class="action-buttons">

                                    <!-- View Details -->
                                    <button type="button"
                                            class="btn btn-sm view-order-btn"
                                            style="background:var(--lavender);color:var(--navy);"
                                            data-order-id="<?= $order['order_id'] ?>"
                                            data-order-number="<?= htmlspecialchars($order['order_number']) ?>">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>

                                    <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'completed'):
                                        // ── Role-based status rules ──
                                        $currentStatus   = $order['status'];
                                        $canUpdate       = true;
                                        $disabledReason  = '';
                                        $allowedOptions  = [];

                                        if ($currentStatus === 'preparing') {
                                            $allowedOptions = ['completed'];
                                            if (!hasRole('admin') && !hasRole('cashier')) {
                                                $canUpdate      = false;
                                                $disabledReason = 'Only cashier/admin can complete orders';
                                            }
                                        }
                                        if (hasRole('kitchen') && !hasRole('cashier')) {
                                            if ($currentStatus === 'pending') {
                                                $allowedOptions = ['preparing'];
                                            } else {
                                                $canUpdate      = false;
                                                $disabledReason = 'Kitchen can only start pending orders';
                                            }
                                        }
                                        if (hasRole('cashier') && !hasRole('admin')) {
                                            if ($currentStatus === 'preparing') {
                                                $allowedOptions = ['completed'];
                                            } elseif ($currentStatus === 'pending') {
                                                $canUpdate      = false;
                                                $disabledReason = 'Cashier cannot modify pending orders';
                                            }
                                        }
                                        if (hasRole('admin')) {
                                            $allowedOptions = $currentStatus === 'preparing'
                                                ? ['completed']
                                                : ['preparing', 'completed'];
                                        }
                                    ?>
                                        <div class="status-form">
                                            <?php if ($canUpdate && !empty($allowedOptions)): ?>
                                                <select class="form-select status-select"
                                                        data-order-id="<?= $order['order_id'] ?>"
                                                        data-current-status="<?= $currentStatus ?>">
                                                    <?php if (in_array('preparing', $allowedOptions)): ?>
                                                        <option value="preparing" <?= $currentStatus === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                                                    <?php endif; ?>
                                                    <?php if (in_array('completed', $allowedOptions)): ?>
                                                        <option value="completed" <?= $currentStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                    <?php endif; ?>
                                                </select>
                                                <button type="button"
                                                        class="btn btn-primary btn-sm update-status-btn"
                                                        data-order-id="<?= $order['order_id'] ?>"
                                                        data-order-number="<?= htmlspecialchars($order['order_number']) ?>">
                                                    <i class="fas fa-check"></i> Update
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-secondary btn-sm" disabled
                                                        title="<?= htmlspecialchars($disabledReason) ?>">
                                                    <i class="fas fa-ban"></i>
                                                    <?= htmlspecialchars($disabledReason ?: 'Action not available') ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ((hasRole('admin') || hasRole('cashier')) && $currentStatus === 'pending'): ?>
                                            <button type="button"
                                                    class="btn btn-danger btn-sm w-100 cancel-order-btn"
                                                    data-order-id="<?= $order['order_id'] ?>"
                                                    data-order-number="<?= htmlspecialchars($order['order_number']) ?>">
                                                <i class="fas fa-times"></i> Cancel Order
                                            </button>
                                        <?php elseif ($currentStatus === 'preparing'): ?>
                                            <span class="badge bg-warning"
                                                  style="padding:0.5rem 1rem;border-radius:30px;color:#1a202c;">
                                                <i class="fas fa-info-circle me-1"></i> Cannot cancel — in preparation
                                            </span>
                                        <?php endif; ?>

                                    <?php elseif ($order['status'] === 'completed'): ?>
                                        <span class="badge bg-success"
                                              style="padding:0.5rem 1rem;border-radius:30px;">
                                            <i class="fas fa-check-circle me-1"></i> Payment Received
                                        </span>

                                    <?php elseif ($order['status'] === 'cancelled'): ?>
                                        <span class="badge bg-secondary"
                                              style="padding:0.5rem 1rem;border-radius:30px;">
                                            <i class="fas fa-ban me-1"></i> Order Cancelled
                                        </span>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="empty-state d-none" id="noResultsMessage">
                    <i class="fas fa-search"></i>
                    <h3>No Orders Found</h3>
                    <p>Try adjusting your filters or search terms.</p>
                </div>

                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <h3>No Orders Yet</h3>
                    <p>Orders will appear here once customers start placing them.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div><!-- /.main-content -->


<!-- ════════════════════════════════════════
     PAYMENT MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fas fa-cash-register me-2" style="color:var(--secondary);"></i>
                    Process Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="paymentForm">
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Order Number</h6>
                            <h4 class="order-number mb-0">#<span id="paymentOrderNumber"></span></h4>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-muted mb-2">Total Amount</h6>
                            <h3 class="price mb-0">₱<span id="paymentTotalAmount">0.00</span></h3>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="paymentMethod" class="form-label fw-semibold">
                                <i class="fas fa-money-bill-wave me-1"></i> Payment Method
                            </label>
                            <select class="form-select" id="paymentMethod" name="payment_method" required>
                                <option value="cash" selected>Cash</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="amountPaid" class="form-label fw-semibold">
                                <i class="fas fa-hand-holding-usd me-1"></i>
                                Amount Paid <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="amountPaid"
                                       name="amount_paid" step="0.01" min="0"
                                       required placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info d-flex align-items-center"
                                 id="changeAlert" style="display:none !important;" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <div>
                                    <strong>Change to return:</strong> ₱<span id="changeAmount">0.00</span>
                                </div>
                            </div>
                            <div class="alert alert-danger d-flex align-items-center"
                                 id="insufficientAlert" style="display:none !important;" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div>
                                    <strong>Insufficient amount!</strong>
                                    Please enter an amount ≥ the total.
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="order_id"      id="paymentOrderId">
                    <input type="hidden" name="status"        value="completed">
                    <input type="hidden" name="change_amount" id="changeAmountHidden">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" name="update_status" class="btn btn-primary"
                            id="confirmPaymentBtn" disabled>
                        <i class="fas fa-check-circle me-1"></i>
                        Confirm Payment & Complete Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════
     UPDATE STATUS MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">
                    <i class="fas fa-edit me-2" style="color:var(--warning);"></i>
                    Update Order Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon primary">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <h5 class="mb-3">Confirm Status Update</h5>
                <p class="text-muted mb-0">
                    Update <strong>Order #<span id="updateOrderNumber"></span></strong>
                    to <strong><span id="updateNewStatus"></span></strong>?
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <form method="POST" action="" id="updateStatusForm">
                    <input type="hidden" name="order_id" id="updateOrderId">
                    <input type="hidden" name="status"   id="updateStatusValue">
                    <button type="submit" name="update_status" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i> Confirm Update
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════
     CANCEL ORDER MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel">
                    <i class="fas fa-exclamation-triangle me-2" style="color:var(--warning);"></i>
                    Cancel Order
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h5 class="mb-3">Are you sure?</h5>
                <p class="text-muted mb-0">
                    Cancel <strong>Order #<span id="cancelOrderNumber"></span></strong>?
                    This action cannot be undone and ingredients will be restored.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-1"></i> Go Back
                </button>
                <form method="POST" action="" id="cancelOrderForm">
                    <input type="hidden" name="order_id" id="cancelOrderId">
                    <button type="submit" name="cancel_order" class="btn btn-danger">
                        <i class="fas fa-times-circle me-1"></i> Yes, Cancel Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════
     ORDER DETAILS MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content"
             style="border-radius:28px;border:none;box-shadow:0 25px 50px rgba(0,0,0,0.15);">

            <!-- Header -->
            <div class="modal-header"
                 style="background:var(--primary);color:white;padding:1.5rem 1.8rem;border-radius:28px 28px 0 0;border-bottom:none;">
                <h5 class="modal-title d-flex align-items-center gap-2"
                    style="color:white;font-size:1.2rem;font-weight:700;">
                    <i class="fas fa-receipt"></i>
                    Order Details —
                    <span style="font-size:0.95rem;" id="detailOrderNumber"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-0">

                <!-- Loading -->
                <div id="orderDetailsLoader" class="text-center py-5">
                    <div class="spinner-border"
                         style="color:var(--primary);width:2.5rem;height:2.5rem;" role="status">
                        <span class="visually-hidden">Loading…</span>
                    </div>
                    <p class="mt-3 text-muted">Fetching order details…</p>
                </div>

                <!-- Content -->
                <div id="orderDetailsContent" style="display:none;">

                    <!-- Meta row -->
                    <div style="background:var(--light-bg);padding:1.4rem 1.8rem;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;gap:1.5rem;">
                        <div>
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--warm-gray);margin-bottom:3px;">Customer</div>
                            <div style="font-weight:600;color:var(--navy);" id="detailCustomer">—</div>
                        </div>
                        <div>
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--warm-gray);margin-bottom:3px;">Date &amp; Time</div>
                            <div style="font-weight:600;color:var(--navy);" id="detailDate">—</div>
                        </div>
                        <div>
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--warm-gray);margin-bottom:3px;">Cashier</div>
                            <div style="font-weight:600;color:var(--navy);" id="detailCashier">—</div>
                        </div>
                        <div>
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--warm-gray);margin-bottom:3px;">Order Type</div>
                            <div id="detailOrderType">—</div>
                        </div>
                        <div>
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--warm-gray);margin-bottom:3px;">Status</div>
                            <div id="detailStatus">—</div>
                        </div>
                        <div id="detailTableWrap" style="display:none;">
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--warm-gray);margin-bottom:3px;">Table</div>
                            <div style="font-weight:600;color:var(--navy);" id="detailTable">—</div>
                        </div>
                    </div>

                    <!-- Items table -->
                    <div style="padding:1.6rem 1.8rem 0;">
                        <div style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--warm-gray);margin-bottom:0.9rem;">
                            <i class="fas fa-list me-1" style="color:var(--primary);"></i> Order Items
                        </div>
                        <table style="width:100%;border-collapse:separate;border-spacing:0;font-size:0.88rem;">
                            <thead>
                                <tr>
                                    <th style="background:#f2f6fc;padding:0.75rem 1rem;text-align:left;color:var(--primary-dark);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;border-bottom:2px solid var(--primary-light);">Item</th>
                                    <th style="background:#f2f6fc;padding:0.75rem 1rem;text-align:center;color:var(--primary-dark);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;border-bottom:2px solid var(--primary-light);">Qty</th>
                                    <th style="background:#f2f6fc;padding:0.75rem 1rem;text-align:right;color:var(--primary-dark);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;border-bottom:2px solid var(--primary-light);">Unit Price</th>
                                    <th style="background:#f2f6fc;padding:0.75rem 1rem;text-align:right;color:var(--primary-dark);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;border-bottom:2px solid var(--primary-light);">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detailItemsBody">
                                <!-- injected by JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals & Payment -->
                    <div style="padding:1.2rem 1.8rem 1.6rem;display:flex;flex-wrap:wrap;gap:1.5rem;justify-content:flex-end;">

                        <!-- Payment info -->
                        <div id="detailPaymentSection"
                             style="display:none;background:rgba(44,122,90,0.07);border:1px solid rgba(44,122,90,0.18);border-radius:18px;padding:1rem 1.4rem;min-width:200px;">
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--success);margin-bottom:0.6rem;">
                                <i class="fas fa-money-bill-wave me-1"></i> Payment
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:0.85rem;">
                                <span style="color:var(--warm-gray);">Method</span>
                                <span style="font-weight:600;color:var(--navy);" id="detailPayMethod">—</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:0.85rem;">
                                <span style="color:var(--warm-gray);">Amount Paid</span>
                                <span style="font-weight:600;color:var(--navy);">₱<span id="detailAmountPaid">0.00</span></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:0.85rem;">
                                <span style="color:var(--warm-gray);">Change</span>
                                <span style="font-weight:600;color:var(--navy);">₱<span id="detailChange">0.00</span></span>
                            </div>
                        </div>

                        <!-- Totals box -->
                        <div style="background:var(--light-bg);border:1px solid var(--border);border-radius:18px;padding:1rem 1.4rem;min-width:230px;">
                            <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:0.85rem;">
                                <span style="color:var(--warm-gray);">Subtotal (excl. VAT)</span>
                                <span style="font-weight:600;color:var(--navy);">₱<span id="detailSubtotal">0.00</span></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:0.85rem;">
                                <span style="color:var(--warm-gray);">VAT (12%)</span>
                                <span style="font-weight:600;color:var(--navy);">₱<span id="detailVat">0.00</span></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding-top:8px;border-top:2px solid var(--border);font-size:1rem;">
                                <span style="font-weight:700;color:var(--navy);">Total</span>
                                <span style="font-weight:700;color:var(--success);font-size:1.1rem;">₱<span id="detailTotal">0.00</span></span>
                            </div>
                        </div>

                    </div>
                </div><!-- /#orderDetailsContent -->

                <!-- Error state -->
                <div id="orderDetailsError"
                     style="display:none;text-align:center;padding:3rem 1.5rem;color:var(--danger);">
                    <i class="fas fa-exclamation-circle"
                       style="font-size:2.5rem;margin-bottom:0.8rem;display:block;"></i>
                    <p class="fw-semibold mb-0">Failed to load order details. Please try again.</p>
                </div>
            </div><!-- /.modal-body -->

            <!-- Footer -->
            <div class="modal-footer"
                 style="border-top:1px solid var(--border);padding:1.2rem 1.8rem;border-radius:0 0 28px 28px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <a id="printReceiptBtn" href="#" target="_blank"
                   class="btn btn-primary" style="display:none;">
                    <i class="fas fa-print me-1"></i> Print Receipt
                </a>
            </div>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════
     SUCCESS MODAL
════════════════════════════════════════ -->
<?php if (!empty($_SESSION['success'])): ?>
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="modal-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5 class="mb-3">Success!</h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($_SESSION['success']) ?></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>


<!-- ════════════════════════════════════════
     ERROR MODAL
════════════════════════════════════════ -->
<?php if (!empty($_SESSION['error'])): ?>
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="modal-icon danger">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h5 class="mb-3">Error</h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($_SESSION['error']) ?></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Pass user role to JavaScript -->
<script>window.userRole = '<?= $_SESSION['role'] ?? '' ?>';</script>
<script src="<?= $baseUrl ?>/assets/js/manage_orders.js"></script>
</body>
</html>