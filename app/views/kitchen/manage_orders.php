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
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/common.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/manage_orders.css">
</head>
<style>

/* ── Refund toggle (void modal) ── */
#voidRefundToggleBtn:hover { background: var(--lavender) !important; }
#voidRefundAmount:focus {
    border-color: var(--danger) !important;
    box-shadow: 0 0 0 3px rgba(214,64,69,0.10);
}
#voidRefundAmount.inp-invalid { border-color: var(--danger) !important; }
#voidRefundAmount.inp-valid   { border-color: var(--success) !important; }
#voidRefundNote:focus, #voidRefundMethod:focus {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 3px rgba(15,76,92,0.08);
    outline: none;
}
.refund-preset-btn {
    padding: 0.3rem 0.75rem;
    border: 1.5px solid var(--border);
    border-radius: 40px;
    background: white;
    font-size: 0.76rem;
    font-weight: 700;
    color: var(--warm-gray);
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
}
.refund-preset-btn:hover { border-color: var(--danger); color: var(--danger); background: rgba(214,64,69,0.05); }
.refund-preset-btn.active { background: var(--danger); color: white; border-color: var(--danger); }
.refund-preset-btn.full   { border-color: rgba(44,122,90,0.5); color: var(--success); background: rgba(44,122,90,0.06); }
.refund-preset-btn.full:hover, .refund-preset-btn.full.active { background: var(--success); color: white; border-color: var(--success); }
#voidRefundChevron.open { transform: rotate(180deg); }

/* ── Mark served bell ── */
#markServedModal .fa-concierge-bell { animation: bellPulse 1.4s ease-in-out infinite; }
@keyframes bellPulse {
    0%,100% { transform: rotate(0deg); }
    20%     { transform: rotate(-14deg); }
    40%     { transform: rotate(14deg); }
    60%     { transform: rotate(-8deg); }
    80%     { transform: rotate(8deg); }
}

/* ── Refund card in order details (void refund) ── */
#detailRefundSection .od-payment-title { color: var(--danger); }

/* ── Post-completion Refund Modal ── */
.refund-type-btn { transition: all 0.15s; }
.refund-type-btn.active[data-type="full"]    { background: var(--success) !important; color: white !important; border-color: var(--success) !important; }
.refund-type-btn.active[data-type="partial"] { background: var(--primary) !important; color: white !important; border-color: var(--primary) !important; }

