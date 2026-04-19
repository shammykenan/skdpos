<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Manage Orders — Dine with Din</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,700;9..144,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/manage_orders.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/common.css">
</head>
<body>

<!-- ── TOPBAR ─────────────────────────────────────────────────────────────── -->
<header class="topbar">
    <button class="mobile-toggle" id="menuToggle" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>
    <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="topbar-brand">
        <div class="brand-logo">
            <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="Dine with Din Logo">
        </div>
        <div>
            <div class="brand-text-main">SKD Software Solutions</div>
            <div class="brand-text-sub">POS with Recipe-Based Inventory Tracking</div>
        </div>
    </a>
    <div class="topbar-spacer"></div>
    <div class="dropdown">
        <button class="user-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border:none;">
            <span class="user-avatar">
                <?php
                    $fullName = $_SESSION['full_name'] ?? 'Admin';
                    $parts    = explode(' ', trim($fullName));
                    $initials = strtoupper(substr($parts[0], 0, 1));
                    if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
                    echo htmlspecialchars($initials);
                ?>
            </span>
            <span class="user-name-text"><?= htmlspecialchars($fullName) ?></span>
            <i class="fas fa-chevron-down user-caret"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <div class="px-3 py-2" style="border-bottom:1px solid var(--border);margin-bottom:0.4rem;">
                    <div style="font-weight:700;font-size:0.9rem;color:var(--navy);"><?= htmlspecialchars($fullName) ?></div>
                    <div style="font-size:0.78rem;color:var(--warm-gray);"><?= ucfirst($_SESSION['role'] ?? 'Staff') ?></div>
                </div>
            </li>
            <li>
                <a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</header>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ── SIDEBAR ────────────────────────────────────────────────────────────── -->
<nav class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="nav-wrap">

        <?php if (hasRole('admin')): ?>
        <div class="nav-section">Dashboard</div>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>

        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_menu"        class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_recipes"     class="nav-link"><i class="fas fa-tags"></i> Recipes</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_addons"      class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables"      class="nav-link"><i class="fas fa-chair"></i> Tables</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_users"       class="nav-link"><i class="fas fa-users"></i> User Management</a>

        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"    class="nav-link"><i class="fas fa-cash-register"></i> POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link active"><i class="fas fa-receipt"></i> Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"       class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a>
        <div class="nav-section">Configuration</div>
        <a href="<?= $baseUrl ?>/index.php?page=bir_settings" class="nav-link"><i class="fa-solid fa-building"></i>BIR Settings</a>
        <?php endif; ?>

        <?php if (hasRole('cashier') && !hasRole('admin')): ?>
        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a>

        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"    class="nav-link"><i class="fas fa-cash-register"></i> POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link active"><i class="fas fa-receipt"></i> Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"       class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a>
        <?php endif; ?>

        <?php if (hasRole('kitchen') && !hasRole('admin') && !hasRole('cashier')): ?>
        <div class="nav-section">Kitchen</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link active"><i class="fas fa-receipt"></i> Orders</a>
        <?php endif; ?>

    </div>
</nav>

