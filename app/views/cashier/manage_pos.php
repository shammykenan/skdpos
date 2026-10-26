<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php require_once __DIR__ . "/../../core/csrfHelper.php"; echo csrf_head($baseUrl ?? ""); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS — The Lounge by: CJV & TEK</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="<?= $baseUrl ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/vendor/fontawesome/css/all.min.css">
    <link href="<?= $baseUrl ?>/assets/vendor/fonts/app.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/manage_pos.css">
</head>
<body>

<!-- ADD TOAST -->
<div id="addToast"><i class="fas fa-check-circle"></i><span id="addToastText">Added to order</span></div>

<!-- ════════════════════════════════════════════════════
     TOPBAR
════════════════════════════════════════════════════ -->
<header class="topbar">
    <button class="mobile-toggle" id="menuToggle" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>

    <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="topbar-brand">
        <div class="brand-logo">
            <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="The Lounge by: CJV & TEK Logo">
        </div>
        <div>
            <div class="brand-text-main">The Lounge by: CJV & TEK</div>
            <div class="brand-text-sub">Chill. Coffee. Dine.</div>
        </div>
    </a>

    <div class="topbar-spacer"></div>

    <div class="dropdown">
        <?php
            $fullName = $_SESSION['full_name'] ?? 'Admin';
            $parts    = explode(' ', trim($fullName));
            $initials = strtoupper(substr($parts[0], 0, 1));
            if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
        ?>
        <button class="user-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="user-avatar"><?= htmlspecialchars($initials) ?></span>
            <span class="user-name-text"><?= htmlspecialchars($fullName) ?></span>
            <i class="fas fa-chevron-down user-caret"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <div class="px-3 py-2" style="border-bottom:1px solid var(--border);margin-bottom:0.4rem;">
                    <div style="font-weight:700;font-size:0.9rem;color:var(--navy);"><?= htmlspecialchars($fullName) ?></div>
                    <div style="font-size:0.78rem;color:var(--warm-gray);">
                        <?= hasRole('admin') ? 'Administrator' : 'Cashier' ?>
                    </div>
                </div>
            </li>
            <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a></li>
        </ul>
    </div>
</header>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ════════════════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════════════ -->
<nav class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="nav-wrap">
        <?php if (hasRole('admin')): ?>
        <div class="nav-section">Dashboard</div>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard"   class="nav-link"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_menu"        class="nav-link"><i class="fas fa-utensils"></i>Menu Items</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_recipes"     class="nav-link"><i class="fas fa-tags"></i>Recipes</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i>Ingredients</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_addons"      class="nav-link"><i class="fas fa-plus-circle"></i>Add-ons</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables"      class="nav-link"><i class="fas fa-chair"></i>Tables</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_users"       class="nav-link"><i class="fas fa-users"></i>User Management</a>
        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"         class="nav-link active"><i class="fas fa-cash-register"></i>POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders"      class="nav-link"><i class="fas fa-receipt"></i>Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"            class="nav-link"><i class="fas fa-chart-bar"></i>Reports</a>
        <div class="nav-section">Configuration</div>
        <a href="<?= $baseUrl ?>/index.php?page=bir_settings" class="nav-link"><i class="fa-solid fa-building"></i>BIR Settings</a>
        <?php endif; ?>
        <?php if (hasRole('cashier') && !hasRole('admin')): ?>
        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables"      class="nav-link"><i class="fas fa-chair"></i>Tables</a>
        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"         class="nav-link active"><i class="fas fa-cash-register"></i>POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders"      class="nav-link"><i class="fas fa-receipt"></i>Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"            class="nav-link"><i class="fas fa-chart-bar"></i>Reports</a>
        <?php endif; ?>
    </div>
</nav>

<!-- ════════════════════════════════════════════════════
     MAIN
════════════════════════════════════════════════════ -->
<main class="main" id="mainContent">

    <div class="page-head">
        <div>
            <h1 class="page-title">Point of Sale</h1>
            <div class="breadcrumb-bar">
                <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
                <span>POS</span>
            </div>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert-bar success" role="alert">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button class="dismiss" aria-label="Close">&times;</button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-bar danger" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button class="dismiss" aria-label="Close">&times;</button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="pos-layout fade-up fade-up-1">

        <!-- ════ MENU PANEL ════ -->
        <div class="pos-panel">
            <div class="pos-panel-header">
                <div class="pos-panel-title">
                    <i class="fas fa-utensils"></i>Menu
                </div>
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" id="menuSearch" placeholder="Search items…" autocomplete="off">
                    <button class="search-clear" id="searchClear" onclick="clearSearch()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Category tabs -->
            <div class="category-tabs-wrapper">
                <div class="category-tabs" id="categoryTabs">
                    <button class="category-tab active" data-category="all" title="All (<?= count($menu_items) ?>)">
                        <i class="fas fa-th me-1"></i>All
                    </button>
                    <?php
                        $uniqueCategories = [];
                        $categoryCounts   = [];
                        foreach ($menu_items as $item) {
                            if (!isset($uniqueCategories[$item['category_id']])) {
                                $uniqueCategories[$item['category_id']] = $item['category_name'];
                                $categoryCounts[$item['category_id']]   = 0;
                            }
                            $categoryCounts[$item['category_id']]++;
                        }
                        foreach ($uniqueCategories as $catId => $catName):
                    ?>
                        <button class="category-tab" data-category="<?= $catId ?>" title="<?= htmlspecialchars($catName) ?> (<?= $categoryCounts[$catId] ?>)">
                            <?= htmlspecialchars($catName) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Menu grid -->
            <div class="menu-grid-wrapper">
                <div class="menu-grid" id="menuGrid">
                    <?php foreach ($menu_items as $item):
                        $hasAddons = !empty((array)($addons_map[$item['item_id']] ?? []));
                        $itemStock = $stock_data[$item['item_id']] ?? null;
                        $hasStock  = $item['has_stock'] ?? false;
                    ?>
                    <div class="menu-item-card <?= !$hasStock ? 'out-of-stock' : '' ?>"
                         data-category="<?= $item['category_id'] ?>"
                         data-item-id="<?= $item['item_id'] ?>"
                         data-item-name="<?= htmlspecialchars($item['item_name']) ?>"
                         data-item-price="<?= $item['base_price'] ?>"
                         data-category-id="<?= $item['category_id'] ?>"
                         data-search-text="<?= htmlspecialchars(strtolower($item['item_name'])) ?>"
                         data-has-stock="<?= $hasStock ? 'true' : 'false' ?>"
                         data-no-price="<?= ($item['no_price'] ?? false) ? 'true' : 'false' ?>">

                        <?php
                            $noPrice = $item['no_price'] ?? false;
                            if ($noPrice): ?>
                                <div class="out-of-stock-badge" style="background:var(--warm-gray);">Unavailable</div>
                            <?php elseif (!$hasStock): ?>
                                <div class="out-of-stock-badge">Out of Stock</div>
                        <?php endif; ?>

                        <div class="in-cart-badge" id="cartBadge_<?= $item['item_id'] ?>" style="display:none"></div>

                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?= $baseUrl . '/' . htmlspecialchars($item['image_url']) ?>"
                                 alt="<?= htmlspecialchars($item['item_name']) ?>"
                                 class="menu-item-image" loading="lazy">
                        <?php else: ?>
                            <div class="menu-item-placeholder"><i class="fas fa-utensils"></i></div>
                        <?php endif; ?>

                        <div class="menu-item-body">
                            <div class="menu-item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                            <?php
                                $itemVariants = $variants_map[$item['item_id']] ?? [];
                                if (!empty($itemVariants)) {
                                    $prices   = array_column($itemVariants, 'price');
                                    $minPrice = min($prices);
                                    $maxPrice = max($prices);
                                    $priceDisplay = ($minPrice === $maxPrice)
                                        ? '₱' . number_format($minPrice, 2)
                                        : '₱' . number_format($minPrice, 2) . ' – ₱' . number_format($maxPrice, 2);
                                } else {
                                    $priceDisplay = '₱' . number_format($item['base_price'], 2);
                                }
                            ?>
                            <div class="menu-item-price"><?= $priceDisplay ?></div>
                            <?php if (!empty($item['ingredients'])): ?>
                                <div class="menu-item-desc"><?php
                                    $ingNames = array_map(fn($i) => $i['ingredient_name'], $item['ingredients']);
                                    echo htmlspecialchars(implode(', ', array_slice($ingNames, 0, 3)));
                                    if (count($ingNames) > 3) echo '…';
                                ?></div>
                            <?php elseif (!empty($item['description'])): ?>
                                <div class="menu-item-desc"><?= htmlspecialchars($item['description']) ?></div>
                            <?php endif; ?>
                            <?php if ($hasAddons): ?>
                                <div class="addon-badge"><i class="fas fa-plus"></i>Add-ons</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="no-results d-none" id="noResults">
                    <i class="fas fa-search"></i>
                    <p>No items found</p>
                    <small>Try a different search or category</small>
                </div>
            </div>
        </div><!-- /.pos-panel menu -->

        <!-- ════ CART PANEL (desktop) ════ -->
        <div class="cart-panel fade-up fade-up-2">

            <div class="cart-panel-header">
                <div class="cart-panel-title">
                    <i class="fas fa-shopping-cart"></i>
                    Order
                    <span class="cart-item-count-pill" id="cartCountPill" style="display:none">0</span>
                </div>
                <button class="clear-cart-btn d-none" id="clearCartBtn" onclick="confirmClearCart()">
                    <i class="fas fa-trash-alt"></i>Clear
                </button>
            </div>

            <!-- Fields -->
            <div class="cart-fields">
                <div class="mb-2">
                    <label class="form-label">Order Type <span class="text-danger">*</span></label>
                    <div class="order-type-container">
                        <button type="button" class="order-type-btn active" id="dineInBtn" onclick="setOrderType('dine_in','desktop')">
                            <i class="fas fa-utensils"></i>Dine In<span class="kbd-hint">D</span>
                        </button>
                        <button type="button" class="order-type-btn" id="takeOutBtn" onclick="setOrderType('takeout','desktop')">
                            <i class="fas fa-bag-shopping"></i>Take Out<span class="kbd-hint">T</span>
                        </button>
                    </div>
                    <input type="hidden" id="order_type" value="dine_in">
                </div>
                <div class="mb-2">
                    <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="customer_name" placeholder="e.g. John Santos"
                           autocomplete="name" inputmode="text" required>
                </div>
                <div id="tableNumberContainer">
                    <label class="form-label">Table <span class="text-danger">*</span></label>
                    <?php if (!empty($available_tables)): ?>
                        <select class="table-select" id="table_id">
                            <option value="">— Choose a table —</option>
                            <?php foreach ($available_tables as $t): ?>
                                <option value="<?= $t['table_id'] ?>"><?= htmlspecialchars($t['table_name']) ?> (<?= $t['capacity'] ?> seats)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="table-count-hint">
                            <i class="fas fa-circle-check"></i>
                            <?= count($available_tables) ?> table<?= count($available_tables) !== 1 ? 's' : '' ?> available
                        </div>
                    <?php else: ?>
                        <div class="table-unavailable-notice">
                            <i class="fas fa-exclamation-triangle me-1"></i>No tables available — free a table first.
                        </div>
                        <input type="hidden" id="table_id" value="">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Draft banner -->
            <div id="draftBanner" class="draft-restored-banner d-none">
                <i class="fas fa-clock-rotate-left"></i>
                <button class="draft-dismiss-btn" onclick="dismissDraftBanner()"><i class="fas fa-times"></i></button>
            </div>

            <!-- Cart items -->
            <div class="cart-items-wrapper" id="cartItemsWrapper">
                <div class="cart-items" id="cartItems">
                    <div class="empty-cart">
                        <div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div>
                        <p>Cart is empty</p>
                        <small>Tap a menu item to add it</small>
                    </div>
                </div>
            </div>

            <!-- Cart footer -->
            <div class="cart-footer d-none" id="cartFooter">
                <div class="cart-total-row">
                    <span class="cart-total-label">Order Total</span>
                    <span class="cart-total-amount" id="total">₱0.00</span>
                </div>
                <button type="button" class="collect-payment-btn" id="collectPaymentBtn" disabled onclick="openPaymentModal()">
                    <i class="fas fa-credit-card"></i><span id="collectPaymentBtnText">Collect Payment</span>
                </button>
                <div class="submit-disabled-msg d-none" id="submitDisabledMessage">
                    <i class="fas fa-exclamation-circle"></i>Fix stock issues before proceeding
                </div>
            </div>

        </div><!-- /.cart-panel -->

    </div><!-- /.pos-layout -->