.refund-item-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.85rem;
    border-bottom: 1px solid var(--border);
    font-size: 0.83rem;
    transition: background 0.1s;
}
.refund-item-row:last-child { border-bottom: none; }
.refund-item-row:hover      { background: var(--light-bg); }
.refund-item-row input[type="checkbox"] {
    accent-color: var(--primary);
    width: 16px; height: 16px;
    cursor: pointer; flex-shrink: 0;
}
.refund-item-amount {
    margin-left: auto;
    padding: 0.25rem 0.5rem;
    width: 95px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 0.82rem;
    font-family: inherit;
    text-align: right;
    outline: none;
    transition: border-color 0.15s;
}
.refund-item-amount:focus         { border-color: var(--primary); }
.refund-item-amount:not([disabled]) { border-color: var(--primary-light); }
</style>
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
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-section-title">MANAGEMENT</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_recipes" class="nav-link"><i class="fas fa-tags"></i> Recipes</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-section-title">SALES</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link"><i class="fas fa-cash-register"></i> POS System</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link active"><i class="fas fa-receipt"></i> Orders</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <?php endif; ?>

        <?php if (hasRole('cashier') && !hasRole('admin')): ?>
        <div class="nav-section-title">Management</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a></div>
        <div class="nav-section-title">SALES</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link"><i class="fas fa-cash-register"></i> POS System</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link active"><i class="fas fa-receipt"></i> Orders</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <?php endif; ?>

        <?php if (hasRole('kitchen') && !hasRole('admin') && !hasRole('cashier')): ?>
        <div class="nav-section-title">KITCHEN</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link active"><i class="fas fa-receipt"></i> Orders</a></div>
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
                        <?php if (hasRole('admin') || hasRole('cashier')): ?>
                        <button class="filter-tab filter-tab-voided" data-status="voided">
                            <i class="fas fa-ban me-1"></i> Voided
                            <span class="filter-count">0</span>
                        </button>
                        <?php endif; ?>
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

            <!-- Date filter row -->
            <div class="date-filter-row">
                <div class="date-shortcuts-group">
                    <button type="button" class="date-shortcut-btn" id="btnAll">
                        <i class="fas fa-th-list me-1"></i> All
                    </button>
                    <button type="button" class="date-shortcut-btn" id="btnToday">
                        <i class="fas fa-calendar-day me-1"></i> Today
                    </button>
                    <button type="button" class="date-shortcut-btn" id="btnYesterday">
                        <i class="fas fa-rotate-left me-1"></i> Yesterday
                    </button>
                    <button type="button" class="date-shortcut-btn" id="btnThisWeek">
                        <i class="fas fa-calendar-week me-1"></i> This Week
                    </button>
                    <button type="button" class="date-shortcut-btn" id="btnCustom">
                        <i class="fas fa-sliders me-1"></i> Custom
                    </button>
                </div>

                <div class="date-custom-range" id="dateCustomRange" style="display:none;">
                    <i class="fas fa-calendar-alt date-filter-icon"></i>
                    <input type="date" class="date-filter-input" id="dateFrom" title="From">
                    <span class="date-filter-sep">—</span>
                    <input type="date" class="date-filter-input" id="dateTo" title="To">
                </div>

                <?php if (hasRole('admin')): ?>
                <div class="cashier-view-toggle" id="cashierViewToggle">
                    <button type="button" class="cashier-view-btn active" data-view="all">
                        <i class="fas fa-users"></i> All Cashiers
                    </button>
                    <button type="button" class="cashier-view-btn" data-view="mine">
                        <i class="fas fa-user"></i> My Orders
                    </button>
                </div>

                <div id="processedByWrapper" style="display:none;align-items:center;gap:.5rem;">
                    <span style="font-size:.78rem;color:var(--warm-gray);white-space:nowrap;">
                        <i class="fas fa-shield-alt me-1"></i> Processed by:
                    </span>
                    <select id="processedByFilter"
                            class="date-filter-input"
                            style="border-radius:8px;padding:.35rem .8rem;font-size:.82rem;
                                   border:1.5px solid var(--border);color:var(--navy);
                                   background:white;cursor:pointer;min-width:160px;">
                        <option value="0">Cashiers</option>
                    </select>
                </div>
                <?php endif; ?>
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
                    <tbody id="ordersTableBody"></tbody>
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


<!-- ══════════════════════════════════════════
     CANCEL ORDER MODAL
══════════════════════════════════════════ -->
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
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="modal-icon danger"><i class="fas fa-times-circle"></i></div>
                    <h5 class="mb-2">Cancel Order #<span id="cancelOrderNumber"></span>?</h5>
                    <p class="text-muted mb-0" style="font-size:0.88rem;line-height:1.6;">
                        This action cannot be undone.<br>
                        Since the order is <strong>pending</strong>, no stock has been deducted — nothing will be restored.
                    </p>
                </div>
                <div class="mt-3">
                    <label for="cancelOrderReasonSelect" class="form-label fw-semibold"
                           style="font-size:0.82rem;color:var(--navy);">
                        <i class="fas fa-pen-to-square me-1" style="color:var(--primary-light);"></i>
                        Reason for cancelling <span class="text-danger">*</span>
                    </label>
                    <select id="cancelOrderReasonSelect" class="form-select mb-2"
                            style="border-radius:12px;font-size:0.85rem;border-color:var(--border);">
                        <option value="">— Select a reason —</option>
                    </select>
                    <textarea id="cancelOrderReasonText" class="form-control" rows="2" maxlength="255"
                              placeholder="Describe the reason in more detail…"
                              style="border-radius:12px;font-size:0.85rem;resize:none;border-color:var(--border);display:none;"></textarea>
                    <div id="cancelOrderReasonError"
                         style="display:none;font-size:0.78rem;color:var(--danger);margin-top:4px;">
                        Please select or enter a reason.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-1"></i> Go Back
                </button>
                <form method="POST" action="index.php?page=manage_orders" id="cancelOrderForm">
                    <input type="hidden" name="order_id"      id="cancelOrderId">
                    <input type="hidden" name="cancel_reason" id="cancelOrderReasonHidden">
                    <input type="hidden" name="cancel_order"  value="1">
                    <button type="button" id="cancelOrderSubmitBtn" class="btn btn-danger">
                        <i class="fas fa-times-circle me-1"></i> Yes, Cancel Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════
     VOID ORDER MODAL