<!-- ── MAIN ───────────────────────────────────────────────────────────────── -->
<main class="main" id="mainContent">

    <!-- Page header -->
    <div class="page-head">
        <div>
            <h1 class="page-title">Manage Orders</h1>
            <div class="breadcrumb-bar">
                <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.6rem;"></i></span>
                <span>Orders</span>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card fade-up fade-up-1">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div>
                <div class="stat-label">All Orders</div>
                <div class="stat-value" id="statAll">—</div>
            </div>
        </div>
        <div class="stat-card c-warning fade-up fade-up-2">
            <div class="stat-icon c-warning"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <div class="stat-label">Pending</div>
                <div class="stat-value" id="statPending">—</div>
            </div>
        </div>
        <div class="stat-card c-info fade-up fade-up-3">
            <div class="stat-icon c-info"><i class="fas fa-bowl-food"></i></div>
            <div>
                <div class="stat-label">Preparing</div>
                <div class="stat-value" id="statPreparing">—</div>
            </div>
        </div>
        <div class="stat-card c-success fade-up fade-up-4">
            <div class="stat-icon c-success"><i class="fas fa-circle-check"></i></div>
            <div>
                <div class="stat-label">Ready</div>
                <div class="stat-value" id="statReady">—</div>
            </div>
        </div>
    </div>

    <!-- Filter toolbar -->
    <div class="filter-toolbar fade-up fade-up-5">
        <!-- Row 1: tabs + search -->
        <div class="filter-row">
            <div class="filter-tabs">
                <button class="filter-tab active" data-status="all">
                    <i class="fas fa-th"></i> All
                    <span class="filter-count">0</span>
                </button>
                <button class="filter-tab" data-status="pending">
                    <i class="fas fa-hourglass-half"></i> Pending
                    <span class="filter-count">0</span>
                </button>
                <button class="filter-tab" data-status="preparing">
                    <i class="fas fa-bowl-food"></i> Preparing
                    <span class="filter-count">0</span>
                </button>
                <button class="filter-tab" data-status="ready">
                    <i class="fas fa-circle-check"></i> Ready
                    <span class="filter-count">0</span>
                </button>
                <button class="filter-tab" data-status="completed">
                    <i class="fas fa-check-circle"></i> Completed
                    <span class="filter-count">0</span>
                </button>
                <?php if (hasRole('admin') || hasRole('cashier')): ?>
                <button class="filter-tab tab-voided" data-status="voided">
                    <i class="fas fa-ban"></i> Voided
                    <span class="filter-count">0</span>
                </button>
                <?php endif; ?>
            </div>
            <div class="search-wrap ms-auto">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Search order #, customer, table…">
            </div>
        </div>

        <!-- Row 2: date filters + cashier toggle -->
        <div class="filter-row">
            <div class="date-shortcuts">
                <button type="button" class="date-shortcut-btn" id="btnAll"><i class="fas fa-th-list me-1"></i>All</button>
                <button type="button" class="date-shortcut-btn" id="btnToday"><i class="fas fa-calendar-day me-1"></i>Today</button>
                <button type="button" class="date-shortcut-btn" id="btnYesterday"><i class="fas fa-rotate-left me-1"></i>Yesterday</button>
                <button type="button" class="date-shortcut-btn" id="btnThisWeek"><i class="fas fa-calendar-week me-1"></i>This Week</button>
                <button type="button" class="date-shortcut-btn" id="btnCustom"><i class="fas fa-sliders me-1"></i>Custom</button>
            </div>

            <div class="date-custom-range d-none" id="dateCustomRange">
                <i class="fas fa-calendar-alt" style="color:var(--warm-gray);font-size:0.8rem;"></i>
                <input type="date" class="date-input" id="dateFrom" title="From">
                <span class="date-sep">—</span>
                <input type="date" class="date-input" id="dateTo" title="To">
            </div>

            <?php if (hasRole('admin')): ?>
            <div class="cashier-toggle ms-auto">
                <button type="button" class="cashier-view-btn active" data-view="all">
                    <i class="fas fa-users"></i> All Cashiers
                </button>
                <button type="button" class="cashier-view-btn" data-view="mine">
                    <i class="fas fa-user"></i> My Orders
                </button>
            </div>

            <div class="filter-select-wrap d-none" id="processedByWrapper">
                <span class="filter-label-sm"><i class="fas fa-shield-alt me-1"></i>Processed by:</span>
                <select id="processedByFilter" class="filter-select">
                    <option value="0">All Cashiers</option>
                </select>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Orders table card -->
    <div class="table-card fade-up fade-up-5">
        <div class="table-card-header">
            <div class="table-card-title">
                <i class="fas fa-list" style="color:var(--primary-light);"></i>
                <span id="currentFilterLabel">All Orders</span>
                <span class="orders-count-badge" id="orderCountBadge">0 orders</span>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Items</th>
                        <th>Subtotal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody"></tbody>
            </table>
            <div class="empty-state d-none" id="noResultsMessage">
                <div class="empty-state-icon"><i class="fas fa-receipt"></i></div>
                <h3>No Orders Found</h3>
                <p>Try adjusting your filters or search terms.</p>
            </div>
        </div>
        <!-- Pagination injected by JS -->
    </div>

</main><!-- /.main -->


<!-- ══════════════════════════════════════════════════════════════════════
     VOID ORDER MODAL
