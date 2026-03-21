<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Manage Orders - Cafeteria POS</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/manage_orders.css">
</head>
<body>

<!-- TOPBAR -->
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

<!-- SIDEBAR -->
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
        <?php if (hasRole('admin') || hasRole('cashier')): ?>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

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
                            <i class="fas fa-th me-1"></i> All
                            <span class="filter-count">0</span>
                        </button>
                        <button class="filter-tab" data-status="pending">
                            <i class="fas fa-hourglass-half me-1"></i> Pending
                            <span class="filter-count">0</span>
                        </button>
                        <button class="filter-tab" data-status="preparing">
                            <i class="fas fa-bowl-food me-1"></i> Preparing
                            <span class="filter-count">0</span>
                        </button>
                        <button class="filter-tab" data-status="ready">
                            <i class="fas fa-circle-check me-1"></i> Ready
                            <span class="filter-count">0</span>
                        </button>
                        <button class="filter-tab" data-status="completed">
                            <i class="fas fa-check-circle me-1"></i> Completed
                            <span class="filter-count">0</span>
                        </button>
                        <button class="filter-tab" data-status="cancelled">
                            <i class="fas fa-times-circle me-1"></i> Cancelled
                            <span class="filter-count">0</span>
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
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Order Type</th>
                            <th>Items</th>
                            <th>Subtotal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        <!-- Injected by manage_orders.js -->
                    </tbody>
                </table>

                <div class="empty-state d-none" id="noResultsMessage">
                    <i class="fas fa-search"></i>
                    <h3>No Orders Found</h3>
                    <p>Try adjusting your filters or search terms.</p>
                </div>
            </div>
        </div>
    </div>

</div><!-- /.main-content -->


<!-- PAYMENT MODAL -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content pm-modal-content">

            <div class="pm-header">
                <div class="pm-header-left">
                    <div class="pm-header-icon"><i class="fas fa-cash-register"></i></div>
                    <div>
                        <div class="pm-header-title">Process Payment</div>
                        <div class="pm-header-sub">Order #<span id="paymentOrderNumber"></span></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="" id="paymentForm">
                <div class="modal-body pm-body">

                    <!-- Total due -->
                    <div class="pm-total-row">
                        <span class="pm-total-label">Total Due</span>
                        <span class="pm-total-amount">₱<span id="paymentTotalAmount">0.00</span></span>
                    </div>

                    <!-- Payment method + amount -->
                    <div class="pm-fields">
                        <div class="pm-field">
                            <label class="pm-label" for="paymentMethod">
                                <i class="fas fa-money-bill-wave"></i> Payment Method
                            </label>
                            <select class="form-select" id="paymentMethod" name="payment_method" required>
                                <option value="cash" selected>Cash</option>
                            </select>
                        </div>
                        <div class="pm-field">
                            <label class="pm-label" for="amountPaid">
                                <i class="fas fa-coins"></i> Amount Received
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="amountPaid"
                                       name="amount_paid" step="0.01" min="0"
                                       required placeholder="0.00" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <!-- Change / insufficient — toggled via JS classes -->
                    <div class="pm-feedback pm-feedback-change pm-hidden" id="changeAlert" role="alert">
                        <div class="pm-feedback-icon pm-feedback-icon-change">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="pm-feedback-label">Change to return</div>
                            <div class="pm-feedback-amount">₱<span id="changeAmount">0.00</span></div>
                        </div>
                    </div>

                    <div class="pm-feedback pm-feedback-warn pm-hidden" id="insufficientAlert" role="alert">
                        <div class="pm-feedback-icon pm-feedback-icon-warn">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <div class="pm-feedback-label">Insufficient amount</div>
                            <div class="pm-feedback-sub">Enter an amount equal to or greater than the total.</div>
                        </div>
                    </div>

                    <input type="hidden" name="order_id"      id="paymentOrderId">
                    <input type="hidden" name="status"        value="completed">
                    <input type="hidden" name="change_amount" id="changeAmountHidden">
                </div>

                <div class="modal-footer pm-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" name="update_status" class="btn btn-primary"
                            id="confirmPaymentBtn" disabled>
                        <i class="fas fa-check-circle me-1"></i>
                        Confirm & Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- CANCEL ORDER MODAL -->
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