══════════════════════════════════════════ -->
<div class="modal fade" id="voidOrderModal" tabindex="-1" aria-labelledby="voidOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;border:none;">

            <div class="modal-header">
                <h5 class="modal-title" id="voidOrderModalLabel">
                    <i class="fas fa-ban me-2" style="color:var(--primary-dark);"></i>
                    Void Order
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="modal-icon" style="background:rgba(15,76,92,0.10);color:var(--primary-dark);">
                        <i class="fas fa-ban"></i>
                    </div>
                    <h5 class="mb-1">Void entire Order #<span id="voidOrderNumber"></span>?</h5>
                    <p class="text-muted mb-0" style="font-size:0.85rem;line-height:1.6;">
                        All items are ready but the customer will not pay.<br>
                        <strong>Ingredients are written off as waste</strong> — stock is NOT restored.<br>
                        Sale total becomes <strong>₱0</strong>.
                    </p>
                </div>

                <div class="mb-3">
                    <label for="voidOrderReasonSelect" class="form-label fw-semibold"
                           style="font-size:0.82rem;color:var(--navy);">
                        <i class="fas fa-pen-to-square me-1" style="color:var(--primary-light);"></i>
                        Reason for voiding <span class="text-danger">*</span>
                    </label>
                    <select id="voidOrderReasonSelect" class="form-select mb-2"
                            style="border-radius:12px;font-size:0.85rem;border-color:var(--border);">
                        <option value="">— Select a reason —</option>
                    </select>
                    <textarea id="voidOrderReasonText" class="form-control" rows="2" maxlength="255"
                              placeholder="Describe the reason in more detail…"
                              style="border-radius:12px;font-size:0.85rem;resize:none;border-color:var(--border);display:none;"></textarea>
                    <div id="voidOrderReasonError"
                         style="display:none;font-size:0.78rem;color:var(--danger);margin-top:4px;">
                        Please select or enter a reason.
                    </div>
                </div>

                <!-- Refund dropdown -->
                <div id="voidRefundToggleRow"
                     style="border:1.5px solid var(--border);border-radius:12px;overflow:hidden;">
                    <button type="button" id="voidRefundToggleBtn"
                            onclick="toggleVoidRefund()"
                            style="width:100%;display:flex;align-items:center;justify-content:space-between;
                                   padding:0.75rem 1rem;background:var(--light-bg);border:none;
                                   cursor:pointer;font-family:inherit;transition:background 0.15s;">
                        <div style="display:flex;align-items:center;gap:0.6rem;">
                            <div style="width:28px;height:28px;background:rgba(214,64,69,0.10);
                                        border-radius:8px;display:flex;align-items:center;
                                        justify-content:center;color:var(--danger);font-size:0.85rem;">
                                <i class="fas fa-rotate-left"></i>
                            </div>
                            <div style="text-align:left;">
                                <div style="font-size:0.85rem;font-weight:700;color:var(--navy);">
                                    Issue a Refund
                                    <span style="font-size:0.75rem;font-weight:400;color:var(--warm-gray);margin-left:0.3rem;">(optional)</span>
                                </div>
                                <div id="voidRefundPaidHint" style="font-size:0.72rem;color:var(--warm-gray);margin-top:1px;">
                                    Loading payment info…
                                </div>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down" id="voidRefundChevron"
                           style="color:var(--warm-gray);font-size:0.8rem;transition:transform 0.2s;"></i>
                    </button>

                    <div id="voidRefundPanel" style="display:none;padding:1rem;border-top:1.5px solid var(--border);background:white;">
                        <div style="margin-bottom:0.75rem;">
                            <div style="font-size:0.73rem;font-weight:700;color:var(--warm-gray);
                                        text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.4rem;">
                                Quick select
                            </div>
                            <div id="voidRefundPresets" style="display:flex;gap:0.4rem;flex-wrap:wrap;"></div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.65rem;margin-bottom:0.65rem;">
                            <div>
                                <label style="font-size:0.75rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.3rem;">
                                    Refund Amount
                                </label>
                                <div style="position:relative;">
                                    <span style="position:absolute;left:0;top:0;bottom:0;width:34px;
                                                 display:flex;align-items:center;justify-content:center;
                                                 font-weight:700;color:var(--warm-gray);font-size:0.9rem;
                                                 background:var(--light-bg);border-radius:10px 0 0 10px;
                                                 border:1.5px solid var(--border);border-right:none;">₱</span>
                                    <input type="number" id="voidRefundAmount"
                                           min="0" step="0.01" placeholder="0.00"
                                           style="width:100%;padding:0.5rem 0.6rem 0.5rem 2.2rem;
                                                  border:1.5px solid var(--border);border-radius:10px;
                                                  font-size:0.88rem;font-family:inherit;outline:none;transition:border-color 0.15s;">
                                </div>
                                <div id="voidRefundError"
                                     style="font-size:0.72rem;color:var(--danger);margin-top:3px;min-height:1rem;"></div>
                            </div>
                            <div>
                                <label style="font-size:0.75rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.3rem;">
                                    Method
                                </label>
                                <select id="voidRefundMethod"
                                        style="width:100%;padding:0.5rem 0.7rem;border:1.5px solid var(--border);border-radius:10px;
                                               font-size:0.85rem;font-family:inherit;background:white;color:var(--dark);outline:none;">
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                        </div>

                        <div style="margin-bottom:0.5rem;">
                            <label style="font-size:0.75rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.3rem;">
                                Note <span style="font-weight:400;color:var(--warm-gray);">(optional)</span>
                            </label>
                            <input type="text" id="voidRefundNote" maxlength="200"
                                   placeholder="e.g. Customer changed mind…"
                                   style="width:100%;padding:0.5rem 0.7rem;border:1.5px solid var(--border);border-radius:10px;
                                          font-size:0.83rem;font-family:inherit;outline:none;transition:border-color 0.15s;">
                        </div>

                        <div id="voidRefundFeedback"
                             style="display:none;margin-top:0.5rem;padding:0.45rem 0.75rem;border-radius:8px;font-size:0.78rem;font-weight:600;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-1"></i> Go Back
                </button>
                <button type="button" class="btn" id="confirmVoidOrderBtn"
                        style="background:var(--primary);color:white;" data-order-id="" data-order-number="">
                    <i class="fas fa-ban me-1"></i> Yes, Void Order
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════
     MARK AS SERVED MODAL
