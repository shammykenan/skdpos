/* ============================================================
   MANAGE RECIPES — JavaScript
   All modal orchestration, AJAX submit, accordion toggle,
   sidebar toggle, and UI utilities.
   No backend logic changed — pure UI layer.
   ============================================================ */

/* ─────────────────────────────────────────────────────────────
   recipePage — all modal orchestration
───────────────────────────────────────────────────────────── */
window.recipePage = (function () {

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

    /* ── Populate "Applied To" variant dropdown ── */
    function populateVariants(itemId, preselectVariantId) {
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
            sel.innerHTML = '';
            variants.forEach(function (v) {
                const opt = document.createElement('option');
                opt.value       = v.variant_id;
                opt.textContent = v.variant_name;
                if (preselectVariantId && String(v.variant_id) === String(preselectVariantId)) {
                    opt.selected = true;
                }
                sel.appendChild(opt);
            });
            sel.disabled = false;
            if (helpText) helpText.textContent = 'This item uses variants — select which variant this ingredient applies to.';
        } else {
            sel.innerHTML = '<option value="">Base Recipe (Standard)</option>';
            sel.disabled  = true;
            if (helpText) helpText.textContent = 'This item has no variants — the ingredient applies to the base recipe.';
        }
    }

    /* ── Open Add modal ── */
    function openAddModal(itemId, variantId) {
        const el = getEl('addRecipeModal');
        if (!el) return;

        const itemSel = getEl('add_item_id');
        const qtySel  = getEl('add_quantity');
        const ingSel  = getEl('add_ingredient_id');
        const fb      = getEl('addRecipeFeedback');

        if (itemSel) itemSel.value = itemId ? String(itemId) : '';
        populateVariants(itemId || null, variantId || null);
        if (qtySel) qtySel.value = '';
        if (ingSel) ingSel.value = '';
        if (fb)     { fb.style.display = 'none'; fb.innerHTML = ''; }
        updateIngredientUnit(null);

        getModal(el).show();
    }

    /* ── "Add Ingredient" from inside a detail modal ── */
    function openAddFromDetail(btn) {
        const itemId    = btn.dataset.itemId;
        const variantId = btn.dataset.variantId || null;
        const parentModal = btn.closest('.modal');

        function doOpen() { openAddModal(itemId, variantId); }

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

    /* ── "Copy Base → Variant" from inside a detail modal ── */
    function openCopyModal(btn) {
        const itemId      = btn.dataset.itemId;
        const variants    = JSON.parse(btn.dataset.variants || '[]');
        const parentModal = btn.closest('.modal');

        const copyItemIdEl    = getEl('copyItemId');
        const copyVariantIdEl = getEl('copyVariantId');
        const submitBtn       = getEl('copySubmitBtn');

        if (copyItemIdEl)    copyItemIdEl.value    = itemId;
        if (copyVariantIdEl) copyVariantIdEl.value = '';
        if (submitBtn)       submitBtn.disabled    = true;

        const optContainer = getEl('copyVariantOptions');
        if (optContainer) {
            if (!variants.length) {
                optContainer.innerHTML = '<p style="font-size:.85rem;color:var(--warm-gray);font-style:italic;">No variants found for this item.</p>';
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

    /* ── Update unit display when ingredient changes ── */
    function updateIngredientUnit(ingredientId) {
        const unitDisplay = getEl('ingredient_unit_display');
        if (!unitDisplay) return;
        if (!ingredientId) { unitDisplay.textContent = '—'; return; }
        const u = (window._ingredientUnits || {})[String(ingredientId)];
        unitDisplay.textContent = (u && (u.unit_code || u.unit_label)) ? (u.unit_code || u.unit_label) : '—';
    }

    /* ── Inject edit + delete modals for a newly-added row ── */
    function injectRecipeModals(recipeId, ingName, qty, variantId) {
        if (getEl('editModal' + recipeId)) return;

        const scopeLabel = (variantId && variantId !== '' && variantId !== '0')
            ? ('Variant #' + variantId)
            : 'Base Recipe (Standard)';

        const formAction = document.getElementById('addRecipeForm')?.action || '';
        const baseUrl    = formAction.includes('/index.php') ? formAction.split('/index.php')[0] : '';
        const actionUrl  = baseUrl + '/index.php?page=manage_recipes';

        /* Edit modal */
        const editWrap = document.createElement('div');
        editWrap.innerHTML = `
        <div class="modal fade" id="editModal${recipeId}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit" style="color:var(--warning);"></i> Edit Quantity</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="${actionUrl}">
                        <div class="modal-body">
                            <input type="hidden" name="edit_recipe" value="1">
                            <input type="hidden" name="recipe_id"   value="${recipeId}">
                            <div class="mb-3">
                                <label class="form-label">Applied To</label>
                                <input type="text" class="form-control" value="${escHtml(scopeLabel)}" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ingredient</label>
                                <input type="text" class="form-control" value="${escHtml(ingName)}" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantity Required <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01"
                                       name="quantity_required" id="edit_qty_${recipeId}"
                                       value="${escHtml(String(qty))}" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-modal-warning"><i class="fas fa-save me-1"></i> Update Recipe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(editWrap.firstElementChild);

        /* Delete modal */
        const delWrap = document.createElement('div');
        delWrap.innerHTML = `
        <div class="modal fade" id="deleteModal${recipeId}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
                <div class="modal-content">
                    <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center" style="padding:1rem 1.6rem 1.25rem;">
                        <div class="confirm-icon danger"><i class="fas fa-trash"></i></div>
                        <h5 style="font-family:'Fraunces',serif;font-weight:700;color:var(--navy);margin-bottom:.5rem;">Remove this ingredient?</h5>
                        <p style="font-size:.85rem;color:var(--warm-gray);margin-bottom:.8rem;">Ingredient: <strong>${escHtml(ingName)}</strong></p>
                        <div style="background:rgba(214,64,69,0.06);border:1px dashed rgba(214,64,69,0.3);border-radius:12px;padding:.65rem 1rem;font-size:.78rem;color:#9e3a34;">
                            <i class="fas fa-exclamation-triangle me-1"></i> This action cannot be undone.
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top:none;">
                        <button type="button" class="btn-modal-secondary" style="flex:1;" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="${actionUrl}" style="flex:1;">
                            <input type="hidden" name="delete_recipe" value="1">
                            <input type="hidden" name="recipe_id"     value="${recipeId}">
                            <button type="submit" class="btn-modal-danger"><i class="fas fa-trash me-1"></i> Remove</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.appendChild(delWrap.firstElementChild);
    }

    /* ── Inject a new ingredient row live into the accordion ── */
    function injectIngredientRow(itemId, variantId, ingName, qty, unit, recipeId) {
        const isBase = !variantId || variantId === '' || variantId === '0';

        const rowsContainerId = isBase
            ? 'acc_base_' + itemId + '_rows'
            : 'acc_v_' + itemId + '_' + variantId + '_rows';

        const rowsContainer = getEl(rowsContainerId);
        if (!rowsContainer) return;

        const newIdx   = rowsContainer.querySelectorAll('.acc-ing-row').length + 1;
        const dotColor = isBase ? 'var(--primary-light)' : 'var(--secondary)';
        const qtyColor = isBase ? 'var(--primary)'       : 'var(--secondary)';

        if (recipeId) injectRecipeModals(recipeId, ingName, qty, variantId);

        const actionsHtml = recipeId
            ? `<button type="button" class="btn-icon warn" title="Edit"
                       data-bs-toggle="modal" data-bs-target="#editModal${recipeId}">
                   <i class="fas fa-edit"></i>
               </button>
               <button type="button" class="btn-icon del" title="Remove"
                       data-bs-toggle="modal" data-bs-target="#deleteModal${recipeId}">
                   <i class="fas fa-trash"></i>
               </button>`
            : '';

        const row = document.createElement('div');
        row.className = 'acc-ing-row new-row';
        row.innerHTML = `
            <span class="acc-ing-num">${newIdx}</span>
            <span class="acc-ing-dot" style="background:${dotColor};"></span>
            <span class="acc-ing-name">${escHtml(ingName)}</span>
            <span class="acc-ing-qty" style="background:${qtyColor};">${escHtml(qty + ' ' + unit)}</span>
            <div class="acc-ing-actions">${actionsHtml}</div>`;

        rowsContainer.appendChild(row);

        /* Update subtitle count */
        const accId    = isBase ? 'acc_base_' + itemId : 'acc_v_' + itemId + '_' + variantId;
        const subtitle = getEl(accId + '_subtitle');
        if (subtitle) {
            const count = rowsContainer.querySelectorAll('.acc-ing-row').length;
            subtitle.textContent = count + ' ingredient' + (count !== 1 ? 's' : '');
        }

        /* Open the accordion section */
        const wrap = getEl(accId + '_wrap');
        if (wrap && !wrap.classList.contains('is-open')) {
            const list = wrap.closest('.accordion-variant-list');
            if (list) list.querySelectorAll('.acc-section.is-open').forEach(s => s.classList.remove('is-open'));
            wrap.classList.add('is-open');
        }

        /* Update card ingredient count */
        const cardCountEl = getEl('cardCount_' + itemId);
        if (cardCountEl) {
            const numEl = cardCountEl.querySelector('.count-num');
            if (numEl) {
                const newCount = parseInt(numEl.textContent, 10) + 1;
                numEl.textContent = newCount;
                cardCountEl.lastChild.textContent = ' ingredient' + (newCount !== 1 ? 's' : '');
            }
        }

        /* Update card pills */
        const pillRow = getEl('cardPills_' + itemId);
        if (pillRow) {
            const pills    = pillRow.querySelectorAll('.pill');
            const moreSpan = pillRow.querySelector('.pill-more');
            if (pills.length < 3) {
                const pill = document.createElement('span');
                pill.className = 'pill';
                pill.title = ingName;
                pill.textContent = ingName;
                if (moreSpan) pillRow.insertBefore(pill, moreSpan);
                else          pillRow.appendChild(pill);
            } else if (moreSpan) {
                const moreNum = parseInt(moreSpan.textContent.replace('+', ''), 10) || 0;
                moreSpan.textContent = '+' + (moreNum + 1) + ' more';
            } else {
                const pill = document.createElement('span');
                pill.className = 'pill-more';
                pill.textContent = '+1 more';
                pillRow.appendChild(pill);
            }
        }

        /* Hide empty notice */
        const emptyNotice = getEl('emptyNotice_' + itemId);
        if (emptyNotice) emptyNotice.style.display = 'none';
    }

    return {
        openDetailModal, openAddModal, openAddFromDetail, openCopyModal,
        populateVariants, updateIngredientUnit, injectIngredientRow, injectRecipeModals
    };

}());

/* ─────────────────────────────────────────────────────────────
   Accordion toggle
───────────────────────────────────────────────────────────── */
function toggleAcc(id) {
    const body = document.getElementById(id);
    const wrap = document.getElementById(id + '_wrap');
    if (!body || !wrap) return;
    const isOpen = wrap.classList.contains('is-open');
    const list = wrap.closest('.accordion-variant-list');
    if (list) {
        list.querySelectorAll('.acc-section.is-open').forEach(function (s) {
            s.classList.remove('is-open');
        });
    }
    if (!isOpen) wrap.classList.add('is-open');
}

/* ─────────────────────────────────────────────────────────────
   DOMContentLoaded
───────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {

    /* ── Sidebar toggle ── */
    const menuToggle     = document.getElementById('menuToggle');
    const sidebar        = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar.classList.add('show');
        if (sidebarOverlay) sidebarOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('show');
        if (sidebarOverlay) sidebarOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }
    menuToggle?.addEventListener('click', function () {
        sidebar.classList.contains('show') ? closeSidebar() : openSidebar();
    });
    sidebarOverlay?.addEventListener('click', closeSidebar);

    /* ── Global "Add Ingredient" button ── */
    document.querySelectorAll('[data-open-add-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () { recipePage.openAddModal(null, null); });
    });

    /* ── "Add Ingredient" from inside detail modals ── */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-open-add-from-detail]');
        if (btn) recipePage.openAddFromDetail(btn);
    });

    /* ── "Copy Base → Variant" from inside detail modals ── */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-open-copy-modal]');
        if (btn) recipePage.openCopyModal(btn);
    });

    /* ── Ingredient → auto-fill unit ── */
    document.getElementById('add_ingredient_id')?.addEventListener('change', function () {
        recipePage.updateIngredientUnit(this.value || null);
    });

    /* ── Item dropdown → repopulate variant picker ── */
    document.getElementById('add_item_id')?.addEventListener('change', function () {
        recipePage.populateVariants(this.value || null, null);
    });

    /* ── Search filter ── */
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.recipe-card').forEach(function (card) {
            card.style.display = (!q || card.dataset.name.includes(q)) ? '' : 'none';
        });
    });

    /* ── Alert dismiss ── */
    document.querySelectorAll('.alert-bar .dismiss').forEach(function (btn) {
        btn.addEventListener('click', function () { btn.closest('.alert-bar').remove(); });
    });
    setTimeout(function () {
        document.querySelectorAll('.alert-bar').forEach(function (a) { a.style.opacity = '0'; });
        setTimeout(function () { document.querySelectorAll('.alert-bar').forEach(function (a) { a.remove(); }); }, 400);
    }, 6000);

    /* ── Card animation stagger ── */
    document.querySelectorAll('#recipesGrid .recipe-card').forEach(function (card, i) {
        card.style.animationDelay = (i * 0.05) + 's';
    });

    /* ══════════════════════════════════════════════════════════
       AJAX SUBMIT — Add Ingredient Form
    ══════════════════════════════════════════════════════════ */
    const addForm   = document.getElementById('addRecipeForm');
    const feedback  = document.getElementById('addRecipeFeedback');
    const submitBtn = document.getElementById('addRecipeSubmitBtn');

    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = String(str ?? '');
        return d.innerHTML;
    }

    function showToast(msg) {
        const toast = document.getElementById('recipeToast');
        const msgEl = document.getElementById('recipeToastMsg');
        if (!toast || !msgEl) return;
        msgEl.textContent = msg;
        toast.classList.add('show');
        clearTimeout(toast._hideTimer);
        toast._hideTimer = setTimeout(function () { toast.classList.remove('show'); }, 2200);
    }

    if (addForm) {
        addForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const itemId    = document.getElementById('add_item_id')?.value;
            const variantEl = document.getElementById('variant_id_select');
            const variantId = (variantEl && !variantEl.disabled) ? variantEl.value : '';
            const ingEl     = document.getElementById('add_ingredient_id');
            const ingId     = ingEl?.value;
            const ingName   = ingEl?.options[ingEl.selectedIndex]?.text || '';
            const qty       = document.getElementById('add_quantity')?.value;
            const unitEl    = document.getElementById('ingredient_unit_display');
            const unit      = (unitEl && unitEl.textContent !== '—') ? unitEl.textContent.trim() : '';

            if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving…'; }
            if (feedback)  { feedback.style.display = 'none'; feedback.innerHTML = ''; }

            let data;
            try {
                const resp = await fetch(addForm.action, { method: 'POST', body: new FormData(addForm) });
                if (!resp.ok) {
                    const text = await resp.text();
                    console.error('[Recipe AJAX] HTTP', resp.status, text);
                    data = { ok: false, error: 'Server error (' + resp.status + '). Check console for details.' };
                } else {
                    const text = await resp.text();
                    try {
                        const jsonStart = text.indexOf('{');
                        data = JSON.parse(jsonStart !== -1 ? text.slice(jsonStart) : text);
                    } catch {
                        console.error('[Recipe AJAX] Non-JSON:', text);
                        data = { ok: false, error: 'Unexpected server response.' };
                    }
                }
            } catch (err) {
                console.error('[Recipe AJAX] Fetch failed:', err);
                data = { ok: false, error: 'Could not reach the server.' };
            }

            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-plus me-1"></i> Add to Recipe'; }

            if (data.ok) {
                /* Reset ingredient + qty; keep item + scope */
                if (ingEl)  ingEl.value  = '';
                const qtyIn = document.getElementById('add_quantity');
                if (qtyIn)  qtyIn.value = '';
                if (unitEl) unitEl.textContent = '—';
                if (feedback) { feedback.style.display = 'none'; feedback.innerHTML = ''; }

                if (itemId && ingName) {
                    recipePage.injectIngredientRow(itemId, variantId, ingName, qty, unit, data.recipe_id || null);
                }

                const addModalEl   = document.getElementById('addRecipeModal');
                const addModalInst = addModalEl ? bootstrap.Modal.getInstance(addModalEl) : null;

                if (itemId && addModalInst) {
                    addModalEl.addEventListener('hidden.bs.modal', function h() {
                        addModalEl.removeEventListener('hidden.bs.modal', h);
                        recipePage.openDetailModal(itemId);
                        showToast(data.message || 'Ingredient added successfully.');
                    });
                    addModalInst.hide();
                } else if (addModalInst) {
                    addModalInst.hide();
                    showToast(data.message || 'Ingredient added successfully.');
                }

            } else {
                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.innerHTML = `
                        <div style="display:flex;align-items:center;gap:.55rem;padding:.75rem 1rem;
                                    background:rgba(214,64,69,.07);border-left:4px solid var(--danger);
                                    border-radius:10px;font-size:.875rem;color:#9e3a34;">
                            <i class="fas fa-exclamation-circle" style="color:var(--danger);font-size:1rem;flex-shrink:0;"></i>
                            <span>${escHtml(data.error || 'Something went wrong.')}</span>
                        </div>`;
                }
            }
        });
    }

    /* ── Bootstrap modal backdrop cleanup ── */
    document.addEventListener('hidden.bs.modal', function () {
        const openModals = document.querySelectorAll('.modal.show');
        if (openModals.length === 0) {
            document.querySelectorAll('.modal-backdrop').forEach(function (bd) { bd.remove(); });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }
    });

});