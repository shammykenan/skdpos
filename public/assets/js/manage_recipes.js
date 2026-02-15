/**
 * manage_recipes.js
 * Handles: mobile sidebar, search, alert auto-dismiss, modal cleanup
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ── MOBILE SIDEBAR TOGGLE ── */
    const menuToggle     = document.getElementById('menuToggle');
    const sidebar        = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (menuToggle) {
        menuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    /* ── SEARCH – filter recipe cards by menu item name ── */
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            document.querySelectorAll('#recipesGrid .recipe-card').forEach(function (card) {
                const name = (card.dataset.name || '').toLowerCase();
                card.style.display = name.includes(term) ? '' : 'none';
            });
        });
    }

    /* ── AUTO-DISMISS ALERTS after 5 s ── */
    setTimeout(function () {
        document.querySelectorAll('.alert').forEach(function (alertEl) {
            try {
                var bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                bsAlert.close();
            } catch (e) { /* ignore */ }
        });
    }, 5000);

    /* ── MODAL CLEANUP – remove lingering backdrop on close ── */
    document.addEventListener('hidden.bs.modal', function () {
        // Remove any orphaned backdrops
        document.querySelectorAll('.modal-backdrop').forEach(function (el) {
            el.remove();
        });
        document.body.classList.remove('modal-open');
        document.body.style.overflow     = '';
        document.body.style.paddingRight = '';
    });

    /* ── STAGGER CARD ANIMATIONS ── */
    document.querySelectorAll('#recipesGrid .recipe-card').forEach(function (card, idx) {
        card.style.animationDelay = (idx * 0.06) + 's';
    });

});