</main>


<!-- ════════════════════════════════════════════════════
     MOBILE BOTTOM BAR
════════════════════════════════════════════════════ -->
<div class="mobile-bottom-bar" id="mobileBottomBar">
    <button class="cart-preview-btn" onclick="openCartDrawer()">
        <div class="cart-preview-icon">
            <i class="fas fa-shopping-cart"></i>
            <div class="cart-badge" id="mobileBadge" style="display:none">0</div>
        </div>
        <div class="cart-preview-info">
            <div class="cart-preview-label" id="mobileCartCount">No items</div>
            <div class="cart-preview-total" id="mobileCartTotal">₱0.00</div>
        </div>
    </button>
    <button class="mobile-place-btn" id="mobileSubmitBtn" onclick="openPaymentModal()" disabled>
        <i class="fas fa-credit-card"></i>Pay
    </button>
</div>

<div class="drawer-backdrop" id="drawerBackdrop" onclick="closeCartDrawer()"></div>

<!-- ════════════════════════════════════════════════════
     MOBILE CART DRAWER
════════════════════════════════════════════════════ -->
<div class="mobile-cart-drawer" id="mobileCartDrawer">
    <div class="drawer-handle"></div>
    <div class="drawer-header">
        <div class="drawer-title">
            <i class="fas fa-shopping-cart" style="color:var(--primary)"></i>
            Your Order
            <span class="cart-item-count-pill" id="cartCountPillM" style="display:none">0</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="clear-cart-btn d-none" id="clearCartBtnM" onclick="confirmClearCart()">
                <i class="fas fa-trash-alt"></i>Clear
            </button>
            <button class="drawer-close" onclick="closeCartDrawer()"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div class="drawer-body">
        <div class="cart-fields">
            <div class="mb-2">
                <label class="form-label">Order Type <span class="text-danger">*</span></label>
                <div class="order-type-container">
                    <button type="button" class="order-type-btn active" id="dineInBtnM" onclick="setOrderType('dine_in','mobile')">
                        <i class="fas fa-utensils"></i>Dine In
                    </button>
                    <button type="button" class="order-type-btn" id="takeOutBtnM" onclick="setOrderType('takeout','mobile')">
                        <i class="fas fa-bag-shopping"></i>Take Out
                    </button>
                </div>
            </div>
            <div id="tableNumberContainerM">
                <label class="form-label">Table <span class="text-danger">*</span></label>
                <?php if (!empty($available_tables)): ?>
                    <select class="table-select" id="table_id_m">
                        <option value="">— Choose a table —</option>
                        <?php foreach ($available_tables as $t): ?>
                            <option value="<?= $t['table_id'] ?>"><?= htmlspecialchars($t['table_name']) ?> (<?= $t['capacity'] ?> seats)</option>
                        <?php endforeach; ?>
                    </select>
                    <div class="table-count-hint">
                        <i class="fas fa-circle-check"></i>
                        <?= count($available_tables) ?> table<?= count($available_tables) !== 1 ? 's' : '' ?> available
                    </div>
                <?php else: ?>
                    <div class="table-unavailable-notice">
                        <i class="fas fa-exclamation-triangle me-1"></i>No tables available.
                    </div>
                    <input type="hidden" id="table_id_m" value="">
                <?php endif; ?>
            </div>
            <div class="mb-2">
                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="customer_name_m" placeholder="e.g. John Santos"
                       autocomplete="name" inputmode="text" required>
            </div>
            <div id="draftBannerM" class="draft-restored-banner d-none" style="margin:0.75rem 0 0;">
                <i class="fas fa-clock-rotate-left"></i>
                <button class="draft-dismiss-btn" onclick="dismissDraftBanner()"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <hr style="margin:0;border-color:#f0f4f8;">

        <div class="cart-items" id="cartItemsMobile">
            <div class="empty-cart">
                <div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div>
                <p>Cart is empty</p>
                <small>Tap a menu item to add it</small>
            </div>
        </div>
    </div>

    <div class="drawer-footer">
        <div class="cart-total-row d-none" id="mobileTotalRow">
            <span class="cart-total-label">Order Total</span>
            <span class="cart-total-amount" id="totalM">₱0.00</span>
        </div>
        <button type="button" class="collect-payment-btn d-none mt-2" id="collectPaymentBtnM" disabled onclick="openPaymentModal()">
            <i class="fas fa-credit-card"></i><span id="collectPaymentBtnTextM">Collect Payment</span>
        </button>
        <div class="submit-disabled-msg d-none" id="submitDisabledMessageM">
            <i class="fas fa-exclamation-circle"></i>Fix stock issues before proceeding
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════
     CUSTOMIZE MODAL
     Variants (size picker) embedded at top when item has sizes.
     Add-ons below. No separate variant modal needed.
════════════════════════════════════════════════════ -->
<div class="modal fade" id="addonsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addonsModalLabel">
                    <i class="fas fa-utensils" style="color:var(--secondary);"></i>
                    <span id="modalItemName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="addonsContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addToCartBtn">
                    <i class="fas fa-cart-plus me-1"></i>Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════
     ITEM NOTE MODAL
════════════════════════════════════════════════════ -->
<div class="modal fade" id="itemNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content">
            <div class="modal-header" style="padding:1rem 1.25rem 0.75rem;">
                <h5 class="modal-title" style="font-size:0.95rem;font-weight:700;color:var(--navy);display:flex;align-items:center;gap:0.5rem;">
                    <i class="fas fa-pen-to-square" style="color:var(--secondary);font-size:0.9rem;"></i>
                    <span id="noteModalItemName">Add Note</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:0.85rem 1.25rem;">
                <p style="font-size:0.78rem;color:var(--warm-gray);margin-bottom:0.65rem;">
                    Note applies only to this item.
                </p>
                <textarea id="noteModalTextarea" rows="4" maxlength="300"
                    placeholder="e.g. No sugar, less ice, extra hot…"
                    style="width:100%;resize:none;border:1.5px solid var(--border);border-radius:10px;padding:0.65rem 0.85rem;font-size:0.88rem;font-family:inherit;outline:none;transition:border-color 0.2s;display:block;"
                    onfocus="this.style.borderColor='var(--primary)'"
                    onblur="this.style.borderColor='var(--border)'"></textarea>
                <div style="font-size:0.72rem;color:var(--warm-gray);margin-top:0.3rem;text-align:right;">
                    <span id="noteModalCharCount">0</span>/300
                </div>
            </div>
            <div class="modal-footer" style="padding:0.65rem 1.25rem 1rem;gap:0.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" id="noteModalClearBtn" onclick="clearNoteFromModal()" style="font-size:0.82rem;">
                    <i class="fas fa-times me-1"></i>Clear Note
                </button>
                <button type="button" class="btn btn-primary btn-sm flex-fill" onclick="saveNoteFromModal()" style="font-size:0.82rem;">
                    <i class="fas fa-check me-1"></i>Save Note
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════
     CLEAR CART CONFIRM MODAL
════════════════════════════════════════════════════ -->
<div class="modal fade" id="clearCartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="modal-icon warning"><i class="fas fa-trash-alt"></i></div>
                <h5 style="font-family:'Fraunces',serif;font-weight:700;color:var(--navy);margin-bottom:0.4rem;">Clear cart?</h5>
                <p style="font-size:0.84rem;color:var(--warm-gray);margin-bottom:0.85rem;">
                    This will remove all <strong id="clearCount">0</strong> item(s) from your current order.
                </p>
                <div class="clear-confirm-list text-start" id="clearConfirmList"></div>
            </div>
            <div class="modal-footer" style="border-top:none;padding-top:0.5rem;gap:0.5rem;">
                <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Keep order</button>
                <button type="button" class="btn btn-danger flex-fill" onclick="executeClearCart()">
                    <i class="fas fa-trash-alt me-1"></i>Yes, clear
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════
     STOCK ERROR MODAL
════════════════════════════════════════════════════ -->
<div class="modal fade" id="stockErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2" style="color:var(--danger);"></i>Insufficient Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon danger"><i class="fas fa-boxes"></i></div>
                <h5 class="mb-2" id="stockErrTitle"></h5>
                <p style="font-size:0.84rem;color:var(--warm-gray);margin-bottom:0.85rem;">The following ingredients don't have enough stock:</p>
                <div class="text-start p-3 rounded" style="background:var(--light-bg);max-height:180px;overflow-y:auto;">
                    <small style="font-size:0.7rem;font-weight:700;text-transform:uppercase;color:var(--warm-gray);">Affected Ingredients</small>
                    <ul class="list-unstyled mt-2 mb-0" id="stockErrList"></ul>
                </div>
            </div>
            <div class="modal-footer" style="border-top:none;padding-top:0;">
                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════
     GENERIC ERROR MODAL
════════════════════════════════════════════════════ -->
<div class="modal fade" id="genericErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2" style="color:var(--danger);"></i>Error
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon danger"><i class="fas fa-exclamation-circle"></i></div>
                <h5 class="mb-2" id="genericErrTitle"></h5>
                <p style="font-size:0.88rem;color:var(--warm-gray);margin-bottom:0;" id="genericErrMsg"></p>
            </div>
            <div class="modal-footer" style="border-top:none;padding-top:0;">
                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════
     PAYMENT MODAL