══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="voidOrderModal" tabindex="-1" aria-labelledby="voidOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="voidOrderModalLabel">
                    <i class="fas fa-ban" style="color:var(--primary-dark);font-size:1rem;"></i> Void Order
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="modal-icon primary"><i class="fas fa-ban"></i></div>
                    <h5 class="mb-1" style="font-family:'Fraunces',serif;">Void Order #<span id="voidOrderNumber"></span>?</h5>
                    <p class="text-muted mb-1" style="font-size:0.85rem;line-height:1.6;">
                        This order will be voided. Ingredients written off as waste — stock is <strong>not</strong> restored.<br>
                        <span id="voidPaymentStatus" style="color:var(--primary-dark);font-weight:600;">Checking payment status…</span>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-pen-to-square me-1" style="color:var(--primary-light);"></i> Reason for voiding <span class="text-danger">*</span></label>
                    <select id="voidOrderReasonSelect" class="form-select mb-2">
                        <option value="">— Select a reason —</option>
                    </select>
                    <textarea id="voidOrderReasonText" class="form-control" rows="2" maxlength="255" placeholder="Describe in more detail…" style="resize:none;display:none;"></textarea>
                    <div id="voidOrderReasonError" style="display:none;font-size:0.78rem;color:var(--danger);margin-top:4px;">Please select or enter a reason.</div>
                </div>
                <!-- Refund accordion -->
                <div style="border:1.5px solid var(--border);border-radius:var(--radius-md);overflow:hidden;">
                    <button type="button" id="voidRefundToggleBtn" onclick="toggleVoidRefund()"
                            style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:0.75rem 1rem;background:var(--light-bg);border:none;cursor:pointer;font-family:inherit;transition:background 0.15s;">
                        <div style="display:flex;align-items:center;gap:0.6rem;">
                            <div style="width:28px;height:28px;background:rgba(214,64,69,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--danger);font-size:0.85rem;"><i class="fas fa-rotate-left"></i></div>
                            <div style="text-align:left;">
                                <div style="font-size:0.85rem;font-weight:700;color:var(--navy);">Issue a Refund <span style="font-size:0.75rem;font-weight:400;color:var(--warm-gray);">(optional)</span></div>
                                <div id="voidRefundPaidHint" style="font-size:0.72rem;color:var(--warm-gray);margin-top:1px;">Loading payment info…</div>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down" id="voidRefundChevron" style="color:var(--warm-gray);font-size:0.8rem;transition:transform 0.2s;"></i>
                    </button>
                    <div id="voidRefundPanel" style="display:none;padding:1rem;border-top:1.5px solid var(--border);background:#fff;">
                        <div style="margin-bottom:0.75rem;">
                            <div style="font-size:0.72rem;font-weight:700;color:var(--warm-gray);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.4rem;">Quick select</div>
                            <div id="voidRefundPresets" style="display:flex;gap:0.4rem;flex-wrap:wrap;"></div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.65rem;margin-bottom:0.65rem;">
                            <div>
                                <label class="form-label" style="font-size:0.75rem;">Refund Amount</label>
                                <div style="position:relative;">
                                    <span style="position:absolute;left:0;top:0;bottom:0;width:34px;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--warm-gray);font-size:0.9rem;background:var(--light-bg);border-radius:var(--radius-md) 0 0 var(--radius-md);border:1.5px solid var(--border);border-right:none;">₱</span>
                                    <input type="number" id="voidRefundAmount" min="0" step="0.01" placeholder="0.00" style="width:100%;padding:0.5rem 0.6rem 0.5rem 2.2rem;border:1.5px solid var(--border);border-radius:var(--radius-md);font-size:0.88rem;font-family:inherit;outline:none;transition:border-color 0.15s;">
                                </div>
                                <div id="voidRefundError" style="font-size:0.72rem;color:var(--danger);margin-top:3px;min-height:1rem;"></div>
                            </div>
                            <div>
                                <label class="form-label" style="font-size:0.75rem;">Method</label>
                                <select id="voidRefundMethod" class="form-select"><option value="cash">Cash</option></select>
                            </div>
                        </div>
                        <div>
                            <label class="form-label" style="font-size:0.75rem;">Note <span style="font-weight:400;color:var(--warm-gray);">(optional)</span></label>
                            <input type="text" id="voidRefundNote" maxlength="200" placeholder="e.g. Customer changed mind…" class="form-control" style="font-size:0.83rem;">
                        </div>
                        <div id="voidRefundFeedback" style="display:none;margin-top:0.5rem;padding:0.45rem 0.75rem;border-radius:var(--radius-sm);font-size:0.78rem;font-weight:600;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal"><i class="fas fa-arrow-left me-1"></i> Go Back</button>
                <button type="button" class="btn-danger-solid" id="confirmVoidOrderBtn" data-order-id="" data-order-number=""><i class="fas fa-ban me-1"></i> Yes, Void Order</button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════════
     MARK AS SERVED MODAL