══════════════════════════════════════════ -->
<div class="modal fade" id="markServedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;border:none;">
            <div style="background:linear-gradient(135deg,var(--success),#1f5d43);
                        padding:1.4rem 1.4rem 1rem;text-align:center;">
                <div style="width:52px;height:52px;background:rgba(255,255,255,0.18);border-radius:16px;
                             display:flex;align-items:center;justify-content:center;font-size:1.5rem;
                             color:white;margin:0 auto 0.75rem;">
                    <i class="fas fa-concierge-bell"></i>
                </div>
                <div style="font-size:1rem;font-weight:700;color:white;line-height:1.3;">Mark as Served?</div>
                <div style="font-size:0.78rem;color:rgba(255,255,255,0.75);margin-top:0.25rem;">
                    Order #<span id="markServedOrderNumber" style="font-weight:700;"></span>
                </div>
            </div>
            <div class="modal-body text-center" style="padding:1.1rem 1.4rem 0.6rem;">
                <p class="mb-0 text-muted" style="font-size:0.85rem;line-height:1.6;">
                    This will mark the order as <strong>completed</strong>.<br>
                    Payment was already collected at the POS.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 pb-3 gap-2 justify-content-center px-4">
                <button type="button" class="btn btn-outline-secondary flex-fill"
                        style="border-radius:12px;" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn flex-fill" id="confirmMarkServedBtn"
                        style="background:var(--success);color:white;border-radius:12px;font-weight:700;">
                    <i class="fas fa-check me-1"></i> Yes, Serve It
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════
     KITCHEN ITEMS MODAL
══════════════════════════════════════════ -->
<div class="modal fade" id="kitchenItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content" style="border-radius:24px;border:none;overflow:hidden;">
            <div class="modal-header kitchen-modal-header" id="kitchenModalHeader"
                 style="background:var(--primary);color:white;padding:1.2rem 1.6rem;
                        border-bottom:none;display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
            </div>
            <div class="modal-body" style="padding:1.4rem 1.6rem;max-height:65vh;overflow-y:auto;">
                <div id="kitchenModalBody"></div>
            </div>
            <div class="modal-footer" style="padding:1rem 1.6rem;border-top:1px solid var(--border);">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════
     ORDER DETAILS MODAL