════════════════════════════════════════════════════ -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content">

            <div class="pm-header">
                <div class="pm-header-icon"><i class="fas fa-cash-register"></i></div>
                <div class="pm-header-text">
                    <div class="pm-header-title">Collect Payment</div>
                    <div class="pm-header-sub" id="pmOrderSummaryLine">Building order…</div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>

            <div class="pm-body">
                <div class="pm-summary">
                    <div class="pm-summary-label"><i class="fas fa-list me-1"></i>Order Summary</div>
                    <div class="pm-summary-items" id="pmSummaryItems">—</div>
                </div>

                <div class="pm-total-card">
                    <div class="pm-total-label">Total Due</div>
                    <div class="pm-total-amount">₱<span id="pmTotalAmount">0.00</span></div>
                </div>

                <!-- ── Discount Section ── -->
                <div id="pmDiscountSection" style="margin:0 0 1rem;">
                    <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--warm-gray);margin-bottom:0.5rem;">
                        <i class="fas fa-tag me-1"></i>Discount
                    </div>
                    <div style="display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:0.6rem;">
                        <button class="pm-disc-btn active" data-type="none"   onclick="setDiscountType('none')">None</button>
                        <button class="pm-disc-btn"        data-type="senior" onclick="setDiscountType('senior')">Senior (20%)</button>
                        <button class="pm-disc-btn"        data-type="pwd"    onclick="setDiscountType('pwd')">PWD (20%)</button>
                        <button class="pm-disc-btn"        data-type="promo"  onclick="setDiscountType('promo')">Promo</button>
                    </div>

                    <div id="pmDiscRefWrap" style="display:none;margin-bottom:0.5rem;">
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <input type="text" id="pmDiscRef" class="pm-cash-input"
                                   style="font-size:0.88rem;padding:0.45rem 0.75rem;flex:1;"
                                   placeholder="Senior/PWD ID number" maxlength="50">
                            <button class="pm-disc-btn active" onclick="applyFixedDiscount(_discountType)"
                                    style="white-space:nowrap;">Apply</button>
                        </div>
                    </div>

                    <div id="pmDiscPromoWrap" style="display:none;margin-bottom:0.5rem;">
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <input type="number" id="pmDiscPromoPct" class="pm-cash-input"
                                   style="font-size:0.88rem;padding:0.45rem 0.75rem;width:100px;"
                                   placeholder="%" min="1" max="100" step="1">
                            <button class="pm-disc-btn active" onclick="applyPromoDiscount()"
                                    style="white-space:nowrap;">Apply</button>
                        </div>
                    </div>

                    <div id="pmDiscApplied" style="display:none;padding:0.55rem 0.85rem;background:rgba(44,122,90,0.07);border:1px solid rgba(44,122,90,0.2);border-radius:8px;font-size:0.83rem;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span id="pmDiscAppliedLabel" style="color:var(--success);font-weight:600;"></span>
                            <span id="pmDiscAppliedAmt"   style="color:var(--success);font-weight:700;"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-top:0.2rem;color:var(--navy);font-weight:700;">
                            <span>New Total</span>
                            <span id="pmDiscNewTotal"></span>
                        </div>
                    </div>
                </div>

                <!-- VAT Breakdown -->
                <div id="pmVatBreakdown" style="display:none; margin: 0 0 1rem; padding: 0.75rem 1rem; background: rgba(15,76,92,0.05); border: 1px solid rgba(15,76,92,0.12); border-radius: 10px; font-size: 0.82rem;">
                    <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--primary);margin-bottom:0.5rem;">
                        <i class="fas fa-percent me-1"></i>VAT Breakdown
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:0.2rem;color:var(--warm-gray);">
                        <span>VATable Sales</span><span id="pmVatableSales">₱0.00</span>
                    </div>
                    <div id="pmVatExemptRow" style="display:none;justify-content:space-between;margin-bottom:0.2rem;color:var(--warm-gray);">
                        <span>VAT-Exempt Sales</span><span id="pmVatExemptSales">₱0.00</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-weight:600;color:var(--primary-dark);">
                        <span>VAT (<span id="pmVatRateLabel">12</span>%)</span>
                        <span id="pmVatAmount">₱0.00</span>
                    </div>
                </div>

                <div class="pm-input-section">
                    <div class="pm-input-label">
                        <i class="fas fa-money-bill-wave" style="color:var(--success);"></i>Cash Tendered
                    </div>
                    <div class="pm-cash-input-wrap">
                        <div class="pm-currency-prefix">₱</div>
                        <input type="number" id="pmCashInput" class="pm-cash-input"
                               placeholder="0.00" min="0" step="0.01" autocomplete="off">
                    </div>
                    <div class="pm-enter-hint"><kbd>Enter</kbd> to confirm payment</div>
                    <div class="pm-quick-amounts" id="pmQuickAmounts"></div>
                </div>

                <div class="pm-change-card change-none" id="pmChangeCard">
                    <div class="pm-change-icon"><i class="fas fa-coins"></i></div>
                    <div class="pm-change-info">
                        <div class="pm-change-sublabel">Change</div>
                        <div class="pm-change-value" id="pmChangeValue">—</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer pm-footer d-flex">
                <button type="button" class="pm-cancel-btn" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="pm-confirm-btn" id="pmConfirmBtn" disabled onclick="confirmPaymentAndSubmit()">
                    <i class="fas fa-check-circle me-1"></i>Confirm & Place Order
                </button>
            </div>

        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════ -->
<script src="<?= $baseUrl ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
/* ── PHP → JS data ── */
const stockData      = <?= json_encode($stock_data      ?? []) ?>;
const addonStockData = <?= json_encode($addon_stock_data ?? []) ?>;
const addonsMap      = <?= json_encode($addons_map       ?? []) ?>;
const variantsMap    = <?= json_encode($variants_map     ?? []) ?>;
const DB_DRAFT_CART  = <?= json_encode($draft_cart      ?? []) ?>;
const DB_DRAFT_META  = <?= json_encode($draft_meta      ?? null) ?>;
const BASE_URL       = <?= json_encode($baseUrl) ?>;
const AJAX_BASE      = <?= json_encode(rtrim(str_replace('/public', '', $baseUrl), '/')) ?>;
const CASHIER_NAME          = <?= json_encode($_SESSION['full_name'] ?? 'Cashier') ?>;
const POS_IS_VAT_REGISTERED = <?= json_encode(VatHelper::isVatRegistered($pdo)) ?>;
const POS_VAT_RATE          = <?= json_encode(VatHelper::getVatRate($pdo)) ?>;
const AVAILABLE_TABLES      = <?= json_encode($available_tables ?? []) ?>;

let cart             = [];
let currentItem      = null;
let currentVariantId   = null;
let currentVariantName = null;
let currentOrderType   = 'dine_in';
let hasStockIssues     = false;
let lastOrderData      = null;
let _submitting        = false;

// Note modal state
let _noteTargetIdx = null;

const isMobile = () => window.innerWidth < 992;

// ─── TOAST ───────────────────────────────────────────────────────
let _toastTimer = null;
function showAddToast(name) {
    const el = document.getElementById('addToast');
    const text = document.getElementById('addToastText');
    if (!el) return;
    if (_toastTimer) clearTimeout(_toastTimer);
    text.textContent = `${name} added`;
    el.classList.add('show');
    _toastTimer = setTimeout(() => el.classList.remove('show'), 1800);
}
function showSuccessToast(message) {
    const el = document.getElementById('addToast');
    const text = document.getElementById('addToastText');
    if (!el) return;
    if (_toastTimer) clearTimeout(_toastTimer);
    text.textContent = message;
    el.classList.add('show');
    _toastTimer = setTimeout(() => el.classList.remove('show'), 3000);
}
function flashCard(itemId) {
    const card = document.querySelector(`.menu-item-card[data-item-id="${itemId}"]`);
    if (!card) return;
    card.classList.remove('just-added');
    void card.offsetWidth;
    card.classList.add('just-added');
    setTimeout(() => card.classList.remove('just-added'), 500);
}

// ─── DRAFT PERSISTENCE ───────────────────────────────────────────
let _draftTimer = null;
function saveDraftToServer() {
    const mobile = isMobile();
    const orderType = document.getElementById('order_type').value;
    const customerName = mobile
        ? (document.getElementById('customer_name_m')?.value.trim() || document.getElementById('customer_name')?.value.trim())
        : document.getElementById('customer_name')?.value.trim();
    const tableIdEl = mobile
        ? (document.getElementById('table_id_m') || document.getElementById('table_id'))
        : document.getElementById('table_id');
    navigator.sendBeacon('index.php?page=manage_pos', new URLSearchParams({
        action: 'save_draft', cart_data: JSON.stringify(cart),
        customer_name: customerName || '', order_type: orderType,
        table_id: tableIdEl?.value || '',
    }));
}
function debouncedSave() { clearTimeout(_draftTimer); _draftTimer = setTimeout(saveDraftToServer, 500); }
function saveCart() { localStorage.setItem('pos_cart', JSON.stringify(cart)); debouncedSave(); }
function loadCart() { try { cart = JSON.parse(localStorage.getItem('pos_cart') || '[]'); } catch(e) { cart = []; } }

// ─── MODAL HELPERS ───────────────────────────────────────────────
function showModal(id) {
    const el = document.getElementById(id);
    const existing = bootstrap.Modal.getInstance(el);
    if (existing) existing.dispose();
    new bootstrap.Modal(el).show();
}
function hideModal(id) { bootstrap.Modal.getInstance(document.getElementById(id))?.hide(); }
document.addEventListener('hidden.bs.modal', () => {
    setTimeout(() => {
        if (document.querySelector('.modal.show')) return;
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }, 200);
});
function showStockErrorModal(title, missing) {
    document.getElementById('stockErrTitle').textContent = title;
    const list = document.getElementById('stockErrList');
    list.innerHTML = '';
    missing.forEach(ing => {
        const li = document.createElement('li');
        li.className = 'mb-2 pb-1 border-bottom';
        li.innerHTML = `<div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
            <span><i class="fas fa-leaf me-2 text-danger"></i>${ing.name}</span>
            <span class="badge bg-danger">Need: ${(+ing.required).toFixed(2)} | Have: ${(+ing.available).toFixed(2)}</span>
        </div>`;
        list.appendChild(li);
    });
    showModal('stockErrorModal');
}
function showErrorModal(title, msg) {
    document.getElementById('genericErrTitle').textContent = title;
    document.getElementById('genericErrMsg').textContent   = msg;
    showModal('genericErrorModal');
}

// ─── NOTE MODAL ──────────────────────────────────────────────────
function openNoteModal(idx) {
    _noteTargetIdx = idx;
    const row = cart[idx];
    if (!row) return;
    const label = row.variantName ? `${row.name} (${row.variantName})` : row.name;
    document.getElementById('noteModalItemName').textContent = label;
    const ta = document.getElementById('noteModalTextarea');
    ta.value = row.note || '';
    document.getElementById('noteModalCharCount').textContent = ta.value.length;
    // Show/hide Clear button based on whether there's an existing note
    document.getElementById('noteModalClearBtn').style.display = row.note ? '' : 'none';
    showModal('itemNoteModal');
    setTimeout(() => { ta.focus(); ta.setSelectionRange(ta.value.length, ta.value.length); }, 200);
}
function normalizeAddons(addons) {
    return addons
        .map(a => `${a.id}:${a.quantity}`)
        .sort()
        .join('|');
}

