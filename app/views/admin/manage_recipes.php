<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">

    <title>Recipe Management - Cafeteria POS</title>

    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Fraunces:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/manage_recipes.css">

    <style>

        /* ── Variant-grouped ingredient sections ── */

        .variant-section { margin-bottom: 1.5rem; }

        .variant-section:last-child { margin-bottom: 0; }



        .variant-section-header {

            display: flex; align-items: center; gap: 0.55rem;

            padding: 0.55rem 0.9rem; border-radius: 10px; margin-bottom: 0.6rem;

            font-size: 0.82rem; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase;

        }

        .variant-section-header.is-base    { background: rgba(15,76,92,0.08);   color: #0a3a47; border-left: 3px solid #0f4c5c; }

        .variant-section-header.is-variant { background: rgba(255,107,74,0.08); color: #b84020; border-left: 3px solid #ff6b4a; }

        .variant-section-header i { font-size: 0.9rem; opacity: 0.8; }

        .ing-count-pill {

            margin-left: auto; background: rgba(0,0,0,0.06); color: inherit;

            border-radius: 20px; padding: 0.15rem 0.55rem; font-size: 0.7rem; font-weight: 600;

        }

        .ing-table-wrap { border: 1px solid #e8edf5; border-radius: 12px; overflow: hidden; }

        .no-ingredients { padding: 0.65rem 1rem; font-size: 0.8rem; color: #8a9bb0; font-style: italic; }



        .variant-badge {

            display: inline-flex; align-items: center; gap: 0.3rem;

            padding: 0.22rem 0.65rem; border-radius: 20px; font-size: 0.72rem; font-weight: 700;

        }

        .variant-badge.base  { background: rgba(15,76,92,0.1);   color: #0f4c5c; }

        .variant-badge.named { background: rgba(255,107,74,0.12); color: #c04020; }



        /* ── Copy-base banner ── */

        .copy-base-banner {

            background: linear-gradient(135deg, rgba(255,107,74,0.07), rgba(15,76,92,0.06));

            border: 1px solid rgba(255,107,74,0.25);

            border-radius: 14px;

            padding: 0.9rem 1.1rem;

            margin-bottom: 1.2rem;

            display: flex;

            align-items: flex-start;

            gap: 0.75rem;

            font-size: 0.84rem;

        }

        .copy-base-banner i { color: var(--secondary); font-size: 1.1rem; flex-shrink: 0; margin-top: 2px; }

        .copy-base-banner strong { color: var(--navy); }

        .copy-base-banner p { margin: 0.25rem 0 0; color: var(--warm-gray); font-size: 0.8rem; line-height: 1.5; }



        /* ── Copy button ── */

        .btn-copy-base {

            background: linear-gradient(135deg, #ff6b4a, #e05535);

            color: white; border: none; border-radius: 40px;

            padding: 0.6rem 1.4rem; font-size: 0.88rem; font-weight: 700;

            display: inline-flex; align-items: center; gap: 0.5rem;

            cursor: pointer; transition: all 0.2s; white-space: nowrap;

        }

        .btn-copy-base:hover {

            background: linear-gradient(135deg, #e05535, #c0441e);

            transform: translateY(-2px);

            box-shadow: 0 8px 16px rgba(255,107,74,0.35);

            color: white;

        }

        .btn-copy-base:disabled {

            opacity: 0.55; transform: none; box-shadow: none; cursor: not-allowed;

        }



        /* ── Copy-to-variant modal options ── */

        .variant-copy-option {

            border: 2px solid var(--border); border-radius: 14px;

            padding: 0.85rem 1.1rem; cursor: pointer; transition: all 0.18s;

            display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.6rem;

        }

        .variant-copy-option:last-child { margin-bottom: 0; }

        .variant-copy-option:hover { border-color: var(--secondary); background: rgba(255,107,74,0.04); }

        .variant-copy-option input[type="radio"] { accent-color: var(--secondary); width: 18px; height: 18px; flex-shrink: 0; }

        .variant-copy-option-name { font-weight: 700; color: var(--navy); font-size: 0.9rem; }

        .variant-copy-option-sub  { font-size: 0.75rem; color: var(--warm-gray); }

        .variant-copy-option.selected { border-color: var(--secondary); background: rgba(255,107,74,0.06); }



        /* ── Info meta row in detail modal ── */

        .recipe-meta {

            display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;

            margin-bottom: 1.1rem; padding-bottom: 1rem;

            border-bottom: 1px solid #edf2f7;

            font-size: 0.82rem; color: var(--warm-gray);

        }

        .recipe-meta i { color: var(--primary-light); }



        /* ── Empty variant notice ── */

        .no-recipe-yet {

            text-align: center; padding: 2rem 1rem;

            color: var(--warm-gray); font-size: 0.88rem;

        }

        .no-recipe-yet i { display: block; font-size: 2rem; opacity: 0.25; margin-bottom: 0.6rem; }

    </style>

</head>

<body>



<?php

/* ═══════════════════════════════════════════════════════════

   HELPER — renders one ingredient table block

   $locked = true  → base rows are read-only (template mode)

═══════════════════════════════════════════════════════════ */

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

            <span style="margin-left:.35rem;font-size:.7rem;font-weight:600;

                         background:rgba(244,185,66,.18);color:#7a5500;

                         border-radius:20px;padding:.15rem .6rem;display:inline-flex;align-items:center;gap:.3rem;">

                <i class="fas fa-lock" style="font-size:.65rem;"></i> Template — read only

            </span>

        <?php endif; ?>

        <span class="ing-count-pill"><?= $count ?> ingredient<?= $count !== 1 ? 's' : '' ?></span>

    </div>

    <?php if ($locked): ?>

    <div style="padding:.55rem 1rem .4rem;font-size:.78rem;color:#7a6030;

                background:rgba(244,185,66,.07);border-bottom:1px dashed rgba(244,185,66,.35);

                display:flex;align-items:center;gap:.45rem;">

        <i class="fas fa-circle-info" style="color:#c4930a;"></i>

        This base recipe has been copied to variants and is now a <strong>template</strong>.

        Edit ingredients on the individual variant sections instead.

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

                            <span class="ing-dot" <?= $locked ? 'style="background:#c4930a;"' : '' ?>></span>

                            <span style="font-weight:600;color:var(--navy);"><?= htmlspecialchars($r['ingredient_name']) ?></span>

                        </div>

                    </td>

                    <td data-label="Qty Required">

                        <span class="qty-badge" <?= $locked ? 'style="background:#c4930a;"' : '' ?>>

                            <?= htmlspecialchars($r['quantity_required'] . ' ' . $r['unit_code']) ?>

                        </span>

                    </td>

                    <td data-label="Actions" class="text-end">

                        <?php if ($locked): ?>

                            <span title="Locked — edit via variant" style="color:#c4930a;font-size:.8rem;opacity:.7;">

                                <i class="fas fa-lock"></i>

                            </span>

                        <?php else: ?>

                        <div class="d-flex gap-2 justify-content-end">

                            <button type="button" class="btn-icon warn" title="Edit quantity"

                                    data-bs-toggle="modal"

                                    data-bs-target="#editModal<?= $r['recipe_id'] ?>">

                                <i class="fas fa-edit"></i>

                            </button>

                            <button type="button" class="btn-icon del" title="Remove ingredient"

                                    data-bs-toggle="modal"

                                    data-bs-target="#deleteModal<?= $r['recipe_id'] ?>">

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



<!-- ═══════ TOPBAR ═══════ -->

<div class="topbar d-flex align-items-center justify-content-between">

    <div class="d-flex align-items-center gap-3">

        <button class="mobile-menu-btn d-lg-none" id="menuToggle" aria-label="Toggle menu">

            <i class="fas fa-bars"></i>

        </button>

        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-none d-sm-flex">

            <i class="fas fa-utensils"></i><span>Dine with Din - Cafeteria</span>

        </a>

        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-sm-none">

            <i class="fas fa-utensils"></i>

        </a>

    </div>

    <div class="dropdown">

        <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center gap-2"

                type="button" data-bs-toggle="dropdown" aria-expanded="false"

                style="color:var(--navy);font-weight:600;">

            <i class="far fa-user-circle" style="font-size:1.6rem;color:var(--secondary);"></i>

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



<div class="sidebar-overlay" id="sidebarOverlay"></div>



<!-- ═══════ SIDEBAR ═══════ -->

<div class="sidebar" id="sidebar">

    <nav class="sidebar-nav">

        <div class="nav-section-title">DASHBOARD</div>

        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>

        <div class="nav-section-title">MANAGEMENT</div>

        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a></div>

        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_recipes" class="nav-link active"><i class="fas fa-tags"></i> Recipes</a></div>

        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a></div>

        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a></div>

        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a></div>

        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>

        <div class="nav-section-title">SALES</div>

        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link"><i class="fas fa-cash-register"></i> POS System</a></div>

        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a></div>

        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>

    </nav>

</div>



<!-- ═══════ MAIN CONTENT ═══════ -->

<main class="main-content">



    <div class="page-header">

        <div>

            <h1><i class="fas fa-book-open"></i> Recipe Management</h1>

            <nav aria-label="breadcrumb">

                <ol class="breadcrumb">

                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a></li>

                    <li class="breadcrumb-item active">Recipes</li>

                </ol>

            </nav>

        </div>

        <button type="button" class="btn-add" data-open-add-modal>

            <i class="fas fa-plus-circle"></i> Add Ingredient

        </button>

    </div>



    <?php if (!empty($_SESSION['success'])): ?>

        <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">

            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

        <?php unset($_SESSION['success']); ?>

    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>

        <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">

            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>



    <div class="section-header fade-in">

        <h5>

            <i class="fas fa-th-large" style="color:var(--primary);"></i>

            Menu Items with Recipes

            <span class="count-badge ms-1"><?= count($grouped) ?></span>

        </h5>

        <div class="search-box">

            <i class="fas fa-search"></i>

            <input type="text" class="form-control" id="searchInput" placeholder="Search menu item...">

        </div>

    </div>



    <?php if (empty($grouped)): ?>

        <div class="empty-state fade-in">

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



                <div class="card-inner">

                    <?php if (!empty($group['image_url'])): ?>

                        <div class="item-img-wrap">

                            <img src="<?= htmlspecialchars($group['image_url']) ?>"

                                 alt="<?= htmlspecialchars($group['item_name']) ?>"

                                 class="item-img" loading="lazy"

                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">

                            <div class="item-icon" style="display:none;"><i class="fas fa-bowl-food"></i></div>

                        </div>

                    <?php else: ?>

                        <div class="item-icon"><i class="fas fa-bowl-food"></i></div>

                    <?php endif; ?>



                    <div class="item-name"><?= htmlspecialchars($group['item_name']) ?></div>



                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">

                        <?php if ($hasBase && !$hasVars): ?>

                            <span class="variant-badge base"><i class="fas fa-tag"></i> Base Recipe</span>

                        <?php endif; ?>

                        <?php foreach (array_keys($group['variants']) as $vn): ?>

                            <span class="variant-badge named"><i class="fas fa-layer-group"></i> <?= htmlspecialchars($vn) ?></span>

                        <?php endforeach; ?>

                        <?php if ($needsCopy): ?>

                            <span class="variant-badge" style="background:rgba(255,107,74,0.12);color:#c04020;">

                                <i class="fas fa-copy"></i> Copy needed

                            </span>

                        <?php endif; ?>

                    </div>



                    <span class="ingredient-count">

                        <i class="fas fa-cubes" style="font-size:.72rem;"></i>

                        <?= $totalCount ?> ingredient<?= $totalCount !== 1 ? 's' : '' ?>

                    </span>



                    <div class="pill-row">

                        <?php foreach ($preview as $p): ?>

                            <span class="pill" title="<?= htmlspecialchars($p['ingredient_name']) ?>"><?= htmlspecialchars($p['ingredient_name']) ?></span>

                        <?php endforeach; ?>

                        <?php if ($more > 0): ?>

                            <span class="pill-more">+<?= $more ?> more</span>

                        <?php endif; ?>

                    </div>

                </div>



                <div class="card-footer-strip">

                    <small class="text-muted" style="font-size:.76rem;">Click to manage</small>

                    <span class="view-btn">View Recipe <i class="fas fa-arrow-right" style="font-size:.72rem;"></i></span>

                </div>

            </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>



</main>





<!-- ═══════════════════════════════════════════════════════════

     RECIPE DETAIL MODALS  (one per grouped item)

═══════════════════════════════════════════════════════════════ -->

<?php foreach ($grouped as $item_id => $group):

    $hasBase       = !empty($group['base']);

    $hasVars       = !empty($group['variants']);

    $totalIngCount = count($group['rows']);

    $needsCopy     = isset($item_variants_for_copy[$item_id]);

    $copyVariants  = $item_variants_for_copy[$item_id] ?? [];

    // Base is a locked template when: it has been copied (variant rows exist for this item)

    // i.e. the item has base rows AND actual variant recipe rows — not just menu_variants defined

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



            <div class="modal-body">



                <!-- Copy-base notice — only when base exists AND menu_variants exist but are not yet set up -->

                <?php if ($needsCopy): ?>

                <div class="copy-base-banner">

                    <i class="fas fa-circle-info"></i>

                    <div>

                        <strong>Variants detected without ingredients.</strong>

                        <p>Use "Copy Base → Variant" to duplicate the base recipe into a variant, then tweak quantities as needed.</p>

                    </div>

                </div>

                <?php endif; ?>



                <!-- Meta row -->

                <div class="recipe-meta">

                    <i class="fas fa-cubes"></i>

                    <strong><?= $totalIngCount ?></strong> ingredient<?= $totalIngCount !== 1 ? 's' : '' ?> total

                    <?php if ($hasVars): ?>

                        &mdash; <i class="fas fa-layer-group"></i> grouped by variant below

                    <?php endif; ?>

                </div>



                <!-- Base section -->

                <?php if ($hasBase): ?>

                    <?php renderIngredientSection($group['base'], 'Base Recipe (Template)', true, $baseIsLocked); ?>

                <?php endif; ?>



                <!-- Variant sections -->

                <?php foreach ($group['variants'] as $vName => $vRows): ?>

                    <?php renderIngredientSection($vRows, 'Variant: ' . $vName, false, false); ?>

                <?php endforeach; ?>



                <!-- Truly empty -->

                <?php if (!$hasBase && !$hasVars): ?>

                    <div class="no-recipe-yet">

                        <i class="fas fa-clipboard-list"></i>

                        No ingredients assigned yet. Use "Add Ingredient" to get started.

                    </div>

                <?php endif; ?>



            </div><!-- /.modal-body -->



            <div class="modal-footer" style="gap:0.6rem;flex-wrap:wrap;">

                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>



                <?php if ($needsCopy): ?>

                    <!-- Base exists, variants defined but not yet populated: show copy CTA + add -->

                    <button type="button"

                            class="btn-add"

                            style="border-radius:12px;padding:.6rem 1.2rem;font-size:.85rem;background:var(--primary);"

                            data-open-add-from-detail

                            data-item-id="<?= $item_id ?>">

                        <i class="fas fa-plus"></i> Add Ingredient

                    </button>

                    <button type="button"

                            class="btn-copy-base"

                            data-open-copy-modal

                            data-item-id="<?= $item_id ?>"

                            data-item-name="<?= htmlspecialchars($group['item_name']) ?>"

                            data-variants="<?= htmlspecialchars(json_encode($copyVariants)) ?>">

                        <i class="fas fa-copy"></i> Copy Base → Variant

                    </button>



                <?php elseif ($baseIsLocked): ?>

                    <!-- Base is now a locked template — add ingredients to variants only -->

                    <span style="font-size:.8rem;color:#7a6030;display:flex;align-items:center;gap:.4rem;">

                        <i class="fas fa-lock"></i>

                        Add ingredients to individual variants below.

                    </span>

                    <button type="button"

                            class="btn-add"

                            style="border-radius:12px;padding:.6rem 1.4rem;"

                            data-open-add-from-detail

                            data-item-id="<?= $item_id ?>">

                        <i class="fas fa-plus"></i> Add to Variant

                    </button>



                <?php else: ?>

                    <!-- Normal base-only item -->

                    <button type="button"

                            class="btn-add"

                            style="border-radius:12px;padding:.6rem 1.4rem;"

                            data-open-add-from-detail

                            data-item-id="<?= $item_id ?>">

                        <i class="fas fa-plus"></i> Add Ingredient

                    </button>

                <?php endif; ?>

            </div>



        </div>

    </div>

</div>

<?php endforeach; ?>





<!-- ═══════════════════════════════════════════════════════════

     COPY BASE → VARIANT  (single shared modal, JS fills it)

═══════════════════════════════════════════════════════════════ -->

<div class="modal fade" id="copyBaseModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="fas fa-copy me-2" style="color:var(--secondary);"></i>

                    Copy Base → Variant

                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_recipes" id="copyBaseForm">

                <div class="modal-body">

                    <input type="hidden" name="copy_base_to_variant" value="1">

                    <input type="hidden" name="item_id"    id="copyItemId">

                    <input type="hidden" name="variant_id" id="copyVariantId">



                    <p class="text-muted mb-3" style="font-size:.85rem;">

                        <i class="fas fa-circle-info me-1" style="color:var(--primary-light);"></i>

                        Select which variant to copy the base ingredients into.

                        Ingredients already assigned to that variant will be skipped automatically.

                    </p>



                    <div id="copyVariantOptions">

                        <!-- Populated by JS -->

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>

                    <button type="submit" class="btn-copy-base" id="copySubmitBtn" disabled>

                        <i class="fas fa-copy me-1"></i> Copy Ingredients

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>





<!-- ═══════════════════════════════════════════════════════════

     ADD INGREDIENT MODAL

═══════════════════════════════════════════════════════════════ -->

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

            <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_recipes">

                <div class="modal-body">

                    <input type="hidden" name="add_recipe" value="1">



                    <!-- Menu Item -->

                    <div class="mb-3">

                        <label for="add_item_id" class="form-label">Menu Item <span class="text-danger">*</span></label>

                        <div class="input-group">

                            <span class="input-group-text"><i class="fas fa-utensils"></i></span>

                            <select class="form-select" id="add_item_id" name="item_id" required>

                                <option value="">— Select Menu Item —</option>

                                <?php foreach ($menu_items as $item): ?>

                                    <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['item_name']) ?></option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>



                    <!--

                        "Applied To" — variant dropdown.

                        JS controls what appears here:

                          • Item has NO variants  → shows "Base Recipe (Standard)" + disabled

                          • Item HAS variants     → shows only variant options (no base)

                    -->

                    <div class="mb-3" id="variantPickerWrap">

                        <label for="variant_id_select" class="form-label fw-semibold">

                            Applied To <span class="text-muted fw-normal">(Scope)</span>

                        </label>

                        <div class="input-group">

                            <span class="input-group-text"><i class="fas fa-layer-group"></i></span>

                            <select name="variant_id" id="variant_id_select" class="form-select" disabled>

                                <option value="">— Select a menu item first —</option>

                            </select>

                        </div>

                        <div class="form-text" id="variantHelpText">Choose a menu item to see available scopes.</div>

                    </div>



                    <!-- Ingredient -->

                    <div class="mb-3">

                        <label for="add_ingredient_id" class="form-label">Ingredient <span class="text-danger">*</span></label>

                        <div class="input-group">

                            <span class="input-group-text"><i class="fas fa-box"></i></span>

                            <select class="form-select" id="add_ingredient_id" name="ingredient_id" required>

                                <option value="">— Select Ingredient —</option>

                                <?php foreach ($ingredients as $ing): ?>

                                    <option value="<?= $ing['ingredient_id'] ?>"><?= htmlspecialchars($ing['ingredient_name']) ?></option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>



                    <!-- Quantity -->

                    <div class="mb-3">

                        <label for="add_quantity" class="form-label">Quantity Required <span class="text-danger">*</span></label>

                        <div class="input-group">

                            <span class="input-group-text"><i class="fas fa-balance-scale"></i></span>

                            <input type="number" class="form-control" id="add_quantity"

                                   name="quantity_required" step="0.01" min="0.01" placeholder="0.00" required>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add to Recipe</button>

                </div>

            </form>

        </div>

    </div>

</div>





<!-- ═══════════════════════════════════════════════════════════

     EDIT & DELETE MODALS  (one pair per recipe row)

     IMPORTANT: each modal is a SIBLING — no nesting.

═══════════════════════════════════════════════════════════════ -->

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

                        <input type="text" class="form-control"

                               value="<?= htmlspecialchars($r['item_name']) ?>" disabled>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Applied To</label>

                        <input type="text" class="form-control"

                               value="<?= !empty($r['variant_name']) ? htmlspecialchars($r['variant_name']) : 'Base Recipe (Standard)' ?>"

                               disabled>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Ingredient</label>

                        <input type="text" class="form-control"

                               value="<?= htmlspecialchars($r['ingredient_name']) ?>" disabled>

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

                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>

                    <button type="submit" class="btn-warning-custom">

                        <i class="fas fa-save me-2"></i> Update Recipe

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



<!-- DELETE MODAL -->

<div class="modal fade" id="deleteModal<?= $r['recipe_id'] ?>" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="fas fa-exclamation-triangle" style="color:var(--warning);"></i> Confirm Remove

                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body text-center" style="padding:2rem 1.6rem;">

                <div style="width:64px;height:64px;border-radius:50%;

                            background:rgba(214,64,69,.1);

                            display:inline-flex;align-items:center;justify-content:center;

                            margin-bottom:1.2rem;">

                    <i class="fas fa-trash-alt fa-xl" style="color:var(--danger);"></i>

                </div>

                <h6 style="color:var(--navy);font-weight:700;margin-bottom:.5rem;">Remove this ingredient?</h6>

                <p class="text-muted mb-1" style="font-size:.88rem;">

                    <strong><?= htmlspecialchars($r['item_name']) ?></strong>

                    &mdash;

                    <?php if (!empty($r['variant_name'])): ?>

                        <span style="color:#c04020;"><?= htmlspecialchars($r['variant_name']) ?></span>

                    <?php else: ?>

                        <span class="text-muted">Base Recipe</span>

                    <?php endif; ?>

                </p>

                <p class="text-muted mb-0" style="font-size:.85rem;">

                    Ingredient: <strong><?= htmlspecialchars($r['ingredient_name']) ?></strong>

                </p>

                <p class="mt-2" style="font-size:.8rem;color:var(--danger);opacity:.8;">

                    This action cannot be undone.

                </p>

            </div>

            <div class="modal-footer justify-content-center gap-3">

                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>

                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_recipes" style="display:inline;">

                    <input type="hidden" name="delete_recipe" value="1">

                    <input type="hidden" name="recipe_id"     value="<?= $r['recipe_id'] ?>">

                    <button type="submit" class="btn-danger-custom">

                        <i class="fas fa-trash me-2"></i> Remove

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>



<?php endforeach; ?>





<!-- ════════════════════

     SCRIPTS

════════════════════ -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



<script>

/* ─────────────────────────────────────────────────────

   VARIANT DATA  (PHP → JS)

   Shape: { "item_id": [{variant_id, variant_name}, ...] }



   Rules encoded here:

     • If an item has variants in the map → it is a VARIANT item

     • If it has no entry (or empty array) → it is a BASE-only item

───────────────────────────────────────────────────── */

window._recipeVariants = <?php

    $map = [];



    // Start from all_variants_map (all menu_variants rows, active only)

    foreach ($all_variants_map as $iid => $variants) {

        $key = (string) $iid;

        if (!isset($map[$key])) $map[$key] = [];

        foreach ($variants as $v) {

            $map[$key][] = [

                'variant_id'   => (int) $v['variant_id'],

                'variant_name' => $v['variant_name'],

            ];

        }

    }



    echo json_encode($map, JSON_HEX_TAG | JSON_HEX_APOS);

?>;



/* ─────────────────────────────────────────────────────

   recipePage  — all modal orchestration

───────────────────────────────────────────────────── */

window.recipePage = (function () {



    /* ── helpers ── */

    function getEl(id) { return document.getElementById(id); }



    function escHtml(str) {

        const d = document.createElement('div');

        d.textContent = String(str ?? '');

        return d.innerHTML;

    }



    function getModal(el) { return bootstrap.Modal.getOrCreateInstance(el); }



    /* ── Open detail modal ── */

    function openDetailModal(itemId) {

        const el = getEl('recipeModal' + itemId);

        if (el) getModal(el).show();

    }



    /* ─────────────────────────────────────────

       populateVariants

       Fills the "Applied To" dropdown in the

       Add Ingredient modal based on the selected item.



       Logic:

         • Item HAS variants → show ONLY variant options (no base)

         • Item has NO variants → show "Base Recipe" (locked)

         • No item selected → placeholder + disabled

    ───────────────────────────────────────── */

    function populateVariants(itemId) {

        const sel      = getEl('variant_id_select');

        const helpText = getEl('variantHelpText');

        if (!sel) return;



        if (!itemId) {

            sel.innerHTML = '<option value="">— Select a menu item first —</option>';

            sel.disabled  = true;

            if (helpText) helpText.textContent = 'Choose a menu item to see available scopes.';

            return;

        }



        const variants = (window._recipeVariants[String(itemId)] || []);



        if (variants.length > 0) {

            // Variant item — only show variant options, never base

            sel.innerHTML = '';

            variants.forEach(function (v) {

                const opt = document.createElement('option');

                opt.value       = v.variant_id;

                opt.textContent = v.variant_name;

                sel.appendChild(opt);

            });

            sel.disabled = false;

            if (helpText) helpText.textContent = 'This item uses variants — select which variant this ingredient applies to.';

        } else {

            // Base-only item — lock to base

            sel.innerHTML = '<option value="">Base Recipe (Standard)</option>';

            sel.disabled  = true;

            if (helpText) helpText.textContent = 'This item has no variants — the ingredient applies to the base recipe.';

        }

    }



    /* ── Open Add modal (optionally pre-select item) ── */

    function openAddModal(itemId) {

        const el = getEl('addRecipeModal');

        if (!el) return;



        const itemSel = getEl('add_item_id');

        const qtySel  = getEl('add_quantity');

        const ingSel  = getEl('add_ingredient_id');



        if (itemSel) itemSel.value = itemId ? String(itemId) : '';

        populateVariants(itemId || null);

        if (qtySel) qtySel.value  = '';

        if (ingSel) ingSel.value  = '';



        getModal(el).show();

    }



    /* ── "Add Ingredient" inside a detail modal ── */

    function openAddFromDetail(btn) {

        const itemId      = btn.dataset.itemId;

        const parentModal = btn.closest('.modal');



        function doOpen() { openAddModal(itemId); }



        if (parentModal) {

            const inst = bootstrap.Modal.getInstance(parentModal);

            if (inst) {

                parentModal.addEventListener('hidden.bs.modal', function h() {

                    parentModal.removeEventListener('hidden.bs.modal', h);

                    doOpen();

                });

                inst.hide();

            } else {

                doOpen();

            }

        } else {

            doOpen();

        }

    }



    /* ── "Copy Base → Variant" inside a detail modal ── */

    function openCopyModal(btn) {

        const itemId      = btn.dataset.itemId;

        const variants    = JSON.parse(btn.dataset.variants || '[]');

        const parentModal = btn.closest('.modal');



        // Populate hidden fields

        const copyItemIdEl = getEl('copyItemId');

        if (copyItemIdEl) copyItemIdEl.value = itemId;



        const copyVariantIdEl = getEl('copyVariantId');

        if (copyVariantIdEl) copyVariantIdEl.value = '';



        const submitBtn = getEl('copySubmitBtn');

        if (submitBtn) submitBtn.disabled = true;



        // Build variant radio options

        const optContainer = getEl('copyVariantOptions');

        if (optContainer) {

            if (!variants.length) {

                optContainer.innerHTML = '<p class="text-muted fst-italic" style="font-size:.85rem;">No variants found for this item.</p>';

            } else {

                optContainer.innerHTML = variants.map(function (v) {

                    return `<label class="variant-copy-option" for="copyVariant_${v.variant_id}">

                        <input type="radio" id="copyVariant_${v.variant_id}" name="_copy_variant_radio" value="${v.variant_id}">

                        <div>

                            <div class="variant-copy-option-name">

                                <i class="fas fa-layer-group me-1" style="color:var(--secondary);font-size:.8rem;"></i>

                                ${escHtml(v.variant_name)}

                            </div>

                            <div class="variant-copy-option-sub">Base ingredients will be copied into this variant</div>

                        </div>

                    </label>`;

                }).join('');



                optContainer.querySelectorAll('input[type="radio"]').forEach(function (radio) {

                    radio.addEventListener('change', function () {

                        optContainer.querySelectorAll('.variant-copy-option').forEach(o => o.classList.remove('selected'));

                        this.closest('.variant-copy-option').classList.add('selected');

                        if (copyVariantIdEl) copyVariantIdEl.value = this.value;

                        if (submitBtn) submitBtn.disabled = false;

                    });

                });

            }

        }



        // Close detail modal → open copy modal

        function showCopy() {

            const copyEl = getEl('copyBaseModal');

            if (copyEl) getModal(copyEl).show();

        }



        if (parentModal) {

            const inst = bootstrap.Modal.getInstance(parentModal);

            if (inst) {

                parentModal.addEventListener('hidden.bs.modal', function h() {

                    parentModal.removeEventListener('hidden.bs.modal', h);

                    showCopy();

                });

                inst.hide();

            } else {

                showCopy();

            }

        } else {

            showCopy();

        }

    }



    return { openDetailModal, openAddModal, openAddFromDetail, openCopyModal, populateVariants };



}());

</script>



<!-- Page JS (sidebar, search, alerts, stagger) -->

<script src="<?= $baseUrl ?>/assets/js/manage_recipes.js"></script>



<script>

/* ─────────────────────────────────────────────────────

   All wiring done here — recipePage and manage_recipes.js

   are both loaded by this point.

───────────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {



    /* item-dropdown change → repopulate variant picker */

    document.getElementById('add_item_id')?.addEventListener('change', function () {

        recipePage.populateVariants(this.value || null);

    });



    /* Top "Add Ingredient" button */

    document.querySelector('[data-open-add-modal]')?.addEventListener('click', function () {

        recipePage.openAddModal(null);

    });



    /* Delegated: buttons inside detail modals */

    document.addEventListener('click', function (e) {

        const addBtn  = e.target.closest('[data-open-add-from-detail]');

        if (addBtn)  { e.preventDefault(); recipePage.openAddFromDetail(addBtn);  return; }



        const copyBtn = e.target.closest('[data-open-copy-modal]');

        if (copyBtn) { e.preventDefault(); recipePage.openCopyModal(copyBtn); return; }

    });

});

</script>



</body>

</html>