══════════════════════════════════════════ -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content od-modal-content">

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

                    <!-- Footer grid -->
                    <div class="od-footer-grid">

                        <!-- Payment (completed) -->
                        <div id="detailPaymentSection" class="od-payment-card" style="display:none;">
                            <div class="od-payment-title">
                                <i class="fas fa-money-bill-wave me-1"></i> Payment
                            </div>
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
                            <div class="od-payment-row" id="detailProcessedByRow" style="display:none;">
                                <span style="color:var(--warm-gray);font-size:0.8rem;">
                                    <i class="fas fa-shield-halved me-1" style="color:var(--primary-light);"></i> Processed by
                                </span>
                                <span id="detailProcessedBy" style="font-size:0.82rem;font-weight:600;color:var(--navy);">—</span>
                            </div>
                        </div>

                        <!-- Cancel audit -->
                        <div id="detailCancelSection" class="od-payment-card"
                             style="display:none;border-color:rgba(214,64,69,0.2);">
                            <div class="od-payment-title" style="color:var(--danger);">
                                <i class="fas fa-times-circle me-1"></i> Cancellation Info
                            </div>
                            <div class="od-payment-row">
                                <span style="color:var(--warm-gray);font-size:0.8rem;">Reason</span>
                                <span id="detailCancelReason"
                                      style="font-size:0.82rem;font-weight:600;color:var(--danger);max-width:60%;text-align:right;">—</span>
                            </div>
                            <div class="od-payment-row">
                                <span style="color:var(--warm-gray);font-size:0.8rem;">Stock impact</span>
                                <span style="font-size:0.78rem;color:var(--warm-gray);max-width:60%;text-align:right;">
                                    No stock deducted — nothing restored
                                </span>
                            </div>
                        </div>

                        <!-- Void audit -->
                        <div id="detailVoidSection" class="od-payment-card"
                             style="display:none;border-color:rgba(15,76,92,0.2);">
                            <div class="od-payment-title" style="color:var(--primary-dark);">
                                <i class="fas fa-ban me-1"></i> Void Information
                            </div>
                            <div class="od-payment-row">
                                <span style="color:var(--warm-gray);font-size:0.8rem;">Reason</span>
                                <span id="detailVoidReason"
                                      style="font-size:0.82rem;font-weight:600;color:var(--primary-dark);max-width:60%;text-align:right;">—</span>
                            </div>
                            <div class="od-payment-row">
                                <span style="color:var(--warm-gray);font-size:0.8rem;">Waste Cost</span>
                                <span id="detailWasteCost" style="font-size:0.82rem;font-weight:700;color:var(--danger);">—</span>
                            </div>
                            <div class="od-payment-row">
                                <span style="color:var(--warm-gray);font-size:0.8rem;">Stock impact</span>
                                <span style="font-size:0.78rem;color:var(--warm-gray);max-width:60%;text-align:right;">
                                    Consumed — not restored
                                </span>
                            </div>
                        </div>

                        <!-- Void refund (payments table, pre-completion) -->
                        <div id="detailRefundSection" class="od-payment-card"
                             style="display:none;border-color:rgba(214,64,69,0.2);">
                            <div class="od-payment-title" style="color:var(--danger);">
                                <i class="fas fa-rotate-left me-1"></i> Refund Issued (Void)
                            </div>
                            <div class="od-payment-row">
                                <span style="color:var(--warm-gray);font-size:0.8rem;">Amount Refunded</span>
                                <span id="detailRefundAmount"
                                      style="font-size:0.9rem;font-weight:800;color:var(--danger);">—</span>
                            </div>
                            <div class="od-payment-row">
                                <span style="color:var(--warm-gray);font-size:0.8rem;">Method</span>
                                <span id="detailRefundMethod" style="font-size:0.82rem;font-weight:600;color:var(--navy);">—</span>
                            </div>
                            <div class="od-payment-row">
                                <span style="color:var(--warm-gray);font-size:0.8rem;">Note</span>
                                <span id="detailRefundNote"
                                      style="font-size:0.8rem;color:var(--warm-gray);max-width:55%;text-align:right;">—</span>
                            </div>
                            <div class="od-payment-row">
                                <span style="color:var(--warm-gray);font-size:0.75rem;">Audit</span>
                                <span style="font-size:0.72rem;color:var(--warm-gray);">Logged in payments table</span>
                            </div>
                        </div>

                        <!-- Post-completion refunds (refunds table) -->
                        <div id="detailRefundsHistorySection" class="od-payment-card"
                             style="display:none;border-color:rgba(214,64,69,0.2);">
                            <div class="od-payment-title" style="color:var(--danger);">
                                <i class="fas fa-rotate-left me-1"></i> Refunds Issued
                            </div>
                            <div id="detailRefundsList"></div>
                            <div class="od-payment-row"
                                 style="border-top:1.5px solid rgba(214,64,69,0.15);margin-top:6px;padding-top:8px;">
                                <span style="font-weight:700;color:var(--navy);">Total Refunded</span>
                                <span id="detailRefundsTotalAmount"
                                      style="font-weight:800;color:var(--danger);font-size:0.95rem;">₱0.00</span>
                            </div>
                        </div>

                        <!-- Totals -->
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

                    </div><!-- /.od-footer-grid -->
                </div><!-- /#orderDetailsContent -->

                <!-- Error -->
                <div id="orderDetailsError"
                     style="display:none;text-align:center;padding:3rem 1.5rem;color:var(--danger);">
                    <i class="fas fa-exclamation-circle" style="font-size:2.5rem;margin-bottom:0.8rem;display:block;"></i>
                    <p class="fw-semibold mb-0">Failed to load order details. Please try again.</p>
                </div>
            </div>

            <div class="modal-footer"
                 style="border-top:1px solid var(--border);padding:1.1rem 1.6rem;border-radius:0 0 24px 24px;gap:0.5rem;flex-wrap:wrap;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <a id="printReceiptBtn" href="#" target="_blank" class="btn btn-primary" style="display:none;">
                    <i class="fas fa-print me-1"></i> Print Receipt
                </a>
                <?php if (hasRole('admin')): ?>
                <button type="button" id="refundOrderBtn"
                        style="display:none;background:var(--success);color:white;border:none;border-radius:8px;
                               padding:0.5rem 1.1rem;font-size:0.85rem;font-weight:700;font-family:inherit;
                               cursor:pointer;align-items:center;gap:0.4rem;">
                    <i class="fas fa-rotate-left me-1"></i> Issue Refund
                </button>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════
     CANCEL / VOID ITEM MODAL
