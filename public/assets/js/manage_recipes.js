document.addEventListener('DOMContentLoaded', function () {

    /* ══════════════════════════════════════════
       MOBILE SIDEBAR
    ══════════════════════════════════════════ */
    setupSidebarToggle();

    /* ══════════════════════════════════════════
       SEARCH  — filter recipe cards by name
    ══════════════════════════════════════════ */
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('#recipesGrid .recipe-card').forEach(function (card) {
            card.style.display = (card.dataset.name || '').includes(term) ? '' : 'none';
        });
    });

    /* ══════════════════════════════════════════
       AUTO-DISMISS ALERTS after 5 s
    ══════════════════════════════════════════ */
    autoDismissAlerts(5000);

    /* ══════════════════════════════════════════
       CARD ANIMATION STAGGER
    ══════════════════════════════════════════ */
    document.querySelectorAll('#recipesGrid .recipe-card').forEach(function (card, i) {
        card.style.animationDelay = (i * 0.06) + 's';
    });

    /* ══════════════════════════════════════════
       MODAL BACKDROP CLEANUP
       Prevents ghost backdrop after modal close
    ══════════════════════════════════════════ */
    setupModalBackdropCleanup();

});


/* ══════════════════════════════════════════
   UTILITY — setupSidebarToggle
   (keep here if not already in a global util file)
══════════════════════════════════════════ */
function setupSidebarToggle() {
    const toggle  = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!toggle || !sidebar) return;

    function openSidebar() {
        sidebar.classList.add('show');
        if (overlay) overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('show');
        if (overlay) overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function () {
        sidebar.classList.contains('show') ? closeSidebar() : openSidebar();
    });
    overlay?.addEventListener('click', closeSidebar);
}


/* ══════════════════════════════════════════
   UTILITY — autoDismissAlerts
══════════════════════════════════════════ */
function autoDismissAlerts(delay) {
    setTimeout(function () {
        document.querySelectorAll('.alert.alert-dismissible').forEach(function (alert) {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        });
    }, delay);
}


/* ══════════════════════════════════════════
   UTILITY — setupModalBackdropCleanup
   Ensures the Bootstrap backdrop is removed
   when any modal finishes hiding.
══════════════════════════════════════════ */
function setupModalBackdropCleanup() {
    document.addEventListener('hidden.bs.modal', function () {
        // If no other modals are open, clean up stale backdrops
        const openModals = document.querySelectorAll('.modal.show');
        if (openModals.length === 0) {
            document.querySelectorAll('.modal-backdrop').forEach(function (bd) { bd.remove(); });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }
    });
}