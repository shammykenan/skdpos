/**
 * manage_orders_ui.js
 * UI-only enhancements for the redesigned manage_orders page.
 * Does NOT replace or modify any backend logic from manage_orders.js.
 * Handles: sidebar, alert auto-dismiss, stat card sync, badge updates.
 */

/* ── Sidebar toggle ──────────────────────────────────────────────── */
document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
});

/* ── Alert auto-dismiss ──────────────────────────────────────────── */
document.querySelectorAll('.alert-bar .dismiss').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.alert-bar').remove());
});
setTimeout(() => {
    document.querySelectorAll('.alert-bar').forEach(a => { a.style.opacity = '0'; });
    setTimeout(() => document.querySelectorAll('.alert-bar').forEach(a => a.remove()), 400);
}, 6000);

/* ── Stat card sync ──────────────────────────────────────────────── */
/**
 * Intercepts updateFilterCounts() calls from manage_orders.js to also
 * update the hero stat cards at the top of the page.
 */
const _origUpdateFilterCounts = window.updateFilterCounts;
if (typeof _origUpdateFilterCounts === 'function') {
    // wrap after DOM ready (manage_orders.js defines it at module level)
    window.updateFilterCounts = function(counts, total) {
        _origUpdateFilterCounts(counts, total);
        syncStatCards(counts, total);
    };
} else {
    // Fallback: poll until manage_orders.js has loaded
    const _waitForFn = setInterval(() => {
        if (typeof window.updateFilterCounts === 'function') {
            clearInterval(_waitForFn);
            const orig = window.updateFilterCounts;
            window.updateFilterCounts = function(counts, total) {
                orig(counts, total);
                syncStatCards(counts, total);
            };
        }
    }, 50);
}

function syncStatCards(counts, total) {
    const statAll      = document.getElementById('statAll');
    const statPending  = document.getElementById('statPending');
    const statPreparing= document.getElementById('statPreparing');
    const statReady    = document.getElementById('statReady');
    if (statAll)       statAll.textContent      = total ?? 0;
    if (statPending)   statPending.textContent  = counts?.pending   ?? 0;
    if (statPreparing) statPreparing.textContent= counts?.preparing ?? 0;
    if (statReady)     statReady.textContent    = counts?.ready     ?? 0;
}

/* ── Orders count badge ───────────────────────────────────────────── */
/**
 * Updates the "X orders" badge in the table card header
 * whenever filteredTotal changes.
 */
const _origRenderPagination = window.renderPagination;
if (typeof _origRenderPagination !== 'function') {
    const _waitBadge = setInterval(() => {
        if (typeof window.renderPagination === 'function') {
            clearInterval(_waitBadge);
            wrapRenderPagination();
        }
    }, 50);
} else {
    wrapRenderPagination();
}

function wrapRenderPagination() {
    const orig = window.renderPagination;
    window.renderPagination = function() {
        orig();
        updateOrderCountBadge();
    };
}

function updateOrderCountBadge() {
    const badge = document.getElementById('orderCountBadge');
    if (!badge) return;
    const total = window.filteredTotal ?? 0;
    badge.textContent = `${total} order${total !== 1 ? 's' : ''}`;
}

/* ── Bootstrap modal cleanup ─────────────────────────────────────── */
document.addEventListener('hidden.bs.modal', function () {
    if (document.querySelector('.modal.show')) return;
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});

/* ── Auto-show flash modals ──────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    ['successModal', 'errorModal'].forEach(id => {
        const el = document.getElementById(id);
        if (el && typeof bootstrap !== 'undefined') {
            const m = new bootstrap.Modal(el);
            m.show();
            setTimeout(() => m.hide(), 3000);
        }
    });
});