══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="markServedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="overflow:hidden;">
            <div style="background:linear-gradient(135deg,var(--success),#1f5d43);padding:1.4rem 1.4rem 1rem;text-align:center;border-radius:var(--radius-xl) var(--radius-xl) 0 0;">
                <div style="width:52px;height:52px;background:rgba(255,255,255,0.18);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;margin:0 auto 0.75rem;">
                    <i class="fas fa-concierge-bell bell-pulse"></i>
                </div>
                <div style="font-size:1rem;font-weight:700;color:white;">Mark as Served?</div>
                <div style="font-size:0.78rem;color:rgba(255,255,255,0.75);margin-top:0.25rem;">Order #<span id="markServedOrderNumber" style="font-weight:700;"></span></div>
            </div>
            <div class="modal-body text-center" style="padding:1.1rem 1.4rem 0.6rem;">
                <p class="text-muted mb-0" style="font-size:0.85rem;line-height:1.6;">This will mark the order as <strong>completed</strong>.<br>Payment was already collected at the POS.</p>
            </div>
            <div class="modal-footer border-0 pt-0 pb-3 gap-2 justify-content-center px-4">
                <button type="button" class="btn-modal-secondary" style="flex:1;border-radius:var(--radius-md);" data-bs-dismiss="modal">Go Back</button>
                <button type="button" class="btn-success-solid" id="confirmMarkServedBtn" style="flex:1;border-radius:var(--radius-md);">
                    <i class="fas fa-check me-1"></i> Serve
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════════
     KITCHEN ITEMS MODAL
══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="kitchenItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content" style="border-radius:var(--radius-xl);border:none;overflow:hidden;">
            <div class="kitchen-modal-header" id="kitchenModalHeader"></div>
            <div class="kitchen-modal-body" id="kitchenModalBody"></div>
            <div class="modal-footer" style="padding:1rem 1.6rem;border-top:1px solid var(--border);">
                <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════════
     PARTIAL ACTION MODAL (void qty)
══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="partialActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="partialActionTitle">Partial Void</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="partialActionBody" class="text-muted mb-3" style="font-size:0.85rem;"></p>
                <div class="mb-3">
                    <label class="form-label">Quantity <span class="text-muted fw-normal" id="partialQtyMax" style="font-size:0.78rem;"></span></label>
                    <input type="number" id="partialQtyInput" class="form-control" min="1" value="1" style="max-width:120px;">
                    <div id="partialQtyFeedback" style="font-size:0.78rem;margin-top:5px;"></div>
                </div>
                <div id="partialVoidReasonRow" style="display:none;" class="mb-3">
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <select id="partialVoidReasonSelect" class="form-select mb-2"><option value="">— Select a reason —</option></select>
                    <textarea id="partialVoidReasonText" class="form-control" rows="2" maxlength="255" placeholder="Details…" style="resize:none;display:none;"></textarea>
                </div>
                <div id="partialVoidRefundRow" style="display:none;">
                    <div style="border:1.5px solid var(--border);border-radius:var(--radius-md);padding:0.85rem 1rem;">
                        <div style="font-size:0.75rem;font-weight:700;color:var(--navy);margin-bottom:0.5rem;">
                            Issue a Refund <span style="font-weight:400;color:var(--warm-gray);">(optional)</span>
                            <span id="partialRefundHint" style="float:right;font-weight:400;color:var(--warm-gray);"></span>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.6rem;">
                            <div>
                                <label class="form-label" style="font-size:0.73rem;">Amount <span id="partialRefundCap" style="font-weight:400;color:var(--warm-gray);"></span></label>
                                <div style="position:relative;">
                                    <span style="position:absolute;left:0;top:0;bottom:0;width:30px;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--warm-gray);font-size:0.88rem;background:var(--light-bg);border-radius:9px 0 0 9px;border:1.5px solid var(--border);border-right:none;">₱</span>
                                    <input type="number" id="partialRefundAmount" min="0" step="0.01" placeholder="0.00" style="width:100%;padding:0.45rem 0.5rem 0.45rem 1.9rem;border:1.5px solid var(--border);border-radius:9px;font-size:0.85rem;font-family:inherit;outline:none;">
                                </div>
                            </div>
                            <div>
                                <label class="form-label" style="font-size:0.73rem;">Method</label>
                                <select id="partialRefundMethod" class="form-select" style="font-size:0.83rem;"><option value="cash">Cash</option></select>
                            </div>
                        </div>
                        <div style="margin-top:0.55rem;">
                            <label class="form-label" style="font-size:0.73rem;">Note <span style="font-weight:400;color:var(--warm-gray);">(optional)</span></label>
                            <input type="text" id="partialRefundNote" maxlength="200" class="form-control" style="font-size:0.81rem;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmPartialActionBtn" class="btn-danger-solid">Void Qty</button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════════
     UPDATE STATUS MODAL