══════════════════════════════════════════ -->
<div class="modal fade" id="cancelVoidItemModal" tabindex="-1" aria-labelledby="cancelVoidItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelVoidItemModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="modal-icon" id="cancelVoidItemIcon"></div>
                    <h5 class="mb-2" id="cancelVoidItemHeading"></h5>
                    <p class="text-muted mb-0" id="cancelVoidItemBody" style="font-size:0.88rem;line-height:1.6;"></p>
                </div>
                <div class="mt-3">
                    <label for="cancelVoidItemReasonSelect" class="form-label fw-semibold"
                           style="font-size:0.82rem;color:var(--navy);">
                        <i class="fas fa-pen-to-square me-1" style="color:var(--primary-light);"></i>
                        Reason <span class="text-danger">*</span>
                    </label>
                    <select id="cancelVoidItemReasonSelect" class="form-select mb-2"
                            style="border-radius:12px;font-size:0.85rem;border-color:var(--border);">
                        <option value="">— Select a reason —</option>
                    </select>
                    <textarea id="cancelVoidItemReasonText" class="form-control" rows="2" maxlength="255"
                              placeholder="Describe the reason in more detail…"
                              style="border-radius:12px;font-size:0.85rem;resize:none;border-color:var(--border);display:none;"></textarea>
                    <div id="cancelVoidItemReasonError"
                         style="display:none;font-size:0.78rem;color:var(--danger);margin-top:4px;">
                        Please select or enter a reason.
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-1"></i> Go Back
                </button>
                <button type="button" class="btn" id="confirmCancelVoidItemBtn"
                        data-order-item-id="" data-action="" data-item-name=""></button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════
     POST-COMPLETION REFUND MODAL (admin only)