function saveNoteFromModal() {
    if (_noteTargetIdx === null) return;

    const ta = document.getElementById('noteModalTextarea');
    const newNote = ta.value.trim();
    const row = cart[_noteTargetIdx];

    if (!newNote) {
        // Search BEFORE mutating — same pattern as clearNoteFromModal
        const matchIdx = cart.findIndex((r, i) =>
            i !== _noteTargetIdx &&
            r.id === row.id &&
            r.variant_id === row.variant_id &&
            (r.note || '') === '' &&
            r.quantity > 0 &&
            normalizeAddons(r.addons) === normalizeAddons(row.addons)
        );

        if (matchIdx > -1) {
            cart[matchIdx].quantity += row.quantity;
            cart.splice(_noteTargetIdx, 1);
        } else {
            row.note = '';
        }

    } else {
        // Adding/editing note — check if another row already has this exact note
        const matchIdx = cart.findIndex((r, i) =>
            i !== _noteTargetIdx &&
            r.id === row.id &&
            r.variant_id === row.variant_id &&
            (r.note || '') === newNote &&
            normalizeAddons(r.addons) === normalizeAddons(row.addons)
        );

        if (matchIdx > -1) {
            // Merge into existing row with same note
            cart[matchIdx].quantity += row.quantity;
            cart.splice(_noteTargetIdx, 1);
        } else if (row.quantity > 1) {
            // Split: leave qty-1 behind, create new noted row
            row.quantity -= 1;
            const splitItem = {
                ...row,
                quantity: 1,
                note: newNote,
                addons: row.addons.map(a => ({ ...a })),
                hasStockIssue: false
            };
            cart.splice(_noteTargetIdx + 1, 0, splitItem);
        } else {
            // qty is 1, just update note in place
            row.note = newNote;
            row.hasStockIssue = false;
        }
    }

    saveCart();
    validateAndRender();
    hideModal('itemNoteModal');
    _noteTargetIdx = null;
}
function clearNoteFromModal() {
    if (_noteTargetIdx === null) return;

    const row = cart[_noteTargetIdx];

    // Search BEFORE mutating row.note — otherwise row already looks like a no-note item
    const matchIdx = cart.findIndex((r, i) =>
        i !== _noteTargetIdx &&
        r.id === row.id &&
        r.variant_id === row.variant_id &&
        (r.note || '') === '' &&
        r.quantity > 0 &&
        normalizeAddons(r.addons) === normalizeAddons(row.addons)
    );

    if (matchIdx > -1) {
        cart[matchIdx].quantity += row.quantity;
        cart.splice(_noteTargetIdx, 1);
    } else {
        row.note = '';
    }

    saveCart();
    validateAndRender();
    hideModal('itemNoteModal');
    _noteTargetIdx = null;
}
document.getElementById('noteModalTextarea')?.addEventListener('input', function() {
    document.getElementById('noteModalCharCount').textContent = this.value.length;
});

// ─── CLEAR CART ──────────────────────────────────────────────────
function confirmClearCart() {
    if (!cart.length) return;
    document.getElementById('clearCount').textContent = cart.reduce((s,r) => s + r.quantity, 0);
    const list = document.getElementById('clearConfirmList');
    list.innerHTML = cart.slice(0,6).map(r =>
        `<div class="clear-confirm-item"><i class="fas fa-circle"></i>${r.name} ×${r.quantity}</div>`
    ).join('') + (cart.length > 6 ? `<div class="clear-confirm-item text-muted">…and ${cart.length-6} more</div>` : '');
    showModal('clearCartModal');
}
function executeClearCart() {
    hideModal('clearCartModal');
    setTimeout(() => { cart = []; validateAndRender(); saveCart(); sessionStorage.removeItem('draft_banner_shown'); }, 200);
}

// ─── DRAWER ──────────────────────────────────────────────────────
function openCartDrawer() {
    document.getElementById('mobileCartDrawer').classList.add('open');
    document.getElementById('drawerBackdrop').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeCartDrawer() {
    document.getElementById('mobileCartDrawer').classList.remove('open');
    document.getElementById('drawerBackdrop').classList.remove('show');
    document.body.style.overflow = '';
}
function dismissDraftBanner() {
    document.getElementById('draftBanner')?.classList.add('d-none');
    document.getElementById('draftBannerM')?.classList.add('d-none');
}

// ─── STOCK VALIDATION ────────────────────────────────────────────
function buildDemandMap(rows) {
    const d = {};
    for (const row of rows) {
        const itemStock = stockData[row.id];
        let ingredients;
        if (row.variant_id && itemStock?.variants?.[row.variant_id]?.ingredients?.length) {
            ingredients = itemStock.variants[row.variant_id].ingredients;
        } else {
            ingredients = itemStock?.base?.ingredients || [];
        }
        ingredients.forEach(ing => {
            if (!ing.quantity_required || ing.quantity_required <= 0) return;
            if (!d[ing.ingredient_id]) {
                d[ing.ingredient_id] = { name: ing.ingredient_name, needed: 0, available: +ing.stock_quantity };
            }
            d[ing.ingredient_id].needed += ing.quantity_required * row.quantity;
        });
        (row.addons || []).forEach(a => {
            (addonStockData[a.id]?.ingredients || []).forEach(ing => {
                if (!ing.quantity_required || ing.quantity_required <= 0) return;
                if (!d[ing.ingredient_id]) {
                    d[ing.ingredient_id] = { name: ing.ingredient_name, needed: 0, available: +ing.stock_quantity };
                }
                d[ing.ingredient_id].needed += ing.quantity_required * a.quantity;
            });
        });
    }
    return d;
}
function validateCumulative(rows) {
    const conflicts = Object.values(buildDemandMap(rows))
        .filter(i => i.needed > i.available)
        .map(i => ({ name: i.name, required: i.needed, available: i.available }));
    return { success: !conflicts.length, missingIngredients: conflicts };
}
function checkCanAdd(id, qty, addons, variantId = null) {
    return validateCumulative([...cart, { id, quantity: qty, variant_id: variantId, addons: addons || [] }]);
}
function checkCanChangeQty(idx, newQty) {
    return validateCumulative(cart.map((r,i) => i === idx ? { ...r, quantity: newQty } : r));
}

// ─── CART MUTATIONS ──────────────────────────────────────────────
function addToCartWithQuantity(item, addons, quantity, note, variantId, variantName) {
    const trimmedNote = (note || '').trim();

    // Merge if id, variant, note, AND addons all match — regardless of whether there's a note
    const ei = cart.findIndex(r =>
        r.id === item.id &&
        r.variant_id === (variantId ?? null) &&
        (r.note || '') === trimmedNote &&
        r.addons.length === addons.length &&
        r.addons.map(a => `${a.id}:${a.quantity}`).sort().join('|') ===
          addons.map(a => `${a.id}:${a.quantity}`).sort().join('|')
    );

    if (ei > -1) {
        const mq = cart[ei].quantity + quantity;
        const mc = checkCanChangeQty(ei, mq);
        if (!mc.success) { showStockErrorModal(`Cannot increase "${item.name}" — exceeds stock`, mc.missingIngredients); return; }
        cart[ei].quantity = mq;
        validateAndRender(); saveCart(); flashCard(item.id); showAddToast(item.name); return;
    }

    cart.push({
        id: item.id, name: item.name, price: item.price, quantity,
        variant_id: variantId ?? null, variantName: variantName ?? null,
        addons: addons.map(a => ({ ...a })), note: trimmedNote, hasStockIssue: false
    });
    validateAndRender(); saveCart(); flashCard(item.id);
    showAddToast(variantName ? `${item.name} (${variantName})` : item.name);
}
function updateQuantity(idx, change) {
    const nq = cart[idx].quantity + change;
    if (nq < 1) { cart.splice(idx, 1); validateAndRender(); saveCart(); return; }
    if (change > 0) {
        const c = checkCanChangeQty(idx, nq);
        if (!c.success) { showStockErrorModal(`Cannot increase "${cart[idx].name}" — exceeds stock`, c.missingIngredients); return; }
    }
    cart[idx].quantity = nq; cart[idx].hasStockIssue = false;
    validateAndRender(); saveCart();
}
function setQuantity(idx, val) {
    const qty = parseInt(val);
    if (isNaN(qty) || qty < 1) { cart.splice(idx, 1); validateAndRender(); saveCart(); return; }
    const c = checkCanChangeQty(idx, qty);
    if (!c.success) { showStockErrorModal(`Cannot set qty for "${cart[idx].name}" — exceeds stock`, c.missingIngredients); renderCart(); return; }
    cart[idx].quantity = qty; cart[idx].hasStockIssue = false;
    validateAndRender(); saveCart();
}
function removeFromCart(idx) { cart.splice(idx, 1); validateAndRender(); saveCart(); }

// ─── RENDER ──────────────────────────────────────────────────────
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function buildCartItemHTML(row, idx) {
    const addonsTotal  = row.addons.reduce((s,a) => s + a.price * a.quantity, 0);
    const lineTotal    = (row.price + addonsTotal) * row.quantity;
    const hasIssue     = row.hasStockIssue === true;
    const note         = row.note || '';
    const variantLabel = row.variantName
        ? `<div class="cart-item-variant"><i class="fas fa-layer-group"></i>${escHtml(row.variantName)}</div>` : '';

    // Note display: show below item name if note exists
    const noteDisplay = note
        ? `<div style="display:flex;align-items:flex-start;gap:0.4rem;margin:0.3rem 0 0;padding:0.3rem 0.5rem;background:rgba(0,0,0,0.03);border-radius:6px;font-size:0.76rem;color:var(--warm-gray);">
               <i class="fas fa-pen-to-square" style="margin-top:2px;flex-shrink:0;color:var(--secondary);font-size:0.7rem;"></i>
               <span style="flex:1;word-break:break-word;line-height:1.4;">${escHtml(note)}</span>
           </div>` : '';

    // Note button: always visible per item
    const noteBtn = `<button
        onclick="openNoteModal(${idx})"
        style="display:inline-flex;align-items:center;gap:0.3rem;margin-top:0.4rem;padding:0.18rem 0.6rem;font-size:0.72rem;font-weight:600;color:var(--secondary);background:transparent;border:1px solid currentColor;border-radius:20px;cursor:pointer;line-height:1.5;font-family:inherit;">
        <i class="fas fa-${note ? 'pen' : 'plus'}" style="font-size:0.65rem;"></i>${note ? 'Edit Note' : 'Add Note'}
    </button>`;

    const minusIcon    = row.quantity <= 1 ? `<i class="fas fa-trash-alt"></i>` : `<i class="fas fa-minus"></i>`;
    const minusBtnCls  = row.quantity <= 1 ? 'qty-btn qty-remove' : 'qty-btn';

    return `<div class="cart-item ${hasIssue ? 'insufficient-stock' : ''}">
        <div class="cart-item-top">
            <div class="cart-item-info">
                <div class="cart-item-name" title="${escHtml(row.name)}">${escHtml(row.name)}</div>
                ${variantLabel}
                <div class="cart-item-price">₱${lineTotal.toFixed(2)}</div>
            </div>
            <button class="cart-item-remove" onclick="removeFromCart(${idx})" title="Remove"><i class="fas fa-times-circle"></i></button>
        </div>
        ${row.addons.length ? `<div class="addon-item">${row.addons.map(a => `+${a.name}${a.quantity > 1 ? ` ×${a.quantity}` : ''} ₱${(a.price*a.quantity).toFixed(2)}`).join(' · ')}</div>` : ''}
        ${noteDisplay}
        ${noteBtn}
        ${hasIssue ? `<div class="stock-warning-danger"><i class="fas fa-exclamation-circle"></i>Exceeds available stock</div>` : ''}
        <div class="cart-item-qty">
            <button class="${minusBtnCls}" onclick="updateQuantity(${idx},-1)">${minusIcon}</button>
            <input type="number" class="qty-input" value="${row.quantity}" min="1" onchange="setQuantity(${idx},this.value)">
            <button class="qty-btn" onclick="updateQuantity(${idx},1)" ${hasIssue ? 'disabled' : ''}><i class="fas fa-plus"></i></button>
        </div>
    </div>`;
}

function updateCheckoutButtonLabel() {
    const total  = cart.reduce((s,r) => s + (r.price + r.addons.reduce((a,b) => a + b.price*b.quantity, 0)) * r.quantity, 0);
    const textEl  = document.getElementById('collectPaymentBtnText');
    const textElM = document.getElementById('collectPaymentBtnTextM');
    const label   = cart.length > 0 ? `Charge ₱${total.toFixed(2)}` : 'Collect Payment';
    if (textEl)  textEl.textContent  = label;
    if (textElM) textElM.textContent = label;
}

function renderCart() {
    const totalItems = cart.reduce((s,r) => s + r.quantity, 0);
    const total      = cart.reduce((s,r) => s + (r.price + r.addons.reduce((a,b) => a + b.price*b.quantity, 0)) * r.quantity, 0);
    const emptyHTML  = `<div class="empty-cart"><div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div><p>Cart is empty</p><small>Tap a menu item to add it</small></div>`;

    [{ id: 'cartItems' }, { id: 'cartItemsMobile' }].forEach(({ id }) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.innerHTML = cart.length ? cart.map((r,i) => buildCartItemHTML(r,i)).join('') : emptyHTML;
    });

    const upd = (id, fn) => { const el = document.getElementById(id); if (el) fn(el); };
    const showCart = cart.length > 0;

    ['cartCountPill','cartCountPillM'].forEach(id => {
        const el = document.getElementById(id); if (!el) return;
        el.textContent = totalItems > 99 ? '99+' : totalItems;
        el.style.display = showCart ? '' : 'none';
    });
    ['clearCartBtn','clearCartBtnM'].forEach(id => { upd(id, el => el.classList.toggle('d-none', !showCart)); });
    upd('cartFooter',            el => el.classList.toggle('d-none', !showCart));
    upd('collectPaymentBtn',     el => { el.disabled = hasStockIssues || !showCart; });
    upd('submitDisabledMessage', el => el.classList.toggle('d-none', !hasStockIssues || !showCart));
    if (showCart) upd('total', el => el.textContent = `₱${total.toFixed(2)}`);
    upd('mobileTotalRow',         el => el.classList.toggle('d-none', !showCart));
    upd('collectPaymentBtnM',     el => { el.classList.toggle('d-none', !showCart); el.disabled = hasStockIssues; });
    upd('submitDisabledMessageM', el => el.classList.toggle('d-none', !hasStockIssues || !showCart));
    if (showCart) upd('totalM', el => el.textContent = `₱${total.toFixed(2)}`);

    const badge  = document.getElementById('mobileBadge');
    const mCount = document.getElementById('mobileCartCount');
    const mTotal = document.getElementById('mobileCartTotal');
    const mBtn   = document.getElementById('mobileSubmitBtn');
    if (showCart) {
        if (badge)  { badge.textContent = totalItems > 99 ? '99+' : totalItems; badge.style.display = 'flex'; }
        if (mCount) mCount.textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
        if (mTotal) mTotal.textContent = `₱${total.toFixed(2)}`;
    } else {
        if (badge)  badge.style.display = 'none';
        if (mCount) mCount.textContent = 'No items';
        if (mTotal) mTotal.textContent = '₱0.00';
    }
    if (mBtn) mBtn.disabled = hasStockIssues || !showCart;

    const cartCounts = {};
    cart.forEach(r => { cartCounts[r.id] = (cartCounts[r.id] || 0) + r.quantity; });
    document.querySelectorAll('.menu-item-card').forEach(card => {
        const id  = card.dataset.itemId;
        const cnt = cartCounts[id] || 0;
        const bdg = document.getElementById(`cartBadge_${id}`);
        if (bdg) { bdg.textContent = cnt > 0 ? cnt : ''; bdg.style.display = cnt > 0 ? 'flex' : 'none'; }
        card.classList.toggle('in-cart', cnt > 0);
    });

    const wrapper = document.getElementById('cartItemsWrapper');
    if (wrapper && cart.length > 0) wrapper.scrollTop = wrapper.scrollHeight;
    updateCheckoutButtonLabel();
}