══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));padding:1.1rem 1.4rem;border-bottom:none;border-radius:var(--radius-xl) var(--radius-xl) 0 0;">
                <div style="display:flex;align-items:center;gap:0.65rem;">
                    <div style="width:34px;height:34px;background:rgba(255,255,255,0.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.9rem;color:white;">
                        <i class="fas fa-arrow-right-long"></i>
                    </div>
                    <h5 class="modal-title" id="updateStatusModalTitle" style="color:white;font-size:0.95rem;font-weight:700;margin:0;max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Update Status</h5>
                </div>
                <button type="button" class="btn-close" style="filter:brightness(0) invert(1);" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="updateStatusModalBody" style="padding:1.2rem 1.4rem;display:flex;flex-direction:column;gap:0;"></div>
            <div class="modal-footer" style="padding:0.85rem 1.4rem;border-top:1px solid var(--border);">
                <button type="button" class="btn-modal-secondary" style="border-radius:10px;" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════════
     ORDER DETAILS MODAL
══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="od-modal-content modal-content">
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
                <div id="orderDetailsLoader" class="text-center py-5">
                    <div class="spinner-border" style="color:var(--primary);width:2.2rem;height:2.2rem;" role="status"></div>
                    <p class="mt-3 text-muted mb-0" style="font-size:0.88rem;">Fetching order details…</p>
                </div>
                <div id="orderDetailsContent" style="display:none;">
                    <div class="od-meta-grid">
                        <div class="od-meta-item"><div class="od-meta-label">Customer</div><div class="od-meta-value" id="detailCustomer">—</div></div>
                        <div class="od-meta-item"><div class="od-meta-label">Date &amp; Time</div><div class="od-meta-value" id="detailDate">—</div></div>
                        <div class="od-meta-item"><div class="od-meta-label">Cashier</div><div class="od-meta-value" id="detailCashier">—</div></div>
                        <div class="od-meta-item"><div class="od-meta-label">Order Type</div><div class="od-meta-value" id="detailOrderType">—</div></div>
                        <div class="od-meta-item"><div class="od-meta-label">Status</div><div class="od-meta-value" id="detailStatus">—</div></div>
                        <div class="od-meta-item" id="detailTableWrap" style="display:none;"><div class="od-meta-label">Table</div><div class="od-meta-value" id="detailTable">—</div></div>
                    </div>
                    <div class="od-items-section">
                        <div class="od-section-label"><i class="fas fa-list me-1"></i> Order Items</div>
                        <table class="od-items-table">
                            <thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Subtotal</th></tr></thead>
                            <tbody id="detailItemsBody"></tbody>
                        </table>
                    </div>
                    <div class="od-footer-grid">
                        <!-- Payment -->
                        <div id="detailPaymentSection" class="od-payment-card" style="display:none;">
                            <div class="od-payment-title"><i class="fas fa-money-bill-wave me-1"></i> Payment</div>
                            <div class="od-payment-row"><span>Method</span><span id="detailPayMethod">—</span></div>
                            <div class="od-payment-row"><span>Amount Paid</span><span>₱<span id="detailAmountPaid">0.00</span></span></div>
                            <div class="od-payment-row"><span>Change</span><span>₱<span id="detailChange">0.00</span></span></div>
                            <div class="od-payment-row" id="detailProcessedByRow" style="display:none;">
                                <span style="color:var(--warm-gray);font-size:0.8rem;"><i class="fas fa-shield-halved me-1" style="color:var(--primary-light);"></i> Processed by</span>
                                <span id="detailProcessedBy" style="font-size:0.82rem;font-weight:600;color:var(--navy);">—</span>
                            </div>
                        </div>
                        <!-- Cancel audit -->
                        <div id="detailCancelSection" class="od-payment-card" style="display:none;border-color:rgba(214,64,69,0.2);">
                            <div class="od-payment-title" style="color:var(--danger);"><i class="fas fa-times-circle me-1"></i> Cancellation</div>
                            <div class="od-payment-row"><span style="color:var(--warm-gray);font-size:0.8rem;">Reason</span><span id="detailCancelReason" style="font-size:0.82rem;font-weight:600;color:var(--danger);max-width:60%;text-align:right;">—</span></div>
                        </div>
                        <!-- Void audit -->
                        <div id="detailVoidSection" class="od-payment-card" style="display:none;border-color:rgba(15,76,92,0.2);">
                            <div class="od-payment-title" style="color:var(--primary-dark);"><i class="fas fa-ban me-1"></i> Void Info</div>
                            <div class="od-payment-row"><span style="color:var(--warm-gray);font-size:0.8rem;">Reason</span><span id="detailVoidReason" style="font-size:0.82rem;font-weight:600;color:var(--primary-dark);max-width:60%;text-align:right;">—</span></div>
                            <div class="od-payment-row"><span style="color:var(--warm-gray);font-size:0.8rem;">Waste Cost</span><span id="detailWasteCost" style="font-size:0.82rem;font-weight:700;color:var(--danger);">—</span></div>
                            <div class="od-payment-row"><span style="color:var(--warm-gray);font-size:0.8rem;">Stock</span><span style="font-size:0.78rem;color:var(--warm-gray);">Consumed — not restored</span></div>
                        </div>
                        <!-- Void refund -->
                        <div id="detailRefundSection" class="od-payment-card" style="display:none;border-color:rgba(214,64,69,0.2);">
                            <div class="od-payment-title" style="color:var(--danger);"><i class="fas fa-rotate-left me-1"></i> Refund Issued (Void)</div>
                            <div class="od-payment-row"><span style="color:var(--warm-gray);font-size:0.8rem;">Amount</span><span id="detailRefundAmount" style="font-size:0.9rem;font-weight:800;color:var(--danger);">—</span></div>
                            <div class="od-payment-row"><span style="color:var(--warm-gray);font-size:0.8rem;">Method</span><span id="detailRefundMethod" style="font-size:0.82rem;font-weight:600;color:var(--navy);">—</span></div>
                            <div class="od-payment-row"><span style="color:var(--warm-gray);font-size:0.8rem;">Note</span><span id="detailRefundNote" style="font-size:0.8rem;color:var(--warm-gray);max-width:55%;text-align:right;">—</span></div>
                        </div>
                        <!-- Post-sale refunds -->
                        <div id="detailRefundsHistorySection" class="od-payment-card" style="display:none;border-color:rgba(214,64,69,0.2);">
                            <div class="od-payment-title" style="color:var(--danger);"><i class="fas fa-rotate-left me-1"></i> Refunds Issued</div>
                            <div id="detailRefundsList"></div>
                            <div class="od-payment-row" style="border-top:1.5px solid rgba(214,64,69,0.15);margin-top:6px;padding-top:8px;">
                                <span style="font-weight:700;color:var(--navy);">Total Refunded</span>
                                <span id="detailRefundsTotalAmount" style="font-weight:800;color:var(--danger);font-size:0.95rem;">₱0.00</span>
                            </div>
                        </div>
                        <!-- Totals -->
                        <div class="od-totals-card">
                            <div class="od-totals-row"><span>Subtotal</span><span>₱<span id="detailSubtotal">0.00</span></span></div>
                            <div class="od-totals-row"><span>VAT</span><span>₱<span id="detailVat">0.00</span></span></div>
                            <div class="od-totals-row od-totals-total"><span>Total</span><span id="detailTotalDisplay">₱<span id="detailTotal">0.00</span></span></div>
                        </div>
                    </div>
                </div>
                <div id="orderDetailsError" style="display:none;text-align:center;padding:3rem 1.5rem;color:var(--danger);">
                    <i class="fas fa-exclamation-circle" style="font-size:2.5rem;margin-bottom:0.8rem;display:block;"></i>
                    <p class="fw-semibold mb-0">Failed to load order details. Please try again.</p>
                </div>
            </div>
            <div class="modal-footer" style="padding:1.1rem 1.6rem;gap:0.5rem;flex-wrap:wrap;">
                <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
                <a id="printReceiptBtn" href="#" class="btn-modal-primary" style="display:none;text-decoration:none;"><i class="fas fa-print me-1"></i> Print Receipt</a>
                <?php if (hasRole('admin')): ?>
                <button type="button" id="refundOrderBtn" class="btn-success-solid" style="display:none;"><i class="fas fa-rotate-left me-1"></i> Issue Refund</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════════
     VOID + REFUND ITEM MODAL
