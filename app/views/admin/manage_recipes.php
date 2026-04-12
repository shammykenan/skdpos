<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Recipe Management — Dine with Din</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,700;9..144,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/manage_recipes.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/common.css">
</head>
<body>

<?php
function renderIngredientSection(array $rows, string $label, bool $isBase, bool $locked = false): void
{
    $count = count($rows);
    $hCls  = $isBase ? 'is-base' : 'is-variant';
    $iCls  = $isBase ? 'fas fa-tag' : 'fas fa-layer-group';
?>
<div class="variant-section">
    <div class="variant-section-header <?= $hCls ?>">
        <i class="<?= $iCls ?>"></i>
        <?= htmlspecialchars($label) ?>
        <?php if ($locked): ?>
            <span style="margin-left:.35rem;font-size:.7rem;font-weight:600;background:rgba(244,185,66,.18);color:#7a5500;border-radius:20px;padding:.15rem .6rem;display:inline-flex;align-items:center;gap:.3rem;">
                <i class="fas fa-lock" style="font-size:.65rem;"></i> Template — read only
            </span>
        <?php endif; ?>
        <span class="ing-count-pill"><?= $count ?> ingredient<?= $count !== 1 ? 's' : '' ?></span>
    </div>
    <?php if ($locked): ?>
    <div style="padding:.55rem 1rem .4rem;font-size:.78rem;color:#7a6030;background:rgba(244,185,66,.07);border-bottom:1px dashed rgba(244,185,66,.35);display:flex;align-items:center;gap:.45rem;">
        <i class="fas fa-circle-info" style="color:#c4930a;"></i>
        This base recipe has been copied to variants and is now a <strong>template</strong>. Edit ingredients on the individual variant sections instead.
    </div>
    <?php endif; ?>
    <div class="ing-table-wrap">
        <?php if (empty($rows)): ?>
            <div class="no-ingredients">No ingredients in this section.</div>
        <?php else: ?>
        <table class="ing-table" style="margin:0;">
            <thead>
                <tr>
                    <th style="width:38px;">#</th>
                    <th>Ingredient</th>
                    <th>Qty Required</th>
                    <th class="text-end"><?= $locked ? '' : 'Actions' ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $idx => $r): ?>
                <tr <?= $locked ? 'style="opacity:.7;"' : '' ?>>
                    <td data-label="#">
                        <span style="color:var(--warm-gray);font-size:.78rem;"><?= $idx + 1 ?></span>
                    </td>
                    <td data-label="Ingredient">
                        <div class="d-flex align-items-center gap-2">
                            <span class="acc-ing-dot" <?= $locked ? 'style="background:#c4930a;"' : '' ?>></span>
                            <span style="font-weight:600;color:var(--navy);"><?= htmlspecialchars($r['ingredient_name']) ?></span>
                        </div>
                    </td>
                    <td data-label="Qty Required">
                        <span class="acc-ing-qty" style="background:<?= $locked ? '#c4930a' : 'var(--primary)' ?>;">
                            <?= htmlspecialchars($r['quantity_required'] . ' ' . $r['unit_code']) ?>
                        </span>
                    </td>
                    <td data-label="Actions" class="text-end">
                        <?php if ($locked): ?>
                            <span title="Locked" style="color:#c4930a;font-size:.8rem;opacity:.7;"><i class="fas fa-lock"></i></span>
                        <?php else: ?>
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn-icon warn" title="Edit"
                                    data-bs-toggle="modal" data-bs-target="#editModal<?= $r['recipe_id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn-icon del" title="Remove"
                                    data-bs-toggle="modal" data-bs-target="#deleteModal<?= $r['recipe_id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php
}
?>

<!-- ═══ TOPBAR ═══════════════════════════════════════════════════════ -->
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
            <div class="brand-text-sub">Web-Based POS Management System</div>
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
                <div class="px-3 py-2" style="border-bottom:1px solid var(--border);margin-bottom:.4rem;">
                    <div style="font-weight:700;font-size:.9rem;color:var(--navy);"><?= htmlspecialchars($fullName) ?></div>
                    <div style="font-size:.78rem;color:var(--warm-gray);">Administrator</div>
                </div>
            </li>
            <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a></li>
        </ul>
    </div>