<!-- KITCHEN ITEMS MODAL -->
<div class="modal fade" id="kitchenItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content" style="border-radius:24px;border:none;overflow:hidden;">
            <!-- Dynamic header populated by JS -->
            <div class="modal-header kitchen-modal-header" id="kitchenModalHeader"
                 style="background:var(--primary);color:white;padding:1.2rem 1.6rem;
                        border-bottom:none;display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
            </div>
            <div class="modal-body" style="padding:1.4rem 1.6rem;max-height:65vh;overflow-y:auto;">
                <div id="kitchenModalBody">
                    <!-- Populated by JS -->
                </div>
            </div>
            <div class="modal-footer" style="padding:1rem 1.6rem;border-top:1px solid var(--border);">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ORDER DETAILS MODAL -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content od-modal-content">

            <!-- Header -->
            <div class="od-header">
                <div class="od-header-left">
                    <div class="od-header-icon"><i class="fas fa-receipt"></i></div>
                    <div>
                        <div class="od-header-title">Order Details</div>
                        <div class="od-header-sub" id="detailOrderNumber">—</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">

                <!-- Loader -->
                <div id="orderDetailsLoader" class="text-center py-5">
                    <div class="spinner-border" style="color:var(--primary);width:2.2rem;height:2.2rem;" role="status"></div>
                    <p class="mt-3 text-muted mb-0" style="font-size:0.88rem;">Fetching order details…</p>
                </div>

                <!-- Content -->
                <div id="orderDetailsContent" style="display:none;">

                    <!-- Meta grid -->
                    <div class="od-meta-grid">
                        <div class="od-meta-item">
                            <div class="od-meta-label">Customer</div>
                            <div class="od-meta-value" id="detailCustomer">—</div>
                        </div>
                        <div class="od-meta-item">
                            <div class="od-meta-label">Date &amp; Time</div>
                            <div class="od-meta-value" id="detailDate">—</div>
                        </div>
                        <div class="od-meta-item">
                            <div class="od-meta-label">Cashier</div>
                            <div class="od-meta-value" id="detailCashier">—</div>
                        </div>
                        <div class="od-meta-item">
                            <div class="od-meta-label">Order Type</div>
                            <div class="od-meta-value" id="detailOrderType">—</div>
                        </div>
                        <div class="od-meta-item">
                            <div class="od-meta-label">Status</div>
                            <div class="od-meta-value" id="detailStatus">—</div>
                        </div>
                        <div class="od-meta-item" id="detailTableWrap" style="display:none;">
                            <div class="od-meta-label">Table</div>
                            <div class="od-meta-value" id="detailTable">—</div>
                        </div>
                    </div>

                    <!-- Items table -->
                    <div class="od-items-section">
                        <div class="od-section-label"><i class="fas fa-list me-1"></i> Order Items</div>
                        <table class="od-items-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detailItemsBody"></tbody>
                        </table>
                    </div>

                    <!-- Totals + Payment -->
                    <div class="od-footer-grid">

                        <!-- Payment info (shown only when completed) -->
                        <div id="detailPaymentSection" class="od-payment-card" style="display:none;">
                            <div class="od-payment-title"><i class="fas fa-money-bill-wave me-1"></i> Payment</div>
                            <div class="od-payment-row">
                                <span>Method</span>
                                <span id="detailPayMethod">—</span>
                            </div>
                            <div class="od-payment-row">
                                <span>Amount Paid</span>
                                <span>₱<span id="detailAmountPaid">0.00</span></span>
                            </div>
                            <div class="od-payment-row">
                                <span>Change</span>
                                <span>₱<span id="detailChange">0.00</span></span>
                            </div>
                        </div>

                        <!-- Totals breakdown -->
                        <div class="od-totals-card">
                            <div class="od-totals-row">
                                <span>Subtotal (excl. VAT)</span>
                                <span>₱<span id="detailSubtotal">0.00</span></span>
                            </div>
                            <div class="od-totals-row">
                                <span>VAT (12%)</span>
                                <span>₱<span id="detailVat">0.00</span></span>
                            </div>
                            <div class="od-totals-row od-totals-total">
                                <span>Total</span>
                                <span id="detailTotalDisplay">₱<span id="detailTotal">0.00</span></span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Error -->
                <div id="orderDetailsError" style="display:none;text-align:center;padding:3rem 1.5rem;color:var(--danger);">
                    <i class="fas fa-exclamation-circle" style="font-size:2.5rem;margin-bottom:0.8rem;display:block;"></i>
                    <p class="fw-semibold mb-0">Failed to load order details. Please try again.</p>
                </div>
            </div>

            <div class="modal-footer" style="border-top:1px solid var(--border);padding:1.1rem 1.6rem;border-radius:0 0 24px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <a id="printReceiptBtn" href="#" target="_blank" class="btn btn-primary" style="display:none;">
                    <i class="fas fa-print me-1"></i> Print Receipt
                </a>
            </div>

        </div>
    </div>
</div>


<!-- SUCCESS MODAL -->
<?php if (!empty($_SESSION['success'])): ?>
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="modal-icon success"><i class="fas fa-check-circle"></i></div>
                <h5 class="mb-3">Success!</h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($_SESSION['success']) ?></p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>


<!-- ERROR MODAL -->
<?php if (!empty($_SESSION['error'])): ?>
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="modal-icon danger"><i class="fas fa-exclamation-circle"></i></div>
                <h5 class="mb-3">Error</h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($_SESSION['error']) ?></p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>window.userRole = '<?= $_SESSION['role'] ?? '' ?>';</script>
<script src="<?= $baseUrl ?>/assets/js/manage_orders.js?v=<?= time() ?>"></script>
</body>
</html>