══════════════════════════════════════════════════════════════════════ -->
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
                    <label class="form-label"><i class="fas fa-pen-to-square me-1" style="color:var(--primary-light);"></i> Reason <span class="text-danger">*</span></label>
                    <select id="cancelVoidItemReasonSelect" class="form-select mb-2"><option value="">— Select a reason —</option></select>
                    <textarea id="cancelVoidItemReasonText" class="form-control" rows="2" maxlength="255" placeholder="More detail…" style="resize:none;display:none;"></textarea>
                    <div id="cancelVoidItemReasonError" style="display:none;font-size:0.78rem;color:var(--danger);margin-top:4px;">Please select or enter a reason.</div>
                </div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal"><i class="fas fa-arrow-left me-1"></i> Go Back</button>
                <button type="button" class="btn-modal-primary" id="confirmCancelVoidItemBtn" data-order-item-id="" data-action="" data-item-name=""></button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════════
     POST-COMPLETION REFUND MODAL (admin only)
══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:520px;">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--success),#1f5d43);padding:1.2rem 1.6rem;border-radius:var(--radius-xl) var(--radius-xl) 0 0;">
                <h5 class="modal-title" style="color:white;display:flex;align-items:center;gap:0.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
                    <i class="fas fa-rotate-left" style="color:rgba(255,255,255,0.85);"></i>
                    Issue Refund — Order #<span id="refundModalOrderNumber" style="font-weight:500;opacity:0.85;"></span>
                </h5>
                <button type="button" class="btn-close" style="filter:brightness(0) invert(1);" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:1.4rem 1.6rem;">
                <!-- Summary strip -->
                <div style="background:var(--light-bg);border-radius:var(--radius-md);padding:0.75rem 1rem;margin-bottom:1.1rem;font-size:0.83rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
                    <div><span style="color:var(--warm-gray);">Order Total</span><strong id="refundOrderTotal" style="color:var(--navy);margin-left:0.4rem;">₱0.00</strong></div>
                    <div><span style="color:var(--warm-gray);">Already Refunded</span><strong id="refundAlreadyRefunded" style="color:var(--danger);margin-left:0.4rem;">₱0.00</strong></div>
                    <div><span style="color:var(--warm-gray);">Remaining</span><strong id="refundRemaining" style="color:var(--success);margin-left:0.4rem;">₱0.00</strong></div>
                </div>
                <!-- Type toggle -->
                <div style="margin-bottom:1rem;">
                    <label class="form-label" style="text-transform:uppercase;letter-spacing:0.4px;">Refund Type</label>
                    <div style="display:flex;gap:0.5rem;">
                        <button type="button" class="refund-type-btn active" data-type="full" style="flex:1;padding:0.55rem;border-radius:10px;font-size:0.83rem;font-weight:700;border:2px solid var(--success);background:var(--success);color:white;cursor:pointer;font-family:inherit;"><i class="fas fa-check-double me-1"></i> Full Refund</button>
                        <button type="button" class="refund-type-btn" data-type="partial" style="flex:1;padding:0.55rem;border-radius:10px;font-size:0.83rem;font-weight:700;border:2px solid var(--border);background:white;color:var(--navy);cursor:pointer;font-family:inherit;"><i class="fas fa-list-check me-1"></i> Partial (per item)</button>
                    </div>
                </div>
                <div id="refundFullPanel">
                    <div style="background:rgba(44,122,90,0.07);border:1.5px solid rgba(44,122,90,0.25);border-radius:var(--radius-md);padding:0.85rem 1rem;font-size:0.85rem;color:var(--success);font-weight:600;text-align:center;">
                        <i class="fas fa-info-circle me-1"></i> Refund of <span id="refundFullAmount" style="font-size:1rem;">₱0.00</span> will be issued.
                    </div>
                </div>
                <div id="refundPartialPanel" style="display:none;">
                    <div style="font-size:0.78rem;font-weight:700;color:var(--warm-gray);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:0.5rem;">Select items to refund</div>
                    <div id="refundItemsList" style="border:1.5px solid var(--border);border-radius:var(--radius-md);overflow:hidden;"></div>
                    <div style="display:flex;justify-content:flex-end;margin-top:0.5rem;font-size:0.82rem;color:var(--warm-gray);">Selected total: <strong id="refundPartialTotal" style="color:var(--danger);margin-left:0.3rem;">₱0.00</strong></div>
                </div>
                <div style="margin-top:1rem;">
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <select id="refundReasonSelect" class="form-select" style="margin-bottom:0.5rem;">
                        <option value="">— Select a reason —</option>
                        <option value="Customer complaint">Customer complaint</option>
                        <option value="Wrong item served">Wrong item served</option>
                        <option value="Quality issue">Quality issue</option>
                        <option value="Overcharge">Overcharge</option>
                        <option value="Other">Other</option>
                    </select>
                    <textarea id="refundReasonText" rows="2" maxlength="255" placeholder="Additional details…" class="form-control" style="resize:none;display:none;"></textarea>
                    <div id="refundReasonError" style="display:none;font-size:0.76rem;color:var(--danger);margin-top:3px;">Please select a reason.</div>
                </div>
                <div style="margin-top:0.75rem;">
                    <label class="form-label">Refund Method</label>
                    <select id="refundMethod" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank-transfer">Bank Transfer</option>
                    </select>
                </div>
                <div id="refundModalError" style="display:none;margin-top:0.85rem;padding:0.6rem 0.85rem;background:rgba(214,64,69,0.08);border:1.5px solid rgba(214,64,69,0.25);border-radius:var(--radius-md);font-size:0.82rem;color:var(--danger);font-weight:600;"></div>
            </div>
            <div class="modal-footer" style="padding:1rem 1.6rem;border-top:1px solid var(--border);">
                <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                <button type="button" id="confirmRefundBtn" class="btn-success-solid"><i class="fas fa-rotate-left me-1"></i> Confirm Refund</button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════════
     FINAL RECEIPT MODAL