</header>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ═══ SIDEBAR ══════════════════════════════════════════════════════ -->
<nav class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="nav-wrap">
        <div class="nav-section">Dashboard</div>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>

        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_menu"        class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_recipes"      class="nav-link active"><i class="fas fa-tags"></i> Recipes</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients"  class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_addons"       class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables"       class="nav-link"><i class="fas fa-chair"></i> Tables</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_users"        class="nav-link"><i class="fas fa-users"></i> User Management</a>

        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"          class="nav-link"><i class="fas fa-cash-register"></i> POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders"       class="nav-link"><i class="fas fa-receipt"></i> Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"             class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a>
    </div>
</nav>

<!-- ═══ MAIN CONTENT ═════════════════════════════════════════════════ -->
<main class="main" id="mainContent">

    <!-- Page Head -->
    <div class="page-head">
        <div>
            <h1 class="page-title">Recipe Management</h1>
            <div class="breadcrumb-bar">
                <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:.6rem;"></i></span>
                <span>Recipes</span>
            </div>
        </div>
        <div class="head-actions">
            <button type="button" class="btn-primary-action" data-open-add-modal>
                <i class="fas fa-plus"></i> Add Ingredient
            </button>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert-bar success fade-up" role="alert">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button class="dismiss" aria-label="Close">&times;</button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-bar danger fade-up" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button class="dismiss" aria-label="Close">&times;</button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Section card with search -->
    <div class="section-card fade-up fade-up-1">
        <div class="section-card-header">
            <div class="section-card-title">
                <i class="fas fa-book-open" style="color:var(--primary-light);"></i>
                Menu Items with Recipes
                <span class="count-badge"><?= count($grouped) ?></span>
            </div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search menu item…" autocomplete="off">
            </div>
        </div>

        <?php if (empty($grouped)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h5>No recipes yet</h5>
                <p>Click "Add Ingredient" to assign ingredients to a menu item.</p>
            </div>
        <?php else: ?>
            <div class="recipes-grid" id="recipesGrid">
                <?php foreach ($grouped as $item_id => $group):
                    $rows       = $group['rows'];
                    $totalCount = count($rows);
                    $hasBase    = !empty($group['base']);
                    $hasVars    = !empty($group['variants']);
                    $needsCopy  = isset($item_variants_for_copy[$item_id]);
                    $preview    = array_slice($rows, 0, 3);
                    $more       = $totalCount - 3;
                ?>
                <div class="recipe-card"
                     data-item="<?= $item_id ?>"
                     data-name="<?= htmlspecialchars(strtolower($group['item_name'])) ?>"
                     role="button" tabindex="0"
                     aria-label="View recipe for <?= htmlspecialchars($group['item_name']) ?>"
                     onclick="recipePage.openDetailModal(<?= $item_id ?>)"
                     onkeydown="if(event.key==='Enter'||event.key===' '){recipePage.openDetailModal(<?= $item_id ?>);}">

                    <!-- Card image or placeholder -->
                    <div class="card-img-wrap">
                        <?php if (!empty($group['image_url'])): ?>
                            <img src="<?= htmlspecialchars($group['image_url']) ?>"
                                 alt="<?= htmlspecialchars($group['item_name']) ?>"
                                 loading="lazy"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div class="card-img-placeholder" style="display:none;"><i class="fas fa-bowl-food"></i></div>
                        <?php else: ?>
                            <div class="card-img-placeholder"><i class="fas fa-bowl-food"></i></div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <div class="card-item-name"><?= htmlspecialchars($group['item_name']) ?></div>

                        <div class="badge-row">
                            <?php if ($hasBase && !$hasVars): ?>
                                <span class="variant-badge base"><i class="fas fa-tag"></i> Base Recipe</span>
                            <?php endif; ?>
                            <?php foreach (array_keys($group['variants']) as $vn): ?>
                                <span class="variant-badge named"><i class="fas fa-layer-group"></i> <?= htmlspecialchars($vn) ?></span>
                            <?php endforeach; ?>
                            <?php if ($needsCopy): ?>
                                <span class="variant-badge copy"><i class="fas fa-copy"></i> Copy needed</span>
                            <?php endif; ?>
                        </div>

                        <div class="ingredient-count" id="cardCount_<?= $item_id ?>">
                            <i class="fas fa-cubes" style="font-size:.7rem;"></i>
                            <span class="count-num"><?= $totalCount ?></span> ingredient<?= $totalCount !== 1 ? 's' : '' ?>
                        </div>

                        <div class="pill-row" id="cardPills_<?= $item_id ?>">
                            <?php foreach ($preview as $p): ?>
                                <span class="pill" title="<?= htmlspecialchars($p['ingredient_name']) ?>"><?= htmlspecialchars($p['ingredient_name']) ?></span>
                            <?php endforeach; ?>
                            <?php if ($more > 0): ?>
                                <span class="pill-more">+<?= $more ?> more</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-footer-strip">
                        <small style="font-size:.75rem;color:var(--warm-gray);">Click to manage</small>
                        <span class="view-btn">View Recipe <i class="fas fa-arrow-right" style="font-size:.7rem;"></i></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</main>


<!-- ═══════════════════════════════════════════════════════════════════
     RECIPE DETAIL MODALS  (one per grouped item)
═══════════════════════════════════════════════════════════════════════ -->
<?php foreach ($grouped as $item_id => $group):
    $hasBase       = !empty($group['base']);
    $hasVars       = !empty($group['variants']);
    $needsCopy     = isset($item_variants_for_copy[$item_id]);
    $copyVariants  = $item_variants_for_copy[$item_id] ?? [];
    $baseIsLocked  = $hasBase && $hasVars;
?>
<div class="modal fade" id="recipeModal<?= $item_id ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <?php if (!empty($group['image_url'])): ?>
                    <img src="<?= htmlspecialchars($group['image_url']) ?>"
                         alt="<?= htmlspecialchars($group['item_name']) ?>"
                         class="modal-item-img" onerror="this.style.display='none';">
                <?php else: ?>
                    <div class="modal-item-icon"><i class="fas fa-bowl-food"></i></div>
                <?php endif; ?>
                <h5 class="modal-title">
                    <i class="fas fa-book-open" style="color:var(--secondary);"></i>
                    <?= htmlspecialchars($group['item_name']) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="max-height:70vh;overflow-y:auto;">

                <?php if ($needsCopy): ?>
                <div class="copy-base-banner">
                    <i class="fas fa-circle-info"></i>
                    <div>
                        <strong>Variants detected without ingredients.</strong>
                        <p>Use "Copy Base → Variant" to duplicate the base recipe into a variant, then tweak quantities as needed.</p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!$hasBase && !$hasVars): ?>
                    <div class="no-recipe-yet" id="emptyNotice_<?= $item_id ?>">
                        <i class="fas fa-clipboard-list"></i>
                        No ingredients assigned yet. Use "Add Ingredient" to get started.
                    </div>

                <?php else: ?>
                <div class="accordion-variant-list" id="accList_<?= $item_id ?>">

                    <?php if ($hasBase):
                        $baseCount = count($group['base']);
                        $accId     = 'acc_base_' . $item_id;
                    ?>
                    <div class="acc-section <?= $baseIsLocked ? 'is-template' : '' ?>" id="<?= $accId ?>_wrap">
                        <div class="acc-header" onclick="toggleAcc('<?= $accId ?>')">
                            <div class="acc-icon <?= $baseIsLocked ? 'tmpl' : 'base' ?>">
                                <i class="fas <?= $baseIsLocked ? 'fa-lock' : 'fa-tag' ?>"></i>
                            </div>
                            <div class="acc-meta">
                                <div class="acc-title">
                                    Base Recipe
                                    <?php if ($baseIsLocked): ?>
                                        <span class="acc-lock-badge"><i class="fas fa-lock"></i> Template</span>
                                    <?php endif; ?>
                                </div>
                                <div class="acc-subtitle" id="<?= $accId ?>_subtitle"><?= $baseCount ?> ingredient<?= $baseCount !== 1 ? 's' : '' ?></div>
                            </div>
                            <i class="fas fa-chevron-right acc-chevron"></i>
                        </div>
                        <div class="acc-body" id="<?= $accId ?>">
                            <?php if ($baseIsLocked): ?>
                            <div class="acc-lock-notice">
                                <i class="fas fa-circle-info"></i>
                                Template — edit quantities on individual variant sections instead.
                            </div>
                            <?php endif; ?>
                            <div id="<?= $accId ?>_rows">
                                <?php foreach ($group['base'] as $idx => $r): ?>
                                <div class="acc-ing-row">
                                    <span class="acc-ing-num"><?= $idx + 1 ?></span>
                                    <span class="acc-ing-dot" style="background:<?= $baseIsLocked ? '#c4930a' : 'var(--primary-light)' ?>;"></span>
                                    <span class="acc-ing-name"><?= htmlspecialchars($r['ingredient_name']) ?></span>
                                    <span class="acc-ing-qty" style="background:<?= $baseIsLocked ? '#c4930a' : 'var(--primary)' ?>;">
                                        <?= htmlspecialchars($r['quantity_required'] . ' ' . $r['unit_code']) ?>
                                    </span>
                                    <div class="acc-ing-actions">
                                        <?php if ($baseIsLocked): ?>
                                            <span style="color:#c4930a;font-size:.8rem;opacity:.65;" title="Locked">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                        <?php else: ?>
                                            <button type="button" class="btn-icon warn" title="Edit"
                                                    data-bs-toggle="modal" data-bs-target="#editModal<?= $r['recipe_id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn-icon del" title="Remove"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal<?= $r['recipe_id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (!$baseIsLocked): ?>
                            <div class="acc-add-row">
                                <button type="button" class="btn-modal-primary" style="font-size:.8rem;padding:.4rem 1rem;border-radius:10px;"
                                        data-open-add-from-detail data-item-id="<?= $item_id ?>">
                                    <i class="fas fa-plus"></i> Add Ingredient
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php foreach ($group['variants'] as $vName => $vRows):
                        $vCount = count($vRows);
                        $vId    = $vRows[0]['variant_id'] ?? null;
                        $accId  = 'acc_v_' . $item_id . '_' . ($vId ?? 0);
                    ?>
                    <div class="acc-section is-variant-section" id="<?= $accId ?>_wrap">
                        <div class="acc-header" onclick="toggleAcc('<?= $accId ?>')">
                            <div class="acc-icon variant"><i class="fas fa-layer-group"></i></div>
                            <div class="acc-meta">
                                <div class="acc-title"><?= htmlspecialchars($vName) ?></div>
                                <div class="acc-subtitle" id="<?= $accId ?>_subtitle"><?= $vCount ?> ingredient<?= $vCount !== 1 ? 's' : '' ?></div>
                            </div>
                            <i class="fas fa-chevron-right acc-chevron"></i>
                        </div>
                        <div class="acc-body" id="<?= $accId ?>">
                            <div id="<?= $accId ?>_rows">
                                <?php foreach ($vRows as $idx => $r): ?>
                                <div class="acc-ing-row">
                                    <span class="acc-ing-num"><?= $idx + 1 ?></span>
                                    <span class="acc-ing-dot" style="background:var(--secondary);"></span>
                                    <span class="acc-ing-name"><?= htmlspecialchars($r['ingredient_name']) ?></span>
                                    <span class="acc-ing-qty" style="background:var(--secondary);">
                                        <?= htmlspecialchars($r['quantity_required'] . ' ' . $r['unit_code']) ?>
                                    </span>
                                    <div class="acc-ing-actions">
                                        <button type="button" class="btn-icon warn" title="Edit"
                                                data-bs-toggle="modal" data-bs-target="#editModal<?= $r['recipe_id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn-icon del" title="Remove"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal<?= $r['recipe_id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="acc-add-row">
                                <button type="button" class="btn-modal-primary" style="font-size:.8rem;padding:.4rem 1rem;border-radius:10px;"
                                        data-open-add-from-detail
                                        data-item-id="<?= $item_id ?>"
                                        data-variant-id="<?= $vId ?>">
                                    <i class="fas fa-plus"></i> Add Ingredient
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
                <?php endif; ?>

            </div>

            <div class="modal-footer" style="gap:.6rem;flex-wrap:wrap;">
                <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Close</button>

                <?php if ($needsCopy): ?>
                    <button type="button" class="btn-modal-primary" style="border-radius:12px;"
                            data-open-add-from-detail data-item-id="<?= $item_id ?>">
                        <i class="fas fa-plus"></i> Add Ingredient
                    </button>
                    <button type="button" class="btn-copy-base"
                            data-open-copy-modal
                            data-item-id="<?= $item_id ?>"
                            data-item-name="<?= htmlspecialchars($group['item_name']) ?>"
                            data-variants="<?= htmlspecialchars(json_encode($copyVariants)) ?>">
                        <i class="fas fa-copy"></i> Copy Base → Variant
                    </button>

                <?php elseif ($baseIsLocked): ?>
                    <span style="font-size:.8rem;color:#7a6030;display:flex;align-items:center;gap:.4rem;">
                        <i class="fas fa-lock"></i> Add to individual variants below.
                    </span>
                    <button type="button" class="btn-modal-primary"
                            data-open-add-from-detail data-item-id="<?= $item_id ?>">
                        <i class="fas fa-plus"></i> Add to Variant
                    </button>

                <?php else: ?>
                    <button type="button" class="btn-modal-primary"
                            data-open-add-from-detail data-item-id="<?= $item_id ?>">
                        <i class="fas fa-plus"></i> Add Ingredient
                    </button>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
<?php endforeach; ?>


<!-- ═══════════════════════════════════════════════════════════════════
     COPY BASE → VARIANT  (shared modal)
═══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="copyBaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-copy" style="color:var(--secondary);"></i>
                    Copy Base → Variant
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_recipes" id="copyBaseForm">
                <div class="modal-body">
                    <input type="hidden" name="copy_base_to_variant" value="1">
                    <input type="hidden" name="item_id"    id="copyItemId">
                    <input type="hidden" name="variant_id" id="copyVariantId">

                    <p style="font-size:.85rem;color:var(--warm-gray);margin-bottom:1rem;">
                        <i class="fas fa-circle-info me-1" style="color:var(--primary-light);"></i>
                        Select which variant to copy the base ingredients into. Existing ingredients will be skipped automatically.
                    </p>

                    <div id="copyVariantOptions"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-copy-base" id="copySubmitBtn" disabled>
                        <i class="fas fa-copy"></i> Copy Ingredients
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════════
     ADD INGREDIENT MODAL
═══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="addRecipeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle" style="color:var(--secondary);"></i>
                    Assign Ingredient to Menu Item
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addRecipeForm" method="POST" action="<?= $baseUrl ?>/index.php?page=manage_recipes">
                <div class="modal-body">
                    <input type="hidden" name="add_recipe" value="1">
                    <input type="hidden" name="ajax"       value="1">

                    <div id="addRecipeFeedback" style="display:none;margin-bottom:1rem;"></div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="add_item_id" class="form-label">
                                Menu Item <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-utensils"></i></span>
                                <select class="form-select" id="add_item_id" name="item_id" required>
                                    <option value="">— Select a menu item —</option>
                                    <?php foreach ($menu_items as $item): ?>
                                        <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['item_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="variant_id_select" class="form-label">
                                Scope
                                <span style="font-weight:500;color:var(--warm-gray);font-size:.78rem;">(variant or base)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                                <select name="variant_id" id="variant_id_select" class="form-select" disabled>
                                    <option value="">— Pick a menu item first —</option>
                                </select>
                            </div>
                            <div class="form-text" id="variantHelpText">Select a menu item to see available scopes.</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="add_ingredient_id" class="form-label">
                                Ingredient <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-box"></i></span>
                                <select class="form-select" id="add_ingredient_id" name="ingredient_id" required>
                                    <option value="">— Select an ingredient —</option>
                                    <?php foreach ($ingredients as $ing): ?>
                                        <option value="<?= $ing['ingredient_id'] ?>"><?= htmlspecialchars($ing['ingredient_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="add_quantity" class="form-label">
                                Quantity Required <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-balance-scale"></i></span>
                                <input type="number" class="form-control" id="add_quantity"
                                       name="quantity_required" step="0.01" min="0.01"
                                       placeholder="e.g. 0.25" required>
                                <span class="input-group-text" id="ingredient_unit_display"
                                      style="min-width:54px;justify-content:center;font-weight:700;color:var(--primary);background:#edf6f9;font-size:.9rem;">
                                    —
                                </span>
                            </div>
                            <div class="form-text">Unit auto-fills when you pick an ingredient.</div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modal-primary" id="addRecipeSubmitBtn">
                        <i class="fas fa-plus"></i> Add to Recipe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════════
     EDIT & DELETE MODALS  (one pair per recipe row)
═══════════════════════════════════════════════════════════════════════ -->
<?php foreach ($recipes as $r): ?>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal<?= $r['recipe_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit" style="color:var(--warning);"></i> Edit Quantity
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_recipes">
                <div class="modal-body">
                    <input type="hidden" name="edit_recipe" value="1">
                    <input type="hidden" name="recipe_id"   value="<?= $r['recipe_id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Menu Item</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($r['item_name']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Applied To</label>
                        <input type="text" class="form-control"
                               value="<?= !empty($r['variant_name']) ? htmlspecialchars($r['variant_name']) : 'Base Recipe (Standard)' ?>"
                               disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ingredient</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($r['ingredient_name']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="edit_qty_<?= $r['recipe_id'] ?>" class="form-label">
                            Quantity Required <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" min="0.01"
                               name="quantity_required"
                               id="edit_qty_<?= $r['recipe_id'] ?>"
                               value="<?= htmlspecialchars($r['quantity_required']) ?>"
                               class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modal-warning">
                        <i class="fas fa-save me-1"></i> Update Recipe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal<?= $r['recipe_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding:1rem 1.6rem 1.25rem;">
                <div class="confirm-icon danger"><i class="fas fa-trash"></i></div>
                <h5 style="font-family:'Fraunces',serif;font-weight:700;color:var(--navy);margin-bottom:.5rem;">Remove this ingredient?</h5>
                <p style="font-size:.85rem;color:var(--warm-gray);margin-bottom:.4rem;">
                    <strong><?= htmlspecialchars($r['item_name']) ?></strong>
                    &mdash;
                    <?php if (!empty($r['variant_name'])): ?>
                        <span style="color:#c04020;"><?= htmlspecialchars($r['variant_name']) ?></span>
                    <?php else: ?>
                        <span>Base Recipe</span>
                    <?php endif; ?>
                </p>
                <p style="font-size:.85rem;color:var(--warm-gray);margin-bottom:.8rem;">
                    Ingredient: <strong><?= htmlspecialchars($r['ingredient_name']) ?></strong>
                </p>
                <div style="background:rgba(214,64,69,0.06);border:1px dashed rgba(214,64,69,0.3);border-radius:var(--radius-md);padding:.65rem 1rem;font-size:.78rem;color:#9e3a34;">
                    <i class="fas fa-exclamation-triangle me-1"></i> This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer" style="border-top:none;">
                <button type="button" class="btn-modal-secondary" style="flex:1;" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_recipes" style="flex:1;">
                    <input type="hidden" name="delete_recipe" value="1">
                    <input type="hidden" name="recipe_id"     value="<?= $r['recipe_id'] ?>">
                    <button type="submit" class="btn-modal-danger">
                        <i class="fas fa-trash me-1"></i> Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php endforeach; ?>


<!-- ═══ SCRIPTS ══════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $baseUrl ?>/assets/js/manage_recipes.js"></script>
<script src="<?= $baseUrl ?>/assets/js/common.js"></script>
<script>
/* ── PHP → JS data ───────────────────────────────────────────────── */
window._recipeVariants = <?php
    $map = [];
    foreach ($all_variants_map as $iid => $variants) {
        $key = (string) $iid;
        if (!isset($map[$key])) $map[$key] = [];
        foreach ($variants as $v) {
            $map[$key][] = ['variant_id' => (int)$v['variant_id'], 'variant_name' => $v['variant_name']];
        }
    }
    echo json_encode($map, JSON_HEX_TAG | JSON_HEX_APOS);
?>;

window._ingredientUnits = <?php
    $umap = [];
    foreach ($ingredients as $ing) {
        $umap[(string)$ing['ingredient_id']] = [
            'unit_code'  => $ing['unit_code']  ?? '',
            'unit_label' => $ing['unit_label'] ?? '',
        ];
    }
    echo json_encode($umap, JSON_HEX_TAG | JSON_HEX_APOS);
?>;
</script>

<div id="recipeToast"><i class="fas fa-check-circle"></i><span id="recipeToastMsg"></span></div>
</body>
</html>