function validateAndRender() {
    const check = validateCumulative(cart);
    if (!check.success) {
        const conflictNames = new Set(check.missingIngredients.map(i => i.name));
        cart.forEach(row => {
            const itemStock = stockData[row.id];
            let ingredients;
            if (row.variant_id && itemStock?.variants?.[row.variant_id]?.ingredients?.length) {
                ingredients = itemStock.variants[row.variant_id].ingredients;
            } else { ingredients = itemStock?.base?.ingredients || []; }
            const addonIngredients = (row.addons || []).flatMap(a =>
                (addonStockData[a.id]?.ingredients || []).map(i => i.ingredient_name)
            );
            const allIngredientNames = new Set([...ingredients.map(i => i.ingredient_name), ...addonIngredients]);
            row.hasStockIssue = [...allIngredientNames].some(n => conflictNames.has(n));
        });
        hasStockIssues = true;
    } else {
        cart.forEach(r => r.hasStockIssue = false);
        hasStockIssues = false;
    }
    renderCart();
}

// ─── ORDER TYPE ──────────────────────────────────────────────────
function setOrderType(type, context) {
    currentOrderType = type;
    document.getElementById('order_type').value = type;
    if (context === 'desktop' || !isMobile()) {
        document.getElementById('dineInBtn').classList.toggle('active', type === 'dine_in');
        document.getElementById('takeOutBtn').classList.toggle('active', type === 'takeout');
        const box = document.getElementById('tableNumberContainer');
        if (box) box.style.display = type === 'dine_in' ? '' : 'none';
        if (type === 'takeout') { const s = document.getElementById('table_id'); if (s) s.value = ''; }
    }
    if (context === 'mobile' || isMobile()) {
        document.getElementById('dineInBtnM').classList.toggle('active', type === 'dine_in');
        document.getElementById('takeOutBtnM').classList.toggle('active', type === 'takeout');
        const boxM = document.getElementById('tableNumberContainerM');
        if (boxM) boxM.style.display = type === 'dine_in' ? '' : 'none';
        if (type === 'takeout') { const s = document.getElementById('table_id_m'); if (s) s.value = ''; }
    }
    debouncedSave();
}

document.addEventListener('keydown', e => {
    if (isMobile()) return;
    if (e.target.matches('input, textarea, select')) return;
    if (e.key === 'd' || e.key === 'D') setOrderType('dine_in', 'desktop');
    if (e.key === 't' || e.key === 'T') setOrderType('takeout', 'desktop');
});

// ─── SEARCH & CATEGORIES ─────────────────────────────────────────
function filterMenu() {
    const query    = document.getElementById('menuSearch').value.trim().toLowerCase();
    const clearBtn = document.getElementById('searchClear');
    if (clearBtn) clearBtn.style.display = query ? 'block' : 'none';
    const activeCat = document.querySelector('.category-tab.active')?.dataset.category || 'all';
    let visible = 0;
    document.querySelectorAll('.menu-item-card').forEach(card => {
        const matchCat  = activeCat === 'all' || card.dataset.category === activeCat;
        const matchText = !query || card.dataset.searchText.includes(query);
        const show = matchCat && matchText;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const noResults = document.getElementById('noResults');
    if (noResults) noResults.classList.toggle('d-none', visible > 0);
}
function clearSearch() {
    const input = document.getElementById('menuSearch');
    if (input) { input.value = ''; input.focus(); }
    filterMenu();
}
document.getElementById('menuSearch')?.addEventListener('input', filterMenu);
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        filterMenu();
    });
});