══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="finalReceiptModal" tabindex="-1" aria-labelledby="finalReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:420px;">
        <div class="modal-content" style="border-radius:var(--radius-xl);overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#1a202c,#2d3748);border-radius:var(--radius-xl) var(--radius-xl) 0 0;padding:1.1rem 1.4rem;">
                <h5 class="modal-title" id="finalReceiptModalLabel" style="color:white;display:flex;align-items:center;gap:0.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
                    <i class="fas fa-receipt" style="color:#fbbf24;"></i>
                    Official Receipt <span id="finalReceiptOrderNumber" style="font-weight:500;opacity:0.7;font-size:0.88rem;margin-left:4px;"></span>
                </h5>
                <button type="button" class="btn-close" style="filter:brightness(0) invert(1);" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:1.2rem;background:#f5f5f0;">
                <div id="finalReceiptPrintArea" style="background:white;font-family:'Courier New',Courier,monospace;font-size:11px;line-height:1.55;color:#000;padding:1rem;border:1px dashed #ccc;border-radius:var(--radius-sm);max-height:480px;overflow-y:auto;"></div>
                <div style="display:flex;gap:0.6rem;justify-content:center;margin-top:1rem;">
                    <button onclick="_printReceiptHTML(document.getElementById('finalReceiptPrintArea').innerHTML)" class="btn-modal-primary">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="btn-modal-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
                </div>
            </div>
        </div>
    </div>