══════════════════════════════════════════ -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:520px;">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;border:none;">

            <div class="modal-header" style="background:linear-gradient(135deg,var(--success),#1f5d43);padding:1.2rem 1.6rem;">
                <h5 class="modal-title" style="color:white;display:flex;align-items:center;gap:0.5rem;">
                    <i class="fas fa-rotate-left" style="color:rgba(255,255,255,0.85);"></i>
                    Issue Refund — Order #<span id="refundModalOrderNumber" style="font-weight:500;opacity:0.85;"></span>
                </h5>
                <button type="button" class="btn-close" style="filter:brightness(0) invert(1);" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding:1.4rem 1.6rem;">

                <!-- Summary strip -->
                <div id="refundOrderSummary"
                     style="background:var(--light-bg);border-radius:12px;padding:0.75rem 1rem;
                            margin-bottom:1.1rem;font-size:0.83rem;display:flex;
                            justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
                    <div>
                        <span style="color:var(--warm-gray);">Order Total</span>
                        <strong id="refundOrderTotal" style="color:var(--navy);margin-left:0.4rem;">₱0.00</strong>
                    </div>
                    <div>
                        <span style="color:var(--warm-gray);">Already Refunded</span>
                        <strong id="refundAlreadyRefunded" style="color:var(--danger);margin-left:0.4rem;">₱0.00</strong>
                    </div>
                    <div>
                        <span style="color:var(--warm-gray);">Remaining</span>
                        <strong id="refundRemaining" style="color:var(--success);margin-left:0.4rem;">₱0.00</strong>
                    </div>
                </div>

                <!-- Type toggle -->
                <div style="margin-bottom:1rem;">
                    <label style="font-size:0.8rem;font-weight:700;color:var(--navy);text-transform:uppercase;
                                  letter-spacing:0.4px;display:block;margin-bottom:0.5rem;">Refund Type</label>
                    <div style="display:flex;gap:0.5rem;">
                        <button type="button" class="refund-type-btn active" data-type="full"
                                style="flex:1;padding:0.55rem;border-radius:10px;font-size:0.83rem;
                                       font-weight:700;border:2px solid var(--success);
                                       background:var(--success);color:white;cursor:pointer;font-family:inherit;">
                            <i class="fas fa-check-double me-1"></i> Full Refund
                        </button>
                        <button type="button" class="refund-type-btn" data-type="partial"
                                style="flex:1;padding:0.55rem;border-radius:10px;font-size:0.83rem;
                                       font-weight:700;border:2px solid var(--border);
                                       background:white;color:var(--navy);cursor:pointer;font-family:inherit;">
                            <i class="fas fa-list-check me-1"></i> Partial (per item)
                        </button>
                    </div>
                </div>

                <!-- Full panel -->
                <div id="refundFullPanel">
                    <div style="background:rgba(44,122,90,0.07);border:1.5px solid rgba(44,122,90,0.25);
                                border-radius:12px;padding:0.85rem 1rem;font-size:0.85rem;
                                color:var(--success);font-weight:600;text-align:center;">
                        <i class="fas fa-info-circle me-1"></i>
                        Refund of <span id="refundFullAmount" style="font-size:1rem;">₱0.00</span> will be issued.
                    </div>
                </div>

                <!-- Partial panel -->
                <div id="refundPartialPanel" style="display:none;">
                    <div style="font-size:0.78rem;font-weight:700;color:var(--warm-gray);
                                text-transform:uppercase;letter-spacing:0.4px;margin-bottom:0.5rem;">
                        Select items to refund
                    </div>
                    <div id="refundItemsList"
                         style="border:1.5px solid var(--border);border-radius:12px;overflow:hidden;"></div>
                    <div style="display:flex;justify-content:flex-end;margin-top:0.5rem;
                                font-size:0.82rem;color:var(--warm-gray);">
                        Selected total:
                        <strong id="refundPartialTotal" style="color:var(--danger);margin-left:0.3rem;">₱0.00</strong>
                    </div>
                </div>

                <!-- Reason -->
                <div style="margin-top:1rem;">
                    <label style="font-size:0.8rem;font-weight:700;color:var(--navy);display:block;margin-bottom:0.4rem;">
                        Reason <span class="text-danger">*</span>
                    </label>
                    <select id="refundReasonSelect"
                            style="width:100%;padding:0.55rem 0.75rem;border:1.5px solid var(--border);
                                   border-radius:10px;font-size:0.85rem;font-family:inherit;
                                   background:white;color:var(--dark);outline:none;margin-bottom:0.5rem;">
                        <option value="">— Select a reason —</option>
                        <option value="Customer complaint">Customer complaint</option>
                        <option value="Wrong item served">Wrong item served</option>
                        <option value="Quality issue">Quality issue</option>
                        <option value="Overcharge">Overcharge</option>
                        <option value="Other">Other</option>
                    </select>
                    <textarea id="refundReasonText" rows="2" maxlength="255"
                              placeholder="Additional details (optional)…"
                              style="width:100%;padding:0.55rem 0.75rem;border:1.5px solid var(--border);
                                     border-radius:10px;font-size:0.83rem;font-family:inherit;
                                     resize:none;outline:none;display:none;"></textarea>
                    <div id="refundReasonError"
                         style="display:none;font-size:0.76rem;color:var(--danger);margin-top:3px;">
                        Please select a reason.
                    </div>
                </div>

                <!-- Method -->
                <div style="margin-top:0.75rem;">
                    <label style="font-size:0.8rem;font-weight:700;color:var(--navy);display:block;margin-bottom:0.4rem;">
                        Refund Method
                    </label>
                    <select id="refundMethod"
                            style="width:100%;padding:0.55rem 0.75rem;border:1.5px solid var(--border);
                                   border-radius:10px;font-size:0.85rem;font-family:inherit;
                                   background:white;color:var(--dark);outline:none;">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank-transfer">Bank Transfer</option>
                    </select>
                </div>

                <!-- Waste Cost Information -->
                <div id="refundWasteInfo" style="display:none;margin-top:0.75rem;">
                    <div style="background:rgba(255,107,74,0.08);border:1.5px solid rgba(255,107,74,0.25);
                                border-radius:12px;padding:0.85rem 1rem;font-size:0.83rem;">
                        <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                            <i class="fas fa-trash-alt" style="color:var(--secondary);"></i>
                            <strong style="color:var(--secondary);">Waste Cost Calculation</strong>
                        </div>
                        <div id="refundWasteItems" style="margin-bottom:0.5rem;color:var(--dark);"></div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="color:var(--warm-gray);font-weight:600;">Total Waste Cost:</span>
                            <strong id="refundTotalWasteCost" style="color:var(--secondary);font-size:0.95rem;">₱0.00</strong>
                        </div>
                        <div style="margin-top:0.4rem;font-size:0.76rem;color:var(--warm-gray);">
                            <i class="fas fa-info-circle me-1"></i>
                            Ingredients already consumed — cost written off as waste
                        </div>
                    </div>
                </div>

                <!-- Error -->
                <div id="refundModalError"
                     style="display:none;margin-top:0.85rem;padding:0.6rem 0.85rem;
                            background:rgba(214,64,69,0.08);border:1.5px solid rgba(214,64,69,0.25);
                            border-radius:10px;font-size:0.82rem;color:var(--danger);font-weight:600;"></div>

            </div>

            <div class="modal-footer" style="padding:1rem 1.6rem;border-top:1px solid var(--border);">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" id="confirmRefundBtn"
                        style="background:var(--success);color:white;border:none;border-radius:10px;
                               padding:0.55rem 1.3rem;font-size:0.88rem;font-weight:700;
                               font-family:inherit;cursor:pointer;display:flex;align-items:center;gap:0.4rem;">
                    <i class="fas fa-rotate-left"></i> Confirm Refund
                </button>
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


