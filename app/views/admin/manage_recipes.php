<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Recipe Management - Cafeteria POS</title>
<link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Fraunces:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <!-- Page Stylesheet -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/manage_recipes.css">
</head>
<body>

<!-- ═══════ TOPBAR ═══════ -->
<div class="topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <button class="mobile-menu-btn d-lg-none" id="menuToggle" aria-label="Toggle menu">
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
        <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center gap-2"
                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                style="color: var(--navy); font-weight: 600;">
            <i class="far fa-user-circle" style="font-size: 1.6rem; color: var(--secondary);"></i>
            <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ═══════ SIDEBAR ═══════ -->
<div class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
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
            <a href="<?= $baseUrl ?>/index.php?page=manage_recipes" class="nav-link active">
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
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a></div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link">
                <i class="fas fa-users"></i> User Management
            </a>
        </div>

        <div class="nav-section-title">SALES</div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link">
                <i class="fas fa-cash-register"></i> POS System
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link">
                <i class="fas fa-receipt"></i> Orders
            </a>
        </div>
        <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
        </div>
    </nav>
</div>

<!-- ═══════ MAIN CONTENT ═══════ -->
<main class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1><i class="fas fa-book-open"></i> Recipe Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Recipes</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addRecipeModal">
            <i class="fas fa-plus-circle"></i> Add Recipe
        </button>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Section header + search -->
    <div class="section-header fade-in">
        <h5>
            <i class="fas fa-th-large" style="color: var(--primary);"></i>
            Menu Items with Recipes
            <span class="count-badge ms-1"><?= count($grouped) ?></span>
        </h5>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" id="searchInput" placeholder="Search menu item...">
        </div>
    </div>

    <!-- ── CARD GRID ── -->
    <?php if (empty($grouped)): ?>
        <div class="empty-state fade-in">
            <i class="fas fa-clipboard-list"></i>
            <h5>No recipes yet</h5>
            <p>Click "Add Recipe" to assign ingredients to a menu item.</p>
        </div>
    <?php else: ?>
        <div class="recipes-grid" id="recipesGrid">
            <?php foreach ($grouped as $item_id => $group):
                $rows    = $group['rows'];
                $count   = count($rows);
                $preview = array_slice($rows, 0, 3);
                $more    = $count - 3;
            ?>
                <div class="recipe-card"
                     data-item="<?= $item_id ?>"
                     data-name="<?= htmlspecialchars(strtolower($group['item_name'])) ?>"
                     role="button"
                     tabindex="0"
                     aria-label="View recipe for <?= htmlspecialchars($group['item_name']) ?>"
                     data-bs-toggle="modal"
                     data-bs-target="#recipeModal<?= $item_id ?>">

                    <div class="card-inner">
                        <?php if (!empty($group['image_url'])): ?>
                            <div class="item-img-wrap">
                                <img src="<?= htmlspecialchars($group['image_url']) ?>"
                                     alt="<?= htmlspecialchars($group['item_name']) ?>"
                                     class="item-img"
                                     loading="lazy"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div class="item-icon" style="display:none;">
                                    <i class="fas fa-bowl-food"></i>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="item-icon">
                                <i class="fas fa-bowl-food"></i>
                            </div>
                        <?php endif; ?>
                        <div class="item-name"><?= htmlspecialchars($group['item_name']) ?></div>
                        <span class="ingredient-count">
                            <i class="fas fa-cubes" style="font-size: .72rem;"></i>
                            <?= $count ?> ingredient<?= $count !== 1 ? 's' : '' ?>
                        </span>
                        <div class="pill-row">
                            <?php foreach ($preview as $p): ?>
                                <span class="pill" title="<?= htmlspecialchars($p['ingredient_name']) ?>">
                                    <?= htmlspecialchars($p['ingredient_name']) ?>
                                </span>
                            <?php endforeach; ?>
                            <?php if ($more > 0): ?>
                                <span class="pill-more">+<?= $more ?> more</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-footer-strip">
                        <small class="text-muted" style="font-size: .76rem; color: var(--warm-gray);">
                            Click to manage
                        </small>
                        <span class="view-btn">
                            View Recipe <i class="fas fa-arrow-right" style="font-size: .72rem;"></i>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div><!-- /recipes-grid -->
    <?php endif; ?>