</div>


<?php if (!empty($_SESSION['success'])): ?>
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="modal-icon success"><i class="fas fa-check-circle"></i></div>
                <h5 class="mb-3" style="font-family:'Fraunces',serif;">Success!</h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($_SESSION['success']) ?></p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn-modal-primary" data-bs-dismiss="modal"><i class="fas fa-check me-1"></i> OK</button>
            </div>
        </div>
    </div>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="modal-icon danger"><i class="fas fa-exclamation-circle"></i></div>
                <h5 class="mb-3" style="font-family:'Fraunces',serif;">Error</h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($_SESSION['error']) ?></p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn-danger-solid" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
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
<script src="<?= $baseUrl ?>/assets/js/manage_orders_ui.js?v=<?= time() ?>"></script>
<script>
    (function() {
        // Ensure sidebar toggle is initialized (common.js already defines setupSidebarToggle)
        if (typeof setupSidebarToggle === 'function') {
            setupSidebarToggle();
        } else {
            // Fallback: manually define and call
            function setupSidebarToggle() {
                const menuToggle = document.getElementById('menuToggle');
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                if (menuToggle && sidebar && overlay) {
                    menuToggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        sidebar.classList.toggle('show');
                        overlay.classList.toggle('show');
                        document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
                    });
                    overlay.addEventListener('click', function() {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                        document.body.style.overflow = '';
                    });
                }
            }
            setupSidebarToggle();
        }
        // Also ensure modal backdrop cleanup
        if (typeof setupModalBackdropCleanup === 'function') {
            setupModalBackdropCleanup();
        }
    })();
</script>
</body>
</html>
