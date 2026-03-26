document.addEventListener('DOMContentLoaded', function () {

    /* ── MOBILE SIDEBAR ── */
    const menuToggle     = document.getElementById('menuToggle');
    const sidebar        = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    menuToggle?.addEventListener('click', function () {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
    });
    sidebarOverlay?.addEventListener('click', function () {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    });

    /* ── SEARCH ── */
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('#recipesGrid .recipe-card').forEach(function (card) {
            card.style.display = (card.dataset.name || '').includes(term) ? '' : 'none';
        });
    });

    /* ── AUTO-DISMISS ALERTS after 5s ── */
    setTimeout(function () {
        document.querySelectorAll('.alert').forEach(function (el) {
            try { bootstrap.Alert.getOrCreateInstance(el).close(); } catch (e) {}
        });
    }, 5000);

    /* ── CARD ANIMATION STAGGER ── */
    document.querySelectorAll('#recipesGrid .recipe-card').forEach(function (card, i) {
        card.style.animationDelay = (i * 0.06) + 's';
    });

    /* ── MODAL BACKDROP CLEANUP ── */
    document.addEventListener('hidden.bs.modal', function () {
        if (document.querySelector('.modal.show')) return;
        document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.overflow     = '';
        document.body.style.paddingRight = '';
    });

    /* ══════════════════════════════════════════
       VARIANT POPULATE HELPER
    ══════════════════════════════════════════ */
    function populateVariants(itemId) {
    const variantSel = document.getElementById('variant_id_select');
    const formText   = variantSel?.closest('.mb-3')?.querySelector('.form-text');
    if (!variantSel) return;

    const variants = (window._recipeVariants || {})[String(itemId)] || [];

    if (variants.length > 0) {
        // Item HAS variants — only show variant options, no base
        variantSel.innerHTML = '';
        variants.forEach(function (v) {
            const opt = document.createElement('option');
            opt.value       = v.variant_id;
            opt.textContent = 'Variant: ' + v.variant_name;
            variantSel.appendChild(opt);
        });
        variantSel.disabled = false;
        if (formText) formText.textContent = 'This item uses variant pricing — select a variant.';
    } else {
        // Item has NO variants — base only, lock it
        variantSel.innerHTML = '<option value="">Base Recipe (Standard — no variant)</option>';
        variantSel.disabled = true;
        if (formText) formText.textContent = 'This item has no variants — ingredients apply to the base recipe.';
    }
}

    /* ══════════════════════════════════════════
       OPEN ADD MODAL
    ══════════════════════════════════════════ */
    function openAddModal(itemId) {
        const el = document.getElementById('addRecipeModal');
        if (!el) return;

        const itemSelect = document.getElementById('add_item_id');
        const qtyInput   = document.getElementById('add_quantity');
        const ingSelect  = document.getElementById('add_ingredient_id');

        if (itemSelect) itemSelect.value = itemId ? String(itemId) : '';
        populateVariants(itemId || '');
        if (qtyInput)  qtyInput.value  = '';
        if (ingSelect) ingSelect.value = '';

        bootstrap.Modal.getOrCreateInstance(el).show();
    }

    /* ══════════════════════════════════════════
       RECIPE CARD CLICK → open detail modal
    ══════════════════════════════════════════ */
    document.getElementById('recipesGrid')?.addEventListener('click', function (e) {
        const card = e.target.closest('[data-modal-target]');
        if (!card) return;
        const el = document.getElementById(card.dataset.modalTarget);
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });

    document.getElementById('recipesGrid')?.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        const card = e.target.closest('[data-modal-target]');
        if (!card) return;
        const el = document.getElementById(card.dataset.modalTarget);
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });

    /* ══════════════════════════════════════════
       TOP-LEVEL "Add Recipe" BUTTON
    ══════════════════════════════════════════ */
    document.querySelector('[data-open-add-modal]')?.addEventListener('click', function () {
        openAddModal(null);
    });

    /* ══════════════════════════════════════════
       "Add Ingredient" BUTTON INSIDE DETAIL MODAL
    ══════════════════════════════════════════ */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-open-add-from-detail]');
        if (!btn) return;

        const itemId      = btn.dataset.itemId;
        const parentModal = btn.closest('.modal');

        if (parentModal) {
            parentModal.addEventListener('hidden.bs.modal', function handler() {
                parentModal.removeEventListener('hidden.bs.modal', handler);
                openAddModal(itemId);
            });
            bootstrap.Modal.getInstance(parentModal)?.hide();
        } else {
            openAddModal(itemId);
        }
    });

    /* ══════════════════════════════════════════
       VARIANT DROPDOWN — item change in Add modal
    ══════════════════════════════════════════ */
    document.getElementById('add_item_id')?.addEventListener('change', function () {
        populateVariants(this.value);
    });

});