// ─── CUSTOMIZE MODAL (variants embedded + addons) ─────────────────
function openCustomizeModal(item, variants) {
    currentItem        = item;
    currentVariantId   = null;
    currentVariantName = null;

    const activeAddons = (addonsMap[item.id] || []).filter(a => a.is_active);
    const hasVariants  = variants && variants.length > 0;

    document.getElementById('addonsModalLabel').innerHTML =
        `<i class="fas fa-utensils" style="color:var(--secondary);"></i>${escHtml(item.name)}`;

    let html = '';

    // ── Embedded size/variant picker ──
    if (hasVariants) {
        html += `<div style="margin-bottom:1rem;">
            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--warm-gray);margin-bottom:0.6rem;">
                <i class="fas fa-layer-group me-1"></i>Choose Size <span class="text-danger">*</span>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.4rem;">`;

        variants.forEach((v, idx) => {
            const vStock      = stockData[item.id]?.variants?.[v.variant_id];
            const hasStock    = vStock?.has_stock  ?? false;
            const noRecipe    = vStock?.no_recipe  ?? false;
            const maxQty      = vStock?.max_quantity ?? 0;
            const isAvailable = hasStock && !noRecipe;

            let badgeHtml = '';
            if (noRecipe)         badgeHtml = '<span class="badge bg-secondary ms-2" style="font-size:0.67rem;">Unavailable</span>';
            else if (!hasStock)   badgeHtml = '<span class="badge bg-danger ms-2" style="font-size:0.67rem;">Out of Stock</span>';
            else if (maxQty < 10) badgeHtml = `<span class="badge bg-warning text-dark ms-2" style="font-size:0.67rem;">Only ${maxQty} left</span>`;

            html += `<label style="display:flex;align-items:center;justify-content:space-between;padding:0.6rem 0.85rem;border:1.5px solid var(--border);border-radius:10px;cursor:${isAvailable ? 'pointer' : 'not-allowed'};gap:0.5rem;opacity:${isAvailable ? '1' : '0.5'};transition:border-color 0.15s,background 0.15s;" id="vrlabel_${idx}" ${isAvailable ? `onclick="selectVariantInModal(${idx})"` : ''}>
                <div style="display:flex;align-items:center;gap:0.5rem;flex:1;">
                    <input type="radio" name="variantPick" id="vr_${idx}"
                        value="${v.variant_id}"
                        data-variant-name="${escHtml(v.variant_name)}"
                        data-variant-price="${v.price}"
                        data-variant-idx="${idx}"
                        ${!isAvailable ? 'disabled' : ''}
                        onchange="onVariantRadioChange(this)"
                        style="accent-color:var(--primary);width:15px;height:15px;flex-shrink:0;cursor:pointer;">
                    <span style="font-weight:600;font-size:0.9rem;color:var(--navy);">${escHtml(v.variant_name)}</span>
                    ${badgeHtml}
                </div>
                <span style="font-weight:700;color:var(--primary);font-size:0.9rem;white-space:nowrap;">₱${parseFloat(v.price).toFixed(2)}</span>
            </label>`;
        });

        html += `</div></div><hr style="margin:0.5rem 0 0.85rem;border-color:var(--border);">`;
    }

    // ── Add-ons ──
    if (activeAddons.length > 0) {
        html += `<div style="margin-bottom:1rem;">
            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--warm-gray);margin-bottom:0.55rem;">
                <i class="fas fa-plus-circle me-1"></i>Add-ons <span style="font-weight:400;opacity:0.7;">(optional)</span>
            </div>`;
        activeAddons.forEach(addon => {
            const as   = addonStockData[addon.addon_id] || {};
            const hasStk = (as.max_quantity ?? 0) > 0;
            const maxA   = as.max_quantity ?? 0;
            html += `<div class="addon-checkbox ${!hasStk ? 'opacity-50' : ''}">
                <input type="checkbox" id="addon_${addon.addon_id}" value="${addon.addon_id}"
                       data-name="${escHtml(addon.addon_name)}" data-price="${addon.price}"
                       onchange="toggleAddonQty(${addon.addon_id})" ${!hasStk ? 'disabled' : ''}>
                <label for="addon_${addon.addon_id}">
                    <span class="addon-name ${!hasStk ? 'text-muted' : ''}">${escHtml(addon.addon_name)}</span>
                    <span class="addon-price">+₱${parseFloat(addon.price).toFixed(2)}</span>
                    <span class="addon-status">${!hasStk ? '<span class="badge bg-danger">Out of Stock</span>' : ''}${hasStk && maxA < 10 ? `<span class="badge bg-warning text-dark">Only ${maxA} left</span>` : ''}</span>
                </label>
                <div class="addon-qty-control" id="qty_${addon.addon_id}" style="display:none">
                    <input type="number" min="1" max="${maxA}" value="1" id="qty_input_${addon.addon_id}" class="form-control form-control-sm" ${!hasStk ? 'disabled' : ''}>
                </div>
            </div>`;
        });
        html += `</div>`;
    } else if (!hasVariants) {
        // Simple item — no variants, no addons
        html += `<div class="text-center py-2">
            <div class="modal-icon primary mb-2"><i class="fas fa-utensils"></i></div>
            <p class="fw-bold mb-1">${escHtml(item.name)}</p>
            <p class="text-success fw-bold">₱${item.price.toFixed(2)}</p>
        </div>`;
    }

    // ── Quantity section ──
    const initMaxQty = hasVariants ? 0 : (stockData[item.id]?.base?.max_quantity ?? 0);
    html += `<div class="p-3 rounded-3 mb-0" style="background:var(--light-bg);" id="qtySection">
        <label class="form-label">Quantity</label>
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary btn-sm px-3" type="button" id="qtyDecBtn" onclick="decModalQty()" ${hasVariants ? 'disabled' : ''}>−</button>
            <input type="number" id="modalItemQty" class="form-control text-center fw-bold"
                   value="1" min="1" max="${initMaxQty}" style="width:75px" ${hasVariants ? 'disabled' : ''}>
            <button class="btn btn-outline-secondary btn-sm px-3" type="button" id="qtyIncBtn" onclick="incModalQty()" ${hasVariants ? 'disabled' : ''}>+</button>
        </div>
        <div class="text-muted mt-1" style="font-size:0.72rem;" id="maxQtyHint">
            ${hasVariants ? 'Select a size first' : `Max available: ${initMaxQty}`}
        </div>
    </div>
    <div class="note-field-wrapper">
        <div class="note-field-label"><i class="fas fa-pen-to-square"></i>Order Note <span style="font-weight:400;opacity:0.65">(optional)</span></div>
        <textarea id="modalItemNote" rows="2" maxlength="300" placeholder="e.g. No sugar, less ice, extra hot…"></textarea>
        <div class="note-field-hint"><i class="fas fa-circle-info me-1"></i>Max 300 characters</div>
    </div>`;

document.getElementById('addonsContent').innerHTML = html;
    const noteField = document.getElementById('modalItemNote');
    if (noteField) noteField.value = '';
    document.getElementById('addToCartBtn').disabled = hasVariants;

    showModal('addonsModal');

    document.getElementById('addonsModal').addEventListener('shown.bs.modal', function handler() {
        this.removeEventListener('shown.bs.modal', handler);
        const modalBody = document.getElementById('addonsContent');
        const ta = document.getElementById('modalItemNote');
        if (!ta || !modalBody) return;
        // Scroll the modal-body (the scrollable container) to bottom so note field is visible
        const scrollable = modalBody.closest('.modal-body');
        if (scrollable) scrollable.scrollTop = scrollable.scrollHeight;
        // Defeat Bootstrap's focus trap with a slightly delayed focus
        requestAnimationFrame(() => requestAnimationFrame(() => ta.focus()));
    }, { once: true });
}

function selectVariantInModal(idx) {
    const radio = document.getElementById(`vr_${idx}`);
    if (!radio || radio.disabled) return;
    radio.checked = true;
    onVariantRadioChange(radio);
}

function onVariantRadioChange(radio) {
    const variantId   = parseInt(radio.value);
    const variantName = radio.dataset.variantName;
    const price       = parseFloat(radio.dataset.variantPrice);
    const variantIdx  = parseInt(radio.dataset.variantIdx);

    currentVariantId   = variantId;
    currentVariantName = variantName;
    currentItem        = { ...currentItem, price };

    // Highlight selected row
    document.querySelectorAll('label[id^="vrlabel_"]').forEach((lbl, i) => {
        const sel = i === variantIdx;
        lbl.style.borderColor = sel ? 'var(--primary)'                  : 'var(--border)';
        lbl.style.background  = sel ? 'rgba(15,76,92,0.05)'             : '';
        lbl.style.boxShadow   = sel ? '0 0 0 2px rgba(15,76,92,0.12)'   : '';
    });

    // Update qty controls
    const vStock  = stockData[currentItem.id]?.variants?.[variantId];
    const maxQty  = vStock?.max_quantity ?? 0;
    const qtyIn   = document.getElementById('modalItemQty');
    const decBtn  = document.getElementById('qtyDecBtn');
    const incBtn  = document.getElementById('qtyIncBtn');
    const hint    = document.getElementById('maxQtyHint');
    if (qtyIn)  { qtyIn.max = maxQty; qtyIn.value = 1; qtyIn.disabled = false; }
    if (decBtn)   decBtn.disabled = false;
    if (incBtn)   incBtn.disabled = false;
    if (hint)     hint.textContent = `Max available: ${maxQty}`;

    document.getElementById('addToCartBtn').disabled = false;
}

// ─── MENU ITEM CLICK ─────────────────────────────────────────────
document.querySelectorAll('.menu-item-card').forEach(card => {
    card.addEventListener('click', function() {
        if (this.classList.contains('out-of-stock')) return;
        const itemId    = this.dataset.itemId;
        const itemName  = this.dataset.itemName;
        const basePrice = parseFloat(this.dataset.itemPrice);
        const catId     = parseInt(this.dataset.categoryId);
        const variants  = variantsMap[itemId] || [];
        const item      = { id: itemId, name: itemName, price: basePrice, categoryId: catId };
        const activeAddons = (addonsMap[itemId] || []).filter(a => a.is_active);

        // No variants and no addons: add directly (1 qty, no note)
        if (variants.length === 0 && activeAddons.length === 0) {
            const c = checkCanAdd(itemId, 1, [], null);
            if (!c.success) { showStockErrorModal(`Cannot add "${itemName}" — exceeds stock`, c.missingIngredients); return; }
            addToCartWithQuantity(item, [], 1, '', null, null);
            return;
        }

        // Open combined customize modal
        openCustomizeModal(item, variants);
    });
});

function decModalQty() {
    const i = document.getElementById('modalItemQty');
    if (i && !i.disabled && +i.value > 1) i.value = +i.value - 1;
}
function incModalQty() {
    const i = document.getElementById('modalItemQty');
    if (i && !i.disabled && +i.value < +i.max) i.value = +i.value + 1;
}
function toggleAddonQty(id) {
    const cb  = document.getElementById(`addon_${id}`);
    const ctl = document.getElementById(`qty_${id}`);
    if (ctl) ctl.style.display = cb.checked ? 'flex' : 'none';
}

document.getElementById('addToCartBtn').addEventListener('click', () => {
    const qty  = Math.max(1, parseInt(document.getElementById('modalItemQty')?.value) || 1);
    const note = (document.getElementById('modalItemNote')?.value || '').trim();
    const adds = Array.from(document.querySelectorAll('#addonsContent input[type="checkbox"]:checked'))
        .map(cb => ({
            id: parseInt(cb.value), name: cb.dataset.name,
            price: parseFloat(cb.dataset.price),
            quantity: parseInt(document.getElementById(`qty_input_${cb.value}`)?.value) || 1
        }));

    const variants = variantsMap[currentItem?.id] || [];
    if (variants.length > 0 && currentVariantId === null) {
        showErrorModal('Size Required', 'Please choose a size before adding to cart.');
        return;
    }

    const c = checkCanAdd(currentItem.id, qty, adds, currentVariantId);
    if (!c.success) { showStockErrorModal(`Cannot add "${currentItem.name}" — exceeds stock`, c.missingIngredients); return; }
    addToCartWithQuantity(currentItem, adds, qty, note, currentVariantId, currentVariantName);
    hideModal('addonsModal');
});

// ─── COLLECT ORDER DATA ──────────────────────────────────────────
function collectOrderData() {
    const mobile = isMobile();
    const orderType = document.getElementById('order_type').value;
    const customerName = mobile
        ? (document.getElementById('customer_name_m')?.value.trim() || document.getElementById('customer_name')?.value.trim())
        : document.getElementById('customer_name')?.value.trim();
    const tableIdEl = mobile
        ? (document.getElementById('table_id_m') || document.getElementById('table_id'))
        : document.getElementById('table_id');
    return { orderType, customerName, tableId: tableIdEl?.value || '' };
}

// ─── DISCOUNT ────────────────────────────────────────────────────────
let _discountType    = 'none';
let _discountPct     = 0;
let _discountAmount  = 0;
let _discountedTotal = 0;
let _discountRef     = '';