</main><!-- /main-content -->


<!-- ═══════════════════════════════════════════════════════════
     RECIPE DETAIL MODALS  (one per menu item)
═══════════════════════════════════════════════════════════════ -->
<?php foreach ($grouped as $item_id => $group): ?>
<div class="modal fade"
     id="recipeModal<?= $item_id ?>"
     tabindex="-1"
     aria-labelledby="recipeModalLabel<?= $item_id ?>"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <?php if (!empty($group['image_url'])): ?>
                    <img src="<?= htmlspecialchars($group['image_url']) ?>"
                         alt="<?= htmlspecialchars($group['item_name']) ?>"
                         class="modal-item-img"
                         onerror="this.style.display='none';">
                <?php else: ?>
                    <div class="modal-item-icon">
                        <i class="fas fa-bowl-food"></i>
                    </div>
                <?php endif; ?>
                <h5 class="modal-title" id="recipeModalLabel<?= $item_id ?>">
                    <i class="fas fa-book-open" style="color: var(--secondary);"></i>
                    <?= htmlspecialchars($group['item_name']) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p class="text-muted mb-3" style="font-size: .83rem;">
                    <i class="fas fa-info-circle me-1" style="color: var(--primary-light);"></i>
                    <?= count($group['rows']) ?> ingredient<?= count($group['rows']) !== 1 ? 's' : '' ?> in this recipe.
                </p>

                <table class="ing-table">
                    <thead>
                        <tr>
                            <th style="width: 38px;">#</th>
                            <th>Ingredient</th>
                            <th>Qty Required</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group['rows'] as $idx => $r): ?>
                        <tr>
                            <td data-label="#">
                                <span style="color: var(--warm-gray); font-size: .78rem;">
                                    <?= $idx + 1 ?>
                                </span>
                            </td>
                            <td data-label="Ingredient">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="ing-dot"></span>
                                    <span style="font-weight: 600; color: var(--navy);">
                                        <?= htmlspecialchars($r['ingredient_name']) ?>
                                    </span>
                                </div>
                            </td>
                            <td data-label="Qty Required">
                                <span class="qty-badge">
                                    <?= htmlspecialchars($r['quantity_required'] . ' ' . $r['unit_code']) ?>
                                </span>
                            </td>
                            <td data-label="Actions" class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button"
                                            class="btn-icon warn"
                                            title="Edit quantity"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal<?= $r['recipe_id'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button"
                                            class="btn-icon del"
                                            title="Remove ingredient"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal<?= $r['recipe_id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div><!-- /modal-body -->

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Close
                </button>
                <button type="button"
                        class="btn-add"
                        style="border-radius: 12px; padding: .6rem 1.4rem;"
                        data-bs-toggle="modal"
                        data-bs-target="#addRecipeModal">
                    <i class="fas fa-plus"></i> Add Ingredient
                </button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>


<!-- ═══════════════════════════════════════════════════════════
     ADD RECIPE MODAL
═══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="addRecipeModal" tabindex="-1" aria-labelledby="addRecipeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="addRecipeModalLabel">
                    <i class="fas fa-plus-circle" style="color: var(--secondary);"></i>
                    Assign Ingredient to Menu Item
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_recipes">
                <div class="modal-body">
                    <input type="hidden" name="add_recipe" value="1">

                    <div class="mb-3">
                        <label for="add_item_id" class="form-label">
                            Menu Item <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-utensils"></i></span>
                            <select class="form-select" id="add_item_id" name="item_id" required>
                                <option value="">-- Select Menu Item --</option>
                                <?php foreach ($menu_items as $item): ?>
                                    <option value="<?= $item['item_id'] ?>">
                                        <?= htmlspecialchars($item['item_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="add_ingredient_id" class="form-label">
                            Ingredient <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                            <select class="form-select" id="add_ingredient_id" name="ingredient_id" required>
                                <option value="">-- Select Ingredient --</option>
                                <?php foreach ($ingredients as $ing): ?>
                                    <option value="<?= $ing['ingredient_id'] ?>">
                                        <?= htmlspecialchars($ing['ingredient_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="add_quantity" class="form-label">
                            Quantity Required <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-balance-scale"></i></span>
                            <input type="number"
                                   class="form-control"
                                   id="add_quantity"
                                   name="quantity_required"
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Add to Recipe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     EDIT & DELETE MODALS  (one per recipe row)
═══════════════════════════════════════════════════════════════ -->
<?php foreach ($recipes as $r): ?>

<!-- EDIT MODAL -->
<div class="modal fade"
     id="editModal<?= $r['recipe_id'] ?>"
     tabindex="-1"
     aria-labelledby="editModalLabel<?= $r['recipe_id'] ?>"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?= $r['recipe_id'] ?>">
                    <i class="fas fa-edit" style="color: var(--warning);"></i>
                    Edit Quantity
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_recipes">
                <div class="modal-body">
                    <input type="hidden" name="edit_recipe" value="1">
                    <input type="hidden" name="recipe_id" value="<?= $r['recipe_id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Menu Item</label>
                        <input type="text"
                               class="form-control"
                               value="<?= htmlspecialchars($r['item_name']) ?>"
                               disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ingredient</label>
                        <input type="text"
                               class="form-control"
                               value="<?= htmlspecialchars($r['ingredient_name']) ?>"
                               disabled>
                    </div>

                    <div class="mb-3">
                        <label for="edit_qty_<?= $r['recipe_id'] ?>" class="form-label">
                            Quantity Required <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="quantity_required"
                               id="edit_qty_<?= $r['recipe_id'] ?>"
                               value="<?= htmlspecialchars($r['quantity_required']) ?>"
                               class="form-control"
                               required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn-warning-custom">
                        <i class="fas fa-save me-2"></i> Update Recipe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade"
     id="deleteModal<?= $r['recipe_id'] ?>"
     tabindex="-1"
     aria-labelledby="deleteModalLabel<?= $r['recipe_id'] ?>"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel<?= $r['recipe_id'] ?>">
                    <i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i>
                    Confirm Remove
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body text-center" style="padding: 2rem 1.6rem;">
                <div style="width: 64px; height: 64px; border-radius: 50%;
                            background: rgba(214,64,69,.1); display: inline-flex;
                            align-items: center; justify-content: center; margin-bottom: 1.2rem;">
                    <i class="fas fa-trash-alt fa-xl" style="color: var(--danger);"></i>
                </div>
                <h6 style="color: var(--navy); font-weight: 700; margin-bottom: .5rem;">
                    Remove this ingredient?
                </h6>
                <p class="text-muted mb-1" style="font-size: .88rem;">
                    <strong><?= htmlspecialchars($r['item_name']) ?></strong>
                </p>
                <p class="text-muted mb-0" style="font-size: .85rem;">
                    Ingredient: <?= htmlspecialchars($r['ingredient_name']) ?>
                </p>
                <p class="mt-2" style="font-size: .8rem; color: var(--danger); opacity: .8;">
                    This action cannot be undone.
                </p>
            </div>

            <div class="modal-footer justify-content-center gap-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_recipes">
                    <input type="hidden" name="delete_recipe" value="1">
                    <input type="hidden" name="recipe_id" value="<?= $r['recipe_id'] ?>">
                    <button type="submit" class="btn-danger-custom">
                        <i class="fas fa-trash me-2"></i> Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php endforeach; ?>


<!-- Bootstrap 5 JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Page JS -->
<script src="<?= $baseUrl ?>/assets/js/manage_recipes.js"></script>

</body>
</html>