<!-- FINAL RECEIPT MODAL -->
<div class="modal fade" id="finalReceiptModal" tabindex="-1" aria-labelledby="finalReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:420px;">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;">
            <div class="modal-header"
                 style="background:linear-gradient(135deg,#1a202c,#2d3748);border-radius:20px 20px 0 0;padding:1.1rem 1.4rem;">
                <h5 class="modal-title" id="finalReceiptModalLabel"
                    style="color:white;display:flex;align-items:center;gap:0.5rem;">
                    <i class="fas fa-receipt" style="color:#fbbf24;"></i>
                    Official Receipt
                    <span id="finalReceiptOrderNumber"
                          style="font-weight:500;opacity:0.7;font-size:0.88rem;margin-left:4px;"></span>
                </h5>
                <button type="button" class="btn-close" style="filter:brightness(0) invert(1);"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:1.2rem;background:#f5f5f0;">
                <div id="finalReceiptPrintArea"
                     style="background:white;font-family:'Courier New',Courier,monospace;
                            font-size:11px;line-height:1.55;color:#000;
                            padding:1rem;border:1px dashed #ccc;border-radius:8px;
                            max-height:480px;overflow-y:auto;"></div>
                <div style="display:flex;gap:0.6rem;justify-content:center;margin-top:1rem;">
                    <button onclick="_printReceiptHTML(document.getElementById('finalReceiptPrintArea').innerHTML)"
                            class="btn btn-primary"
                            style="border-radius:12px;font-weight:700;display:flex;align-items:center;gap:0.5rem;">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            style="border-radius:12px;font-weight:600;">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


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
<script src="<?= $baseUrl ?>/assets/js/common.js?v=<?= time() ?>"></script>
<script src="<?= $baseUrl ?>/assets/js/manage_orders.js?v=<?= time() ?>"></script>
</body>
</html>