function setDiscountType(type) {
    _discountType = type; _discountPct = 0; _discountAmount = 0;
    _discountedTotal = _pmTotal; _discountRef = '';
    document.querySelectorAll('.pm-disc-btn[data-type]').forEach(b => b.classList.toggle('active', b.dataset.type === type));
    document.getElementById('pmDiscRefWrap').style.display   = (type === 'senior' || type === 'pwd') ? '' : 'none';
    document.getElementById('pmDiscPromoWrap').style.display = type === 'promo' ? '' : 'none';
    document.getElementById('pmDiscApplied').style.display   = 'none';
    if (document.getElementById('pmDiscRef'))      document.getElementById('pmDiscRef').value      = '';
    if (document.getElementById('pmDiscPromoPct')) document.getElementById('pmDiscPromoPct').value = '';
    document.getElementById('pmTotalAmount').textContent = _pmTotal.toFixed(2);
    buildQuickAmounts(_pmTotal);
    const cashInput = document.getElementById('pmCashInput');
    const paid = parseFloat(cashInput.value) || 0;
    updateChangeDisplay(paid, cashInput.value !== '');
    document.getElementById('pmConfirmBtn').disabled = paid < _pmTotal;
    renderVatBreakdown();
    if (type === 'none') resetDiscountedTotal();
}

async function applyFixedDiscount(type) {
    const ref = document.getElementById('pmDiscRef')?.value.trim() || '';
    const payload = new URLSearchParams({ discount_type: type, reference_no: ref, subtotal: _pmTotal.toFixed(2) });
    try {
        const res  = await fetch('index.php?page=ajax_apply_discount', { method: 'POST', body: payload });
        const data = await res.json();
        if (!data.success) { showErrorModal('Discount Error', data.message); return; }
        applyDiscountResult(data);
    } catch(e) { showErrorModal('Discount Error', 'Could not compute discount. Try again.'); }
}

async function applyPromoDiscount() {
    const pct = parseFloat(document.getElementById('pmDiscPromoPct')?.value) || 0;
    if (pct <= 0 || pct > 100) { showErrorModal('Invalid %', 'Enter a promo percentage between 1 and 100.'); return; }
    const payload = new URLSearchParams({ discount_type: 'promo', reference_no: '', subtotal: _pmTotal.toFixed(2), promo_pct: pct });
    try {
        const res  = await fetch('index.php?page=ajax_apply_discount', { method: 'POST', body: payload });
        const data = await res.json();
        if (!data.success) { showErrorModal('Discount Error', data.message); return; }
        applyDiscountResult(data);
    } catch(e) { showErrorModal('Discount Error', 'Could not compute discount. Try again.'); }
}

function applyDiscountResult(data) {
    _discountPct     = data.discount_pct;
    _discountAmount  = data.discount_amount;
    _discountedTotal = data.total_after;
    _discountRef     = data.reference_no || '';
    const label = _discountType === 'senior' ? `Senior Citizen (${_discountPct}%)`
                : _discountType === 'pwd'    ? `PWD (${_discountPct}%)`
                : `Promo (${_discountPct}%)`;
    document.getElementById('pmDiscAppliedLabel').textContent = label;
    document.getElementById('pmDiscAppliedAmt').textContent   = `-₱${_discountAmount.toFixed(2)}`;
    document.getElementById('pmDiscNewTotal').textContent     = `₱${_discountedTotal.toFixed(2)}`;
    document.getElementById('pmDiscApplied').style.display    = '';
    document.getElementById('pmTotalAmount').textContent      = _discountedTotal.toFixed(2);
    const cashInput = document.getElementById('pmCashInput');
    const paid = parseFloat(cashInput.value) || 0;
    updateChangeDisplay(paid, cashInput.value !== '');
    document.getElementById('pmConfirmBtn').disabled = paid < _discountedTotal;
    buildQuickAmounts(_discountedTotal);
    renderVatBreakdown(data);
}

function resetDiscountedTotal() {
    _discountPct = 0; _discountAmount = 0; _discountedTotal = _pmTotal; _discountRef = '';
    document.getElementById('pmTotalAmount').textContent = _pmTotal.toFixed(2);
    buildQuickAmounts(_pmTotal);
    const cashInput = document.getElementById('pmCashInput');
    const paid = parseFloat(cashInput.value) || 0;
    updateChangeDisplay(paid, cashInput.value !== '');
    document.getElementById('pmConfirmBtn').disabled = paid < _pmTotal;
    renderVatBreakdown();
}

function renderVatBreakdown(vatData = null) {
    const wrap = document.getElementById('pmVatBreakdown');
    if (!wrap) return;
    if (!POS_IS_VAT_REGISTERED) { wrap.style.display = 'none'; return; }
    const rate = parseFloat(POS_VAT_RATE) || 0;
    let vatableSales = 0, vatAmount = 0, vatExemptSales = 0;
    if (vatData) {
        vatableSales   = parseFloat(vatData.vatable_sales ?? 0) || 0;
        vatAmount      = parseFloat(vatData.vat_amount    ?? 0) || 0;
        vatExemptSales = parseFloat(vatData.vat_exempt    ?? 0) || 0;
    } else {
        const divisor = 1 + (rate / 100);
        vatableSales  = Math.round((_pmTotal / divisor) * 100) / 100;
        vatAmount     = Math.round((_pmTotal - vatableSales) * 100) / 100;
    }
    document.getElementById('pmVatRateLabel').textContent   = rate.toString();
    document.getElementById('pmVatableSales').textContent   = `₱${vatableSales.toFixed(2)}`;
    document.getElementById('pmVatAmount').textContent      = `₱${vatAmount.toFixed(2)}`;
    document.getElementById('pmVatExemptSales').textContent = `₱${vatExemptSales.toFixed(2)}`;
    document.getElementById('pmVatExemptRow').style.display = vatExemptSales > 0 ? 'flex' : 'none';
    wrap.style.display = 'block';
}

// ─── PAYMENT MODAL ───────────────────────────────────────────────
let _pmTotal = 0;
function openPaymentModal() {
    if (!cart.length) { showErrorModal('Empty Cart', 'Please add items before collecting payment.'); return; }
    const fc = validateCumulative(cart);
    if (!fc.success) { showStockErrorModal('Cannot proceed — stock exceeded', fc.missingIngredients); return; }
    const { orderType, customerName, tableId } = collectOrderData();
    if (!orderType)                               { showErrorModal('Order Type Required',    'Please select an order type.');               return; }
    if (!customerName || !customerName.trim())    { showErrorModal('Customer Name Required', 'Please enter a customer name.');              return; }
    if (orderType === 'dine_in' && !tableId)      { showErrorModal('Table Required',         'Please select a table for dine-in orders.'); return; }
    if (isMobile()) closeCartDrawer();

    _pmTotal = cart.reduce((s,r) => s + (r.price + r.addons.reduce((a,b) => a + b.price*b.quantity, 0)) * r.quantity, 0);
    const totalItems = cart.reduce((s,r) => s + r.quantity, 0);
    document.getElementById('pmOrderSummaryLine').textContent =
        `${totalItems} item${totalItems !== 1 ? 's' : ''} · ${orderType === 'dine_in' ? 'Dine In' : 'Take Out'}`;

    const summaryEl = document.getElementById('pmSummaryItems');
    let summaryHTML = '';
    cart.forEach(row => {
        const lineTotal = (row.price + row.addons.reduce((a,b) => a + b.price*b.quantity, 0)) * row.quantity;
        const label = row.variantName ? `${row.name} (${row.variantName})` : row.name;
        summaryHTML += `<div class="pm-summary-item-row"><span class="pm-summary-item-name">${escHtml(label)} ×${row.quantity}</span><span class="pm-summary-item-price">₱${lineTotal.toFixed(2)}</span></div>`;
        row.addons.forEach(a => {
            summaryHTML += `<div class="pm-summary-item-row" style="padding-left:12px;opacity:0.7;"><span class="pm-summary-item-name">+ ${escHtml(a.name)}</span><span class="pm-summary-item-price">₱${(a.price*a.quantity).toFixed(2)}</span></div>`;
        });
        if (row.note) {
            summaryHTML += `<div class="pm-summary-item-row" style="padding-left:12px;opacity:0.6;font-size:0.77rem;font-style:italic;"><span><i class="fas fa-pen-to-square me-1"></i>${escHtml(row.note)}</span></div>`;
        }
    });
    summaryHTML += `<hr class="pm-summary-divider"><div class="pm-summary-item-row" style="font-weight:700;"><span>Total</span><span>₱${_pmTotal.toFixed(2)}</span></div>`;
    summaryEl.innerHTML = summaryHTML;
    document.getElementById('pmTotalAmount').textContent = _pmTotal.toFixed(2);

    // Reset discount
    _discountType = 'none'; _discountPct = 0; _discountAmount = 0; _discountedTotal = _pmTotal; _discountRef = '';
    document.querySelectorAll('.pm-disc-btn[data-type]').forEach(b => b.classList.toggle('active', b.dataset.type === 'none'));
    document.getElementById('pmDiscRefWrap').style.display   = 'none';
    document.getElementById('pmDiscPromoWrap').style.display = 'none';
    document.getElementById('pmDiscApplied').style.display   = 'none';
    if (document.getElementById('pmDiscRef'))      document.getElementById('pmDiscRef').value      = '';
    if (document.getElementById('pmDiscPromoPct')) document.getElementById('pmDiscPromoPct').value = '';

    renderVatBreakdown();
    const cashInput = document.getElementById('pmCashInput');
    cashInput.value = ''; cashInput.className = 'pm-cash-input';
    updateChangeDisplay(0, false);
    document.getElementById('pmConfirmBtn').disabled = true;
    buildQuickAmounts(_pmTotal);
    showModal('paymentModal');
    setTimeout(() => { cashInput.focus(); cashInput.select(); }, 200);
}

function buildQuickAmounts(total) {
    const roundUp = (n,to) => Math.ceil(n/to)*to;
    const suggestions = new Set([total]);
    [20,50,100,200,500,1000].forEach(d => { const r = roundUp(total,d); if (r >= total && r <= total*3) suggestions.add(r); });
    const sorted = [...suggestions].sort((a,b) => a-b).slice(0,5);
    const container = document.getElementById('pmQuickAmounts');
    container.innerHTML = sorted.map(amt => {
        const isExact = amt === total;
        return `<button class="pm-quick-btn ${isExact ? 'exact' : ''}" onclick="setQuickAmount(${amt})">${isExact ? '<i class="fas fa-equals me-1"></i>Exact' : ''}₱${amt.toFixed(amt%1===0 ? 0 : 2)}</button>`;
    }).join('');
}
function setQuickAmount(amt) {
    const cashInput = document.getElementById('pmCashInput');
    cashInput.value = amt.toFixed(2);
    cashInput.dispatchEvent(new Event('input'));
}
document.getElementById('pmCashInput')?.addEventListener('input', function() {
    const val = parseFloat(this.value) || 0;
    const effectiveTotal = (_discountAmount > 0) ? _discountedTotal : _pmTotal;
    const valid = val >= effectiveTotal && effectiveTotal > 0;
    this.className = 'pm-cash-input' + (this.value === '' ? '' : (valid ? ' valid' : ' invalid'));
    updateChangeDisplay(val, this.value !== '');
    document.getElementById('pmConfirmBtn').disabled = !valid;
});
document.getElementById('pmCashInput')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); const btn = document.getElementById('pmConfirmBtn'); if (!btn.disabled) confirmPaymentAndSubmit(); }
});
function updateChangeDisplay(amountPaid, hasInput) {
    const card    = document.getElementById('pmChangeCard');
    const valueEl = document.getElementById('pmChangeValue');
    const iconEl  = card.querySelector('.pm-change-icon i');
    const effectiveTotal = (_discountAmount > 0) ? _discountedTotal : _pmTotal;
    if (!hasInput || effectiveTotal <= 0) {
        card.className = 'pm-change-card change-none'; valueEl.textContent = '—'; iconEl.className = 'fas fa-coins'; return;
    }
    const change = amountPaid - effectiveTotal;
    if (change < 0) {
        card.className = 'pm-change-card change-err'; valueEl.textContent = `₱${Math.abs(change).toFixed(2)} short`; iconEl.className = 'fas fa-exclamation-triangle';
    } else if (change === 0) {
        card.className = 'pm-change-card change-exact'; valueEl.textContent = 'No change — exact amount'; iconEl.className = 'fas fa-check-circle';
    } else {
        card.className = 'pm-change-card change-ok'; valueEl.textContent = `₱${change.toFixed(2)}`; iconEl.className = 'fas fa-hand-holding-dollar';
    }
}

// ─── CONFIRM PAYMENT & SUBMIT ────────────────────────────────────
async function confirmPaymentAndSubmit() {
    if (_submitting) return;
    const amountPaid     = parseFloat(document.getElementById('pmCashInput').value) || 0;
    const effectiveTotal = (_discountAmount > 0) ? _discountedTotal : _pmTotal;
    const changeAmt      = Math.max(0, amountPaid - effectiveTotal);
    if (amountPaid < effectiveTotal) { showErrorModal('Insufficient Amount', 'Cash tendered must be ≥ the order total.'); return; }
    const { orderType, customerName, tableId } = collectOrderData();
    const btn = document.getElementById('pmConfirmBtn');
    _submitting = true;
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing…';
    const payload = new URLSearchParams({
        action: 'pay_and_submit', customer_name: customerName || '',
        table_id: orderType === 'dine_in' ? tableId : '',
        order_type: orderType, cart_data: JSON.stringify(cart),
        amount_paid: amountPaid.toFixed(2), change_amount: changeAmt.toFixed(2),
        discount_type: _discountType, discount_ref: _discountRef,
        discount_pct: _discountPct.toFixed(2), discount_amount: _discountAmount.toFixed(2),
    });
    try {
        const resp = await fetch('index.php?page=manage_pos', {
            method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: payload,
        });
        const data = await resp.json();
        if (!data.success) throw new Error(data.error || 'Unknown error from server.');
        const vatData = data.vat || null;
        lastOrderData = {
            orderId: data.order_id,
            orderNumber: data.order_number, orNumber: data.or_number,
            orderType, customerName, tableName: data.table_name || '',
            items: [...cart], total: effectiveTotal, amountPaid, changeAmt,
            cashierName: CASHIER_NAME, vat: vatData,
            discountType: _discountType, discountAmount: _discountAmount,
        };
        renderVatBreakdown(vatData);
        cart = [];
        localStorage.removeItem('pos_cart');
        sessionStorage.removeItem('draft_banner_shown');
        validateAndRender();
        ['customer_name','customer_name_m'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
        ['table_id','table_id_m'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
        setOrderType('dine_in','desktop');
        setOrderType('dine_in','mobile');
        hideModal('paymentModal');
        _submitting = false;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Confirm & Place Order';
        showSuccessToast(`OR ${data.or_number} — Order placed!`);
        if (orderType === 'dine_in') await refreshTableAvailability();
        setTimeout(() => printReceiptESC(lastOrderData), 300);
        if (!isMobile()) setTimeout(() => document.getElementById('menuSearch')?.focus(), 400);
    } catch (err) {
        _submitting = false;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Confirm & Place Order';
        showErrorModal('Order Failed', err.message);
    }
}

// ─── REFRESH TABLE AVAILABILITY ──────────────────────────────────
async function refreshTableAvailability() {
    try {
        const response = await fetch(`${AJAX_BASE}/app/ajax/get_tables.php`);
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to fetch tables');
        const availableTables = data.tables.filter(table => table.status === 'available');
        ['table_id', 'table_id_m'].forEach(selectId => {
            const select    = document.getElementById(selectId);
            const container = select?.closest('div[id$="Container"]');
            if (!select || !container) return;
            select.innerHTML = '<option value="">— Choose a table —</option>';
            if (availableTables.length > 0) {
                availableTables.forEach(table => {
                    const option = document.createElement('option');
                    option.value = table.table_id;
                    option.textContent = `${table.table_name} (${table.capacity} seats)`;
                    select.appendChild(option);
                });
                const hint = container.querySelector('.table-count-hint');
                if (hint) hint.innerHTML = `<i class="fas fa-circle-check"></i>${availableTables.length} table${availableTables.length !== 1 ? 's' : ''} available`;
                select.style.display = '';
                const unavailableNotice = container.querySelector('.table-unavailable-notice');
                if (unavailableNotice) unavailableNotice.style.display = 'none';
                if (availableTables.length === 1) select.value = String(availableTables[0].table_id);
            } else {
                select.style.display = 'none';
                let unavailableNotice = container.querySelector('.table-unavailable-notice');
                if (!unavailableNotice) {
                    unavailableNotice = document.createElement('div');
                    unavailableNotice.className = 'table-unavailable-notice';
                    unavailableNotice.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>No tables available — free a table first.';
                    container.appendChild(unavailableNotice);
                }
                unavailableNotice.style.display = '';
            }
        });
    } catch (error) { console.error('Error refreshing table availability:', error); }
}

async function printReceiptESC(d) {
    // Only the order id is sent — the server prints from the recorded sale so
    // the paper always matches what was actually stored and filed.
    const orderId = d && d.orderId ? d.orderId : null;
    if (!orderId) { console.warn('No order id — cannot print receipt.'); return; }
    try {
        const response = await fetch('index.php?page=print_receipt', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId })
        });
        const result = await response.json();
        if (result.status === 'no_printer')        console.log('No printer available');
        else if (result.status === 'printed')      console.log('Receipt printed');
        else if (result.status === 'print_failed') showErrorModal('Printing Failed', result.message);
        else if (result.message)                   console.warn('Print:', result.message);
    } catch (error) { console.log('Print error:', error.message); }
}

// ─── SIDEBAR ─────────────────────────────────────────────────────
function setSidebarOpen(open) {
    const s = document.getElementById('sidebar');
    const o = document.getElementById('sidebarOverlay');
    const b = document.getElementById('mobileBottomBar');
    if (open) { s.classList.add('show'); o.classList.add('show'); if (b) b.style.visibility = 'hidden'; closeCartDrawer(); }
    else       { s.classList.remove('show'); o.classList.remove('show'); if (b) b.style.visibility = ''; }
}
document.getElementById('menuToggle')?.addEventListener('click', () => setSidebarOpen(!document.getElementById('sidebar').classList.contains('show')));
document.getElementById('sidebarOverlay')?.addEventListener('click', () => setSidebarOpen(false));

document.getElementById('customer_name')?.addEventListener('input',   debouncedSave);
document.getElementById('customer_name_m')?.addEventListener('input', debouncedSave);

// ─── ALERT DISMISS ───────────────────────────────────────────────
document.querySelectorAll('.alert-bar .dismiss').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.alert-bar').remove());
});
setTimeout(() => {
    document.querySelectorAll('.alert-bar').forEach(a => { a.style.opacity = '0'; });
    setTimeout(() => document.querySelectorAll('.alert-bar').forEach(a => a.remove()), 400);
}, 6000);

// ─── INIT ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (DB_DRAFT_CART?.length > 0) {
    cart = DB_DRAFT_CART;
    localStorage.setItem('pos_cart', JSON.stringify(cart));
        const ot = DB_DRAFT_META?.order_type || 'dine_in';
        setOrderType(ot, 'desktop'); setOrderType(ot, 'mobile');
        if (DB_DRAFT_META?.customer_name) {
            ['customer_name','customer_name_m'].forEach(id => {
                const el = document.getElementById(id); if (el) el.value = DB_DRAFT_META.customer_name;
            });
        }
        if (DB_DRAFT_META?.table_id) {
            const tid = String(DB_DRAFT_META.table_id);
            ['table_id','table_id_m'].forEach(id => {
                const s = document.getElementById(id);
                if (s?.querySelector(`option[value="${tid}"]`)) s.value = tid;
            });
        }
    } else {
        loadCart(); setOrderType('dine_in','desktop'); setOrderType('dine_in','mobile');
    }

    document.querySelectorAll('.menu-item-card').forEach(card => {
        const id       = card.dataset.itemId;
        const itemData = stockData[id];
        const variants = variantsMap[id] || [];
        const noPrice  = card.dataset.noPrice === 'true';
        let hasStock;
        if (variants.length > 0) { hasStock = variants.some(v => itemData?.variants?.[v.variant_id]?.has_stock ?? false); }
        else { hasStock = !noPrice && (itemData?.base?.has_stock ?? false); }
        if (!hasStock) {
            card.classList.add('out-of-stock'); card.dataset.hasStock = 'false';
            if (!card.querySelector('.out-of-stock-badge')) {
                const b = document.createElement('div');
                b.className = 'out-of-stock-badge';
                b.textContent = noPrice ? 'Unavailable' : 'Out of Stock';
                if (noPrice) b.style.background = 'var(--warm-gray)';
                card.prepend(b);
            }
        }
    });

    validateAndRender();

    if (AVAILABLE_TABLES.length === 1) {
        const tid = String(AVAILABLE_TABLES[0].table_id);
        ['table_id','table_id_m'].forEach(id => {
            const s = document.getElementById(id);
            if (s?.querySelector(`option[value="${tid}"]`)) s.value = tid;
        });
    }

    if (!isMobile()) setTimeout(() => document.getElementById('menuSearch')?.focus(), 150);

    if (isMobile()) {
        const bar = document.getElementById('mobileBottomBar'); if (bar) bar.style.display = 'flex';
        const drw = document.getElementById('mobileCartDrawer'); if (drw) drw.style.display = 'flex';
    }

    const drawer = document.getElementById('mobileCartDrawer');
    if (drawer) {
        let sy = 0;
        drawer.addEventListener('touchstart', e => { sy = e.touches[0].clientY; }, { passive: true });
        drawer.addEventListener('touchend',   e => { if (e.changedTouches[0].clientY - sy > 80) closeCartDrawer(); }, { passive: true });
    }
});

window.addEventListener('resize', () => {
    const bar = document.getElementById('mobileBottomBar');
    const drw = document.getElementById('mobileCartDrawer');
    if (isMobile()) {
        if (bar) bar.style.display = 'flex';
        if (drw) drw.style.display = 'flex';
    } else {
        if (bar) bar.style.display = 'none';
        if (drw) { drw.style.display = 'none'; closeCartDrawer(); }
    }
});
</script>
</body>
</html>