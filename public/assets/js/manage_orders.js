/**
 * ============================================
 * ORDERS MANAGEMENT — Item-level tracking
 * ============================================
 */

const REFRESH_INTERVAL     = 2000;
const PAGE_LIMIT           = 10;
const BASE_URL             = '/dinewithdinpos'; // Set by server-side config; adjust if your setup differs
const AJAX_URL             = `${BASE_URL}/app/ajax/get_orders.php`;
const ITEM_STATUS_URL      = `${BASE_URL}/app/ajax/update_order_item_status.php`;
const ORDER_DETAILS_URL    = `${BASE_URL}/app/ajax/get_order_details.php`;
const ADMIN_USERS_URL      = `${BASE_URL}/app/ajax/get_admin_users.php`;
const ORDER_ITEMS_URL      = `${BASE_URL}/app/ajax/get_order_items.php`;
const CANCEL_VOID_ITEM_URL = `${BASE_URL}/app/ajax/cancel_items.php`;
const VOID_ORDER_URL       = `${BASE_URL}/app/ajax/void_order.php`;
const GET_REASONS_URL      = `${BASE_URL}/app/ajax/get_reasons.php`;

let currentFilter      = 'all';
let currentSearch      = '';
let currentDateFrom    = '';
let currentDateTo      = '';
let currentCashierView = 'all';
let currentProcessedBy = 0;
let currentPage        = 1;
let totalPages         = 1;
let filteredTotal      = 0;
let isLoading          = false;
let isRefreshing       = false;
let refreshTimer       = null;
let currentOrderTotal  = 0;
let currentOrdersCache = {};
let openDetailsOrderId = null;
let kitchenModalOrderId = null;

let paymentModal, cancelOrderModal, orderDetailsModal, kitchenItemsModal,
    voidOrderModal, cancelVoidItemModal;

// Pending cancel/void item action (set before modal confirms)
let pendingItemAction = { orderItemId: null, action: null, itemName: null, btn: null };

// ============================================
// REASON DROPDOWN HELPERS
// ============================================

/**
 * Fetch reasons from the DB for a given type and populate a <select>.
 * Adds an "Other" option at the end that reveals a free-text textarea.
 * Returns a promise that resolves when done.
 */
async function loadReasons(type, selectId) {
    const sel = document.getElementById(selectId);
    if (!sel) return;

    sel.innerHTML = '<option value="">— Loading… —</option>';
    try {
        const res  = await fetch(`${GET_REASONS_URL}?type=${encodeURIComponent(type)}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to load reasons');

        sel.innerHTML = '<option value="">— Select a reason —</option>';
        data.data.forEach(r => {
            const opt       = document.createElement('option');
            opt.value       = r.reason_text;
            opt.textContent = r.reason_text;
            sel.appendChild(opt);
        });
    } catch (err) {
        console.error('loadReasons error:', err);
        sel.innerHTML = '<option value="">— Could not load reasons —</option>';
    }
}

/**
 * Wire a reason <select> + optional free-text <textarea>.
 *
 * - When "Other" is selected the textarea slides into view.
 * - getReason() returns the selected value, or the textarea content when "Other".
 * - reset()     clears both controls and hides the textarea.
 * - showError() highlights the select and shows the error <div>.
 *
 * NOTE: This function is defined ONCE here. Do not redefine it below.
 */
function wireReasonDropdown(selectId, textareaId, errorId) {
    const sel = document.getElementById(selectId);
    const ta  = document.getElementById(textareaId);
    const err = document.getElementById(errorId);

    if (!sel) {
        return {
            getReason: () => '',
            reset:     () => {},
            showError: () => {},
        };
    }

    sel.addEventListener('change', function () {
        const isOther = this.value === 'Other';
        if (ta)  ta.style.display    = isOther ? '' : 'none';
        if (err) err.style.display   = 'none';
        sel.style.borderColor        = '';
    });

    return {
        getReason() {
            const selected = sel.value;
            if (!selected) return '';
            if (selected === 'Other') {
                return ta ? ta.value.trim() : '';
            }
            return selected;
        },
        reset() {
            sel.value          = '';
            sel.style.borderColor = '';
            if (ta)  { ta.value = ''; ta.style.display = 'none'; ta.style.borderColor = ''; }
            if (err) err.style.display = 'none';
        },
        showError() {
            if (err) err.style.display = 'block';
            sel.style.borderColor      = 'var(--danger)';
            sel.focus();
        },
    };
}

// ============================================
// SAFE JSON PARSE
// ============================================
async function safeJSONParse(response) {
    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    const text = await response.text();
    try { return JSON.parse(text); }
    catch (e) {
        console.error('Invalid JSON response:', text);
        throw new Error('Invalid JSON from server');
    }
}

// ============================================
// ERROR MODAL
// ============================================
function showErrorModal(title, message) {
    const existing = document.getElementById('dynamicErrorModal');
    if (existing) existing.remove();

    const modalHtml = `
        <div class="modal fade" id="dynamicErrorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle me-2"></i>${escapeHtml(title)}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">${escapeHtml(message)}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>`;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const el = document.getElementById('dynamicErrorModal');
    if (el && typeof bootstrap !== 'undefined') {
        const m = new bootstrap.Modal(el);
        m.show();
        el.addEventListener('hidden.bs.modal', () => el.remove());
    }
}

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function () {

    // ── Bootstrap modal instances ─────────────────────────────────────────────
    try {
        if (typeof bootstrap !== 'undefined') {
            paymentModal        = new bootstrap.Modal(document.getElementById('paymentModal'));
            cancelOrderModal    = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
            orderDetailsModal   = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            kitchenItemsModal   = new bootstrap.Modal(document.getElementById('kitchenItemsModal'));
            voidOrderModal      = new bootstrap.Modal(document.getElementById('voidOrderModal'));
            cancelVoidItemModal = new bootstrap.Modal(document.getElementById('cancelVoidItemModal'));
        }
    } catch (e) { console.error('Modal init error:', e); }

    // ── Wire reason dropdowns (defined once above, called once here) ──────────
    window._cancelOrderReason    = wireReasonDropdown('cancelOrderReasonSelect',    'cancelOrderReasonText',    'cancelOrderReasonError');
    window._voidOrderReason      = wireReasonDropdown('voidOrderReasonSelect',      'voidOrderReasonText',      'voidOrderReasonError');
    window._cancelVoidItemReason = wireReasonDropdown('cancelVoidItemReasonSelect', 'cancelVoidItemReasonText', 'cancelVoidItemReasonError');

    // ── Modal cleanup hooks ───────────────────────────────────────────────────
    document.getElementById('orderDetailsModal')?.addEventListener('hidden.bs.modal', () => {
        openDetailsOrderId = null;
    });

    document.getElementById('kitchenItemsModal')?.addEventListener('hidden.bs.modal', function () {
        kitchenModalOrderId = null;
        const focused = document.activeElement;
        if (focused && this.contains(focused)) focused.blur();
    });

    document.getElementById('kitchenItemsModal')?.addEventListener('show.bs.modal', function () {
        this.removeAttribute('aria-hidden');
    });

    // ── Cancel order: reset dropdown when modal opens ─────────────────────────
    document.getElementById('cancelOrderModal')?.addEventListener('show.bs.modal', function () {
        window._cancelOrderReason?.reset();
    });

    // ── Void order: reset dropdown when modal opens ───────────────────────────
    document.getElementById('voidOrderModal')?.addEventListener('show.bs.modal', function () {
        window._voidOrderReason?.reset();
    });

    // ── Cancel item/void item: reset when modal opens ─────────────────────────
    document.getElementById('cancelVoidItemModal')?.addEventListener('show.bs.modal', function () {
        window._cancelVoidItemReason?.reset();
    });

    // ── Cancel Order submit ───────────────────────────────────────────────────
    document.getElementById('cancelOrderSubmitBtn')?.addEventListener('click', function () {
        const reason = window._cancelOrderReason?.getReason() || '';
        if (!reason) {
            window._cancelOrderReason?.showError();
            return;
        }
        document.getElementById('cancelOrderReasonHidden').value = reason;
        document.getElementById('cancelOrderForm')?.submit();
    });

    // ── Void Order confirm ────────────────────────────────────────────────────
    document.getElementById('confirmVoidOrderBtn')?.addEventListener('click', async function () {
        const reason = window._voidOrderReason?.getReason() || '';
        if (!reason) {
            window._voidOrderReason?.showError();
            return;
        }
        const orderId     = this.getAttribute('data-order-id');
        const orderNumber = this.getAttribute('data-order-number');

        voidOrderModal?.hide();
        await executeVoidOrder(orderId, orderNumber, reason, this);
    });

    // ── Cancel/Void Item confirm (wired ONCE) ─────────────────────────────────
    document.getElementById('confirmCancelVoidItemBtn')?.addEventListener('click', async function () {
        const reason = window._cancelVoidItemReason?.getReason() || '';
        if (!reason) {
            window._cancelVoidItemReason?.showError();
            return;
        }
        cancelVoidItemModal?.hide();
        if (pendingItemAction.orderItemId) {
            await executeCancelVoidItem(
                pendingItemAction.orderItemId,
                pendingItemAction.action,
                pendingItemAction.btn,
                reason
            );
            pendingItemAction = { orderItemId: null, action: null, itemName: null, btn: null };
        }
    });

    setupEventListeners();
    loadPage(1);
    startAutoRefresh();

    if (window.userRole === 'admin') loadAdminUsers();

    // Auto-show flash modals
    ['successModal', 'errorModal'].forEach(id => {
        const el = document.getElementById(id);
        if (el && typeof bootstrap !== 'undefined') {
            const m = new bootstrap.Modal(el);
            m.show();
            setTimeout(() => m.hide(), 3000);
        }
    });
});

// ============================================
// LOAD ADMIN USERS
// ============================================
async function loadAdminUsers() {
    try {
        const res  = await fetch(ADMIN_USERS_URL, { headers: { 'Cache-Control': 'no-cache' } });
        const data = await res.json();
        if (!data.success || !data.data.length) return;
        const select = document.getElementById('processedByFilter');
        if (!select) return;
        data.data.forEach(admin => {
            const opt       = document.createElement('option');
            opt.value       = admin.user_id;
            opt.textContent = admin.full_name;
            select.appendChild(opt);
        });
    } catch (err) { console.error('Failed to load admin users:', err); }
}

// ============================================
// SHOW / HIDE PROCESSED-BY FILTER
// ============================================
function syncProcessedByVisibility() {
    const wrapper = document.getElementById('processedByWrapper');
    if (!wrapper) return;
    if (currentFilter === 'completed' && window.userRole === 'admin') {
        wrapper.style.display = 'flex';
    } else {
        wrapper.style.display  = 'none';
        currentProcessedBy     = 0;
        const select = document.getElementById('processedByFilter');
        if (select) select.value = '0';
    }
}

// ============================================
// LOAD PAGE
// ============================================
async function loadPage(page) {
    if (isLoading) return;
    isLoading = true;
    showTableSpinner(true);
    try {
        const offset = (page - 1) * PAGE_LIMIT;
        const res    = await fetch(buildURL(offset, PAGE_LIMIT), { headers: { 'Cache-Control': 'no-cache' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Unknown error');
        currentPage   = page;
        filteredTotal = data.filtered_total || 0;
        totalPages    = Math.max(1, Math.ceil(filteredTotal / PAGE_LIMIT));
        renderTableRows(data.data || []);
        updateFilterCounts(data.counts, data.total);
        renderPagination();
        updateFilterLabel();
    } catch (err) {
        console.error('Load error:', err);
        showError();
    } finally {
        showTableSpinner(false);
        isLoading = false;
    }
}

// ============================================
// BACKGROUND REFRESH
// ============================================
async function refreshOrders() {
    if (isRefreshing || isLoading) return;
    isRefreshing = true;
    try {
        const offset = (currentPage - 1) * PAGE_LIMIT;
        const res    = await fetch(buildURL(offset, PAGE_LIMIT), { headers: { 'Cache-Control': 'no-cache' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Unknown error');
        updateFilterCounts(data.counts, data.total);
        const newTotal      = data.filtered_total || 0;
        const freshData     = data.data || [];
        const tbody         = document.getElementById('ordersTableBody');
        const existingCount = tbody ? tbody.querySelectorAll('tr[data-order-id]').length : 0;
        if (newTotal !== filteredTotal || existingCount !== freshData.length) {
            filteredTotal = newTotal;
            totalPages    = Math.max(1, Math.ceil(filteredTotal / PAGE_LIMIT));
            renderTableRows(freshData);
            renderPagination();
        } else {
            patchTableRows(freshData);
        }
        if (openDetailsOrderId)  refreshOrderDetailsModal(openDetailsOrderId);
        if (kitchenModalOrderId) refreshKitchenModal(kitchenModalOrderId);
    } catch (err) {
        console.error('Refresh error:', err);
    } finally {
        isRefreshing = false;
    }
}

function startAutoRefresh() { stopAutoRefresh(); refreshTimer = setInterval(refreshOrders, REFRESH_INTERVAL); }
function stopAutoRefresh()  { if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; } }

// ============================================
// URL BUILDER
// ============================================
function buildURL(offset, limit) {
    const params = new URLSearchParams({
        limit:  limit  ?? PAGE_LIMIT,
        offset: offset ?? 0,
        status: currentFilter,
        search: currentSearch,
    });
    if (currentDateFrom)               params.set('date_from',    currentDateFrom);
    if (currentDateTo)                 params.set('date_to',      currentDateTo);
    if (currentCashierView === 'mine') params.set('cashier_view', 'mine');
    if (currentFilter === 'completed' && currentProcessedBy > 0)
        params.set('processed_by', currentProcessedBy);
    return `${AJAX_URL}?${params}`;
}

// ============================================
// TABLE RENDERING
// ============================================
function renderTableRows(orders) {
    const tbody    = document.getElementById('ordersTableBody');
    const table    = document.querySelector('.orders-table');
    const noResult = document.getElementById('noResultsMessage');
    if (!tbody) return;
    if (!orders || orders.length === 0) {
        tbody.innerHTML = '';
        if (table)    table.style.display = 'none';
        if (noResult) noResult.classList.remove('d-none');
        return;
    }
    if (table)    table.style.display = '';
    if (noResult) noResult.classList.add('d-none');
    orders.forEach(o => { currentOrdersCache[String(o.order_id)] = o; });
    tbody.innerHTML = orders.map(order => createOrderRowHTML(order)).join('');
}

// FIX 4: patchTableRows — always sync price cells on every refresh pass,
// not only when status changes. Ensures subtotal/total update in real-time
// after a void zeroes the order total.
function patchTableRows(orders) {
    const tbody = document.getElementById('ordersTableBody');
    if (!tbody) return;
    orders.forEach(order => {
        const row = tbody.querySelector(`tr[data-order-id="${order.order_id}"]`);
        if (!row) return;

        const currentStatus = row.getAttribute('data-status');
        const newStatus     = (order.status || 'pending').toLowerCase();

        // Always sync price cells on every refresh pass — not only on status change.
        // Ensures subtotal/total update immediately after void zeroes the order total.
        const newTotal      = parseFloat(order.total_amount || 0);
        const newSubtotalEx = newTotal / 1.12;
        const newVat        = newTotal - newSubtotalEx;
        const subtotalCell  = row.querySelector('td[data-label="Subtotal"]');
        if (subtotalCell) {
            subtotalCell.innerHTML = `
                <span class="price-subtotal">₱${formatPrice(newSubtotalEx)}</span>
                <div class="price-vat">+₱${formatPrice(newVat)} VAT (12%)</div>`;
        }

        if (currentStatus !== newStatus) {
            row.setAttribute('data-status', newStatus);
            row.setAttribute('data-order-total', order.total_amount || 0);
            const statusCell  = row.querySelector('td[data-label="Status"]');
            const actionsCell = row.querySelector('td[data-label="Actions"]');
            const totalSpan   = row.querySelector('td[data-label="Total"] span');
            if (statusCell)  statusCell.innerHTML  = getOrderStatusBadge(newStatus);
            if (actionsCell) actionsCell.innerHTML = getOrderActionsHTML(order);
            if (totalSpan) {
                totalSpan.className   = newStatus === 'completed' ? 'price-total-paid' : 'price-total-unpaid';
                totalSpan.textContent = '₱' + formatPrice(newTotal);
            }
        }

        if (order.items) {
            const itemsCell = row.querySelector('.items-cell');
            if (itemsCell) {
                const tally   = { pending: 0, preparing: 0, ready: 0 };
                order.items.forEach(i => { const s = i.item_status || 'pending'; if (tally[s] !== undefined) tally[s]++; });
                const dotsCfg = [
                    { key: 'pending',   color: '#f59e0b', icon: 'fa-hourglass-half' },
                    { key: 'preparing', color: '#3b82f6', icon: 'fa-bowl-food'      },
                    { key: 'ready',     color: '#10b981', icon: 'fa-circle-check'   },
                ];
                const summaryRow = itemsCell.querySelector('.items-summary-row');
                if (summaryRow) {
                    const noteCount = order.items.filter(i => i.item_notes && i.item_notes.trim()).length;
                    const hasAddons = order.items.some(i => i.addons && i.addons.length > 0);
                    const notesDot  = noteCount > 0 ? `<span class="items-has-notes-dot">&#9998; ${noteCount} note${noteCount > 1 ? 's' : ''}</span>` : '';
                    const addonsDot = hasAddons ? `<span class="items-has-addons-dot"><i class="fas fa-plus-circle"></i></span>` : '';
                    summaryRow.innerHTML = dotsCfg
                        .filter(d => tally[d.key] > 0)
                        .map(d => `<span class="item-tally-dot" style="background:${d.color}18;color:${d.color};border:1px solid ${d.color}44;"><i class="fas ${d.icon}" style="font-size:0.6rem;"></i> ${tally[d.key]}</span>`)
                        .join('') + notesDot + addonsDot;
                }
            }
        }
        currentOrdersCache[String(order.order_id)] = order;
    });
}

function renderPagination() {
    let container = document.getElementById('paginationContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'paginationContainer';
        container.style.cssText = 'padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;';
        const tc = document.querySelector('.table-container');
        if (tc) tc.after(container);
    }
    if (totalPages <= 1 && filteredTotal <= PAGE_LIMIT) { container.style.display = 'none'; return; }
    container.style.display = 'flex';
    const from = filteredTotal === 0 ? 0 : (currentPage - 1) * PAGE_LIMIT + 1;
    const to   = Math.min(currentPage * PAGE_LIMIT, filteredTotal);
    const info = `<span style="font-size:0.85rem;color:var(--warm-gray);">Showing <strong>${from}–${to}</strong> of <strong>${filteredTotal}</strong> orders</span>`;
    let btns = '';
    btns += pageBtn('<i class="fas fa-chevron-left"></i>', currentPage - 1, currentPage === 1, false);
    getPageRange(currentPage, totalPages).forEach(p => {
        btns += p === '...'
            ? `<span style="padding:0 4px;color:var(--warm-gray);align-self:center;">…</span>`
            : pageBtn(p, p, false, p === currentPage);
    });
    btns += pageBtn('<i class="fas fa-chevron-right"></i>', currentPage + 1, currentPage === totalPages, false);
    container.innerHTML = `${info}<div style="display:flex;align-items:center;gap:0.35rem;">${btns}</div>`;
    container.querySelectorAll('.page-btn:not([disabled])').forEach(btn => {
        btn.addEventListener('click', () => loadPage(parseInt(btn.dataset.page)));
    });
}

function pageBtn(label, page, disabled, active) {
    const base  = 'class="page-btn" style="border:none;border-radius:8px;width:34px;height:34px;font-size:0.82rem;font-weight:600;cursor:pointer;transition:all .15s;';
    const style = active   ? 'background:var(--primary);color:#fff;"'
                : disabled ? 'background:#f0f0f0;color:#bbb;cursor:not-allowed;"'
                           : 'background:var(--light-bg);color:var(--navy);"';
    return `<button ${base}${style} data-page="${page}" ${(disabled || active) ? 'disabled' : ''}>${label}</button>`;
}

function getPageRange(current, total) {
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    if (current <= 4)         return [1, 2, 3, 4, 5, '...', total];
    if (current >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
    return [1, '...', current - 1, current, current + 1, '...', total];
}

// ============================================
// FILTER COUNTS & LABEL
// ============================================
function updateFilterCounts(counts, total) {
    const isAdmin  = window.userRole === 'admin';
    const isCashier = window.userRole === 'cashier';
    const data = {
        all:       total ?? 0,
        pending:   counts?.pending   ?? 0,
        preparing: counts?.preparing ?? 0,
        ready:     counts?.ready     ?? 0,
        completed: counts?.completed ?? 0,
        cancelled: counts?.cancelled ?? 0,
    };
    // Admin sees all voided; cashiers see only their own voided (handled server-side)
    if (isAdmin || isCashier) data.voided = counts?.voided ?? 0;

    Object.keys(data).forEach(status => {
        const span = document.querySelector(`.filter-tab[data-status="${status}"] .filter-count`);
        if (span) span.textContent = data[status];
    });
}

function updateFilterLabel() {
    const map = {
        all:       'All Orders',
        pending:   'Pending Orders',
        preparing: 'Preparing Orders',
        ready:     'Ready Orders',
        completed: 'Completed Orders',
        cancelled: 'Cancelled Orders',
        voided:    'Voided Orders',
    };
    const el = document.getElementById('currentFilterLabel');
    if (el) el.textContent = map[currentFilter] || 'Orders';
}

// ============================================
// SPINNER / ERROR
// ============================================
function showTableSpinner(show) {
    let spinner = document.getElementById('tableSpinner');
    if (show) {
        if (!spinner) {
            spinner = document.createElement('tr');
            spinner.id = 'tableSpinner';
            spinner.innerHTML = `<td colspan="8" class="text-center py-4"><div class="spinner-border spinner-border-sm me-2" style="color:var(--primary);" role="status"></div><span class="text-muted" style="font-size:0.875rem;">Loading orders…</span></td>`;
            document.getElementById('ordersTableBody')?.appendChild(spinner);
        }
    } else {
        spinner?.remove();
    }
}

function showError() {
    const tbody = document.getElementById('ordersTableBody');
    if (tbody) tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger"><i class="fas fa-exclamation-circle me-2"></i>Failed to load orders. Please refresh the page.</td></tr>`;
}

// ============================================
// ROW HTML
// ============================================
function createOrderRowHTML(order) {
    const total      = parseFloat(order.total_amount || 0);
    const subtotalEx = total / 1.12;
    const vat        = total - subtotalEx;
    const searchText = [order.order_number || '', order.customer_name || 'walk-in', order.table_name || ''].join(' ').toLowerCase();
    return `
        <tr data-status="${(order.status || 'pending').toLowerCase()}"
            data-search-text="${escapeHtml(searchText)}"
            data-order-total="${order.total_amount || 0}"
            data-order-id="${order.order_id}"
            data-order-number="${escapeHtml(order.order_number || '')}">
            <td data-label="Order #"><span class="order-number">#${escapeHtml(order.order_number || '')}</span></td>
            <td data-label="Customer">
                <div class="fw-medium" style="color:var(--navy);">${escapeHtml(order.customer_name || 'Walk-in')}</div>
                <small class="text-muted">${formatDate(order.created_at)}</small>
                ${order.table_name ? `<div class="table-number-badge"><i class="fas fa-chair"></i>${escapeHtml(order.table_name)}</div>` : ''}
            </td>
            <td data-label="Order Type">${getOrderTypeBadge(order.order_type)}</td>
            <td data-label="Items">${getOrderItemsHTML(order.items || [], order.status, order.order_id)}</td>
            <td data-label="Subtotal">
                <span class="price-subtotal">₱${formatPrice(subtotalEx)}</span>
                <div class="price-vat">+₱${formatPrice(vat)} VAT (12%)</div>
            </td>
            <td data-label="Total"><span class="${order.status === 'completed' ? 'price-total-paid' : 'price-total-unpaid'}">₱${formatPrice(order.total_amount)}</span></td>
            <td data-label="Status">${getOrderStatusBadge(order.status)}</td>
            <td data-label="Actions">${getOrderActionsHTML(order)}</td>
        </tr>`;
}

function getOrderTypeBadge(orderType) {
    if (!orderType) return '<span class="order-type-missing">—</span>';
    const types = { dine_in: { icon: 'fa-utensils', label: 'Dine In' }, takeout: { icon: 'fa-bag-shopping', label: 'Takeout' } };
    const t = types[orderType] || { icon: 'fa-question', label: orderType };
    return `<span class="order-type-badge ${orderType}"><i class="fas ${t.icon}"></i> ${t.label}</span>`;
}

function getOrderStatusBadge(status) {
    if (!status) status = 'pending';
    return `<span class="status-badge status-${status.toLowerCase()}">${capitalize(status)}</span>`;
}

function getOrderItemsHTML(items, orderStatus, orderId) {
    if (!items || items.length === 0) return '<span class="text-muted fst-italic" style="font-size:0.8rem;">No items</span>';
    const tally   = { pending: 0, preparing: 0, ready: 0 };
    items.forEach(i => { const s = i.item_status || 'pending'; if (tally[s] !== undefined) tally[s]++; });
    const dotsCfg = [
        { key: 'pending',   color: '#f59e0b', icon: 'fa-hourglass-half' },
        { key: 'preparing', color: '#3b82f6', icon: 'fa-bowl-food'      },
        { key: 'ready',     color: '#10b981', icon: 'fa-circle-check'   },
    ];
    const dotsHTML = dotsCfg.filter(d => tally[d.key] > 0)
        .map(d => `<span class="item-tally-dot" style="background:${d.color}18;color:${d.color};border:1px solid ${d.color}44;"><i class="fas ${d.icon}" style="font-size:0.6rem;"></i> ${tally[d.key]}</span>`)
        .join('');
    const namePreview = items.slice(0, 2).map(i => {
        const baseName    = escapeHtml(i.item_name_snapshot || i.item_name || '');
        const variantName = i.variant_name_snapshot || i.current_variant_name || i.variant_name || null;
        const qty         = parseInt(i.quantity) > 1 ? ` ×${i.quantity}` : '';
        const variantBit  = variantName ? `<span style="opacity:0.65;font-weight:400;"> · ${escapeHtml(variantName)}</span>` : '';
        return `<span class="item-name-chip">${baseName}${variantBit}${qty}</span>`;
    }).join('');
    const moreChip  = items.length > 2 ? `<span class="item-name-chip item-name-more">+${items.length - 2} more</span>` : '';
    const noteCount = items.filter(i => i.item_notes && i.item_notes.trim()).length;
    const hasAddons = items.some(i => i.addons && i.addons.length > 0);
    const notesDot  = noteCount > 0 ? `<span class="items-has-notes-dot">&#9998; ${noteCount} note${noteCount > 1 ? 's' : ''}</span>` : '';
    const addonsDot = hasAddons ? `<span class="items-has-addons-dot" title="Has add-ons"><i class="fas fa-plus-circle"></i></span>` : '';
    return `<div class="items-cell" data-order-id="${orderId}">
        <div class="items-summary-row">${dotsHTML}${notesDot}${addonsDot}</div>
        <div class="items-name-row">${namePreview}${moreChip}</div>
        <button type="button" class="items-manage-btn" data-order-id="${orderId}"><i class="fas fa-chevron-right"></i> Manage items</button>
    </div>`;
}

function getOrderActionsHTML(order) {
    const userRole  = window.userRole || '';
    const isAdmin   = userRole === 'admin';
    const isCashier = userRole === 'cashier';
    const status    = order.status || 'pending';
    const orderId   = order.order_id;
    const orderNum  = order.order_number || '';

    let html = `<div class="action-buttons">
        <button type="button" class="btn btn-sm view-order-btn" style="background:var(--lavender);color:var(--navy);"
                data-order-id="${orderId}" data-order-number="${escapeHtml(orderNum)}">
            <i class="fas fa-eye"></i> View
        </button>`;

    if (status === 'ready' && (isAdmin || isCashier)) {
        html += `<button type="button" class="btn btn-success btn-sm collect-payment-btn"
                         data-order-id="${orderId}" data-order-number="${escapeHtml(orderNum)}" data-order-total="${order.total_amount || 0}">
                     <i class="fas fa-cash-register"></i> Collect Payment</button>`;
        if (isAdmin) {
            html += `<button type="button" class="btn btn-sm void-order-btn"
                             style="background:rgba(15,76,92,0.07);color:var(--primary-dark);border:1.5px solid rgba(15,76,92,0.28);border-radius:8px;font-size:0.78rem;font-weight:600;"
                             data-order-id="${orderId}" data-order-number="${escapeHtml(orderNum)}">
                         <i class="fas fa-ban"></i> Void Order</button>`;
        }
    } else if (status === 'pending' && (isAdmin || isCashier)) {
        html += `<button type="button" class="btn btn-danger btn-sm cancel-order-btn"
                         data-order-id="${orderId}" data-order-number="${escapeHtml(orderNum)}">
                     <i class="fas fa-times"></i> Cancel</button>`;
    } else if ((status === 'pending' || status === 'preparing') && isCashier && !isAdmin) {
        html += `<span class="badge action-status-badge" style="font-size:0.75rem;border-radius:20px;display:flex;align-items:center;justify-content:center;padding:0.4rem 0.8rem;width:100%;box-sizing:border-box;background:rgba(94,155,158,0.12);color:var(--info);border:1px solid rgba(94,155,158,0.3);">In Kitchen</span>`;
    } else if (status === 'completed') {
        html += `<span class="badge action-status-badge" style="font-size:0.75rem;border-radius:20px;display:flex;align-items:center;justify-content:center;padding:0.4rem 0.8rem;width:100%;box-sizing:border-box;background:rgba(44,122,90,0.12);color:var(--success);border:1px solid rgba(44,122,90,0.25);">Paid</span>`;
    } else if (status === 'cancelled') {
        html += `<span class="badge action-status-badge" style="font-size:0.75rem;border-radius:20px;display:flex;align-items:center;justify-content:center;padding:0.4rem 0.8rem;width:100%;box-sizing:border-box;background:rgba(214,64,69,0.10);color:var(--danger);border:1px solid rgba(214,64,69,0.25);">Cancelled</span>`;
    } else if (status === 'voided') {
        html += `<span class="badge action-status-badge" style="font-size:0.75rem;border-radius:20px;display:flex;align-items:center;justify-content:center;padding:0.4rem 0.8rem;width:100%;box-sizing:border-box;background:rgba(15,76,92,0.10);color:var(--primary-dark);border:1px solid rgba(15,76,92,0.28);">Voided</span>`;
    }

    html += '</div>';
    return html;
}

// ============================================
// ITEM STATUS UPDATE (kitchen progress)
// ============================================
async function updateItemStatus(orderItemId, nextStatus, btn) {
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Updating…</span>';
    }

    try {
        const formData = new FormData();
        formData.append('order_item_id', orderItemId);
        formData.append('status', nextStatus);

        const res = await fetch(ITEM_STATUS_URL, { method: 'POST', body: formData });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        // ── Safely parse JSON, expose raw response if PHP returned HTML ──
        const rawText = await res.text();
        let data;
        try {
            data = JSON.parse(rawText);
        } catch (parseErr) {
            console.error('Server returned non-JSON response:');
            console.error(rawText); // ← this will show the actual PHP error HTML
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <span>Retry</span>';
            }
            return;
        }

        if (!data.success) throw new Error(data.error || 'Update failed');

        if (kitchenModalOrderId) await refreshKitchenModal(kitchenModalOrderId);

        const row = document.querySelector(`tr[data-order-id="${data.order_id}"]`);
        if (row && data.order_status) {
            row.setAttribute('data-status', data.order_status);

            const statusCell  = row.querySelector('td[data-label="Status"]');
            const actionsCell = row.querySelector('td[data-label="Actions"]');
            const totalSpan   = row.querySelector('td[data-label="Total"] span');

            if (statusCell)  statusCell.innerHTML = getOrderStatusBadge(data.order_status);
            if (totalSpan)   totalSpan.className  = data.order_status === 'completed'
                                                        ? 'price-total-paid'
                                                        : 'price-total-unpaid';
            if (actionsCell) actionsCell.innerHTML = getOrderActionsHTML({
                order_id:     data.order_id,
                order_number: row.getAttribute('data-order-number'),
                total_amount: row.getAttribute('data-order-total'),
                status:       data.order_status,
            });
        }

    } catch (err) {
        console.error('Item status update error:', err.message);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <span>Retry</span>';
        }
    }
}
// ============================================
// CANCEL / VOID ITEM — show confirm modal
// ============================================
async function cancelVoidOrderItem(orderItemId, action, itemName, btn) {
    const isCancel = action === 'cancel_item';
    pendingItemAction = { orderItemId, action, itemName, btn };

    const titleEl   = document.querySelector('#cancelVoidItemModal .modal-title');
    const iconEl    = document.getElementById('cancelVoidItemIcon');
    const headingEl = document.getElementById('cancelVoidItemHeading');
    const bodyEl    = document.getElementById('cancelVoidItemBody');
    const confirmEl = document.getElementById('confirmCancelVoidItemBtn');
    const selEl     = document.getElementById('cancelVoidItemReasonSelect');

    // Reset first
    window._cancelVoidItemReason?.reset();

    // Configure modal appearance
    if (isCancel) {
        if (titleEl)   titleEl.innerHTML  = `<i class="fas fa-times-circle me-2" style="color:var(--danger);"></i> Cancel Item`;
        if (iconEl)    { iconEl.innerHTML = `<i class="fas fa-times-circle"></i>`; iconEl.style.background = 'rgba(214,64,69,0.10)'; iconEl.style.color = 'var(--danger)'; }
        if (headingEl) headingEl.textContent = `Cancel "${itemName}"?`;
        if (bodyEl)    bodyEl.innerHTML    = `This item has <strong>not been prepared</strong> yet — it will be removed from the order.<br>No stock will be restored (none was deducted).`;
        if (confirmEl) { confirmEl.className = 'btn btn-danger'; confirmEl.style.cssText = ''; confirmEl.innerHTML = '<i class="fas fa-times-circle me-1"></i> Yes, Cancel Item'; }
    } else {
        if (titleEl)   titleEl.innerHTML  = `<i class="fas fa-ban me-2" style="color:var(--primary-dark);"></i> Void Item`;
        if (iconEl)    { iconEl.innerHTML = `<i class="fas fa-ban"></i>`; iconEl.style.background = 'rgba(15,76,92,0.10)'; iconEl.style.color = 'var(--primary-dark)'; }
        if (headingEl) headingEl.textContent = `Void "${itemName}"?`;
        if (bodyEl)    bodyEl.innerHTML    = `This item was already <strong>prepared</strong>. Ingredients will <strong>NOT be restored</strong> — cost is written off as waste.<br>Sale total updated to exclude this item.`;
        if (confirmEl) { confirmEl.className = 'btn'; confirmEl.style.cssText = 'background:var(--primary);color:white;'; confirmEl.innerHTML = '<i class="fas fa-ban me-1"></i> Yes, Void Item'; }
    }

    // Load reasons from DB dynamically
    if (selEl) {
        selEl.innerHTML = '<option value="">— Loading reasons… —</option>';
        await loadReasons(action === 'cancel_item' ? 'cancel_item' : 'void_item', 'cancelVoidItemReasonSelect');
    }

    cancelVoidItemModal?.show();
}

// Execute after modal confirms
async function executeCancelVoidItem(orderItemId, action, btn, reason) {
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Processing…</span>'; }
    try {
        const formData = new FormData();
        formData.append('order_item_id', orderItemId);
        formData.append('action', action);
        formData.append('reason', reason);
        const res  = await fetch(CANCEL_VOID_ITEM_URL, { method: 'POST', body: formData });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Operation failed');
        if (kitchenModalOrderId) await refreshKitchenModal(kitchenModalOrderId);
        const row = document.querySelector(`tr[data-order-id="${data.order_id}"]`);
        if (row && data.order_status) {
            row.setAttribute('data-status', data.order_status);
            const statusCell  = row.querySelector('td[data-label="Status"]');
            const actionsCell = row.querySelector('td[data-label="Actions"]');
            const totalSpan   = row.querySelector('td[data-label="Total"] span');
            if (statusCell) statusCell.innerHTML = getOrderStatusBadge(data.order_status);
            if (totalSpan) {
                totalSpan.className = data.order_status === 'completed' ? 'price-total-paid' : 'price-total-unpaid';
                if (data.new_total !== undefined) {
                    totalSpan.textContent = '₱' + formatPrice(data.new_total);
                    row.setAttribute('data-order-total', data.new_total);
                }
            }
            if (actionsCell) {
                actionsCell.innerHTML = getOrderActionsHTML({
                    order_id:     data.order_id,
                    order_number: row.getAttribute('data-order-number'),
                    total_amount: data.new_total ?? row.getAttribute('data-order-total'),
                    status:       data.order_status,
                });
            }
        }
    } catch (err) {
        console.error('Cancel/void item error:', err);
        showErrorModal('Operation Failed', err.message);
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <span>Retry</span>'; }
    }
}

// ============================================
// VOID ORDER — show confirm modal
// ============================================
async function voidOrder(orderId, orderNumber, btn) {
    const numEl  = document.getElementById('voidOrderNumber');
    const confEl = document.getElementById('confirmVoidOrderBtn');
    if (numEl)  numEl.textContent = orderNumber || '';
    if (confEl) {
        confEl.setAttribute('data-order-id',     orderId);
        confEl.setAttribute('data-order-number', orderNumber);
    }

    // Load void order reasons from DB
    const sel = document.getElementById('voidOrderReasonSelect');
    if (sel) {
        sel.innerHTML = '<option value="">— Loading reasons… —</option>';
        await loadReasons('void_order', 'voidOrderReasonSelect');
    }

    window._voidOrderReason?.reset();
    voidOrderModal?.show();
}

async function executeVoidOrder(orderId, orderNumber, reason, btn) {
    try {
        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('reason',   reason);
        const res  = await fetch(VOID_ORDER_URL, { method: 'POST', body: formData });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Void failed');
        const row = document.querySelector(`tr[data-order-id="${data.order_id}"]`);
        if (row) {
            row.setAttribute('data-status', data.order_status);
            const statusCell  = row.querySelector('td[data-label="Status"]');
            const actionsCell = row.querySelector('td[data-label="Actions"]');
            const totalSpan   = row.querySelector('td[data-label="Total"] span');
            if (statusCell) statusCell.innerHTML = getOrderStatusBadge(data.order_status);
            if (totalSpan)  { totalSpan.className = 'price-total-unpaid'; totalSpan.textContent = '₱0.00'; row.setAttribute('data-order-total', '0'); }
            if (actionsCell) {
                actionsCell.innerHTML = getOrderActionsHTML({
                    order_id:     data.order_id,
                    order_number: row.getAttribute('data-order-number'),
                    total_amount: 0,
                    status:       data.order_status,
                });
            }
        }
        if (kitchenModalOrderId && String(kitchenModalOrderId) === String(orderId)) {
            kitchenItemsModal?.hide();
            kitchenModalOrderId = null;
        }
        await refreshOrders();
    } catch (err) {
        console.error('Void order error:', err);
        showErrorModal('Void Failed', err.message);
    }
}

// ============================================
// KITCHEN ITEMS MODAL
// ============================================
async function openKitchenItemsModal(orderId, orderNumber, orderStatus) {
    const modal = document.getElementById('kitchenItemsModal');
    if (!modal || !kitchenItemsModal) return;
    kitchenModalOrderId = String(orderId);
    modal.setAttribute('data-open-order-id', String(orderId));
    const bodyEl   = document.getElementById('kitchenModalBody');
    const headerEl = document.getElementById('kitchenModalHeader');
    if (bodyEl)   bodyEl.innerHTML = `<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm mb-2" style="color:var(--primary);" role="status"></div><p class="mb-0" style="font-size:0.85rem;">Loading…</p></div>`;
    if (headerEl) headerEl.innerHTML = '';
    kitchenItemsModal.show();
    await refreshKitchenModal(orderId);
}

async function refreshKitchenModal(orderId) {
    const modal = document.getElementById('kitchenItemsModal');
    if (!modal || !modal.classList.contains('show')) return;
    if (String(kitchenModalOrderId) !== String(orderId)) return;
    try {
        const res  = await fetch(`${ORDER_ITEMS_URL}?order_id=${orderId}`, { headers: { 'Cache-Control': 'no-cache' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data.success) return;
        renderKitchenModal(data.order, data.items);
    } catch (err) { console.error('Kitchen modal refresh error:', err); }
}

// FIX 1: Compute activeItemCount and pass to buildItemCardHTML.
// Active = not cancelled, voided, or completed.
function renderKitchenModal(order, items) {
    const modal = document.getElementById('kitchenItemsModal');
    if (!modal || !modal.classList.contains('show')) return;
    const orderStatus = order.status || 'pending';
    const headerEl    = document.getElementById('kitchenModalHeader');
    if (headerEl) {
        headerEl.innerHTML = `
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                    <i class="fas fa-receipt"></i>
                </div>
                <div>
                    <div style="font-size:1.1rem;font-weight:700;color:white;">Order #${escapeHtml(order.order_number)}</div>
                    <div style="font-size:0.78rem;opacity:0.75;color:white;">
                        ${items.length} item${items.length !== 1 ? 's' : ''} &nbsp;·&nbsp;
                        ${order.order_type === 'dine_in' ? 'Dine In' : order.order_type === 'takeout' ? 'Takeout' : '—'}
                    </div>
                </div>
            </div>
            <div style="margin-left:auto;">
                <span class="kitchen-modal-status-pill kitchen-modal-status-${orderStatus}">${capitalize(orderStatus)}</span>
            </div>`;
    }
    const bodyEl = document.getElementById('kitchenModalBody');
    if (bodyEl) {
        // Count items that are still active (not cancelled, voided, or completed).
        // If only 1 active item remains, individual cancel/void buttons are hidden —
        // the user must use the order-level Cancel/Void instead.
        const activeItemCount = items.filter(
            i => !['cancelled', 'voided', 'completed'].includes(i.item_status || 'pending')
        ).length;

        bodyEl.innerHTML = items.length
            ? '<div class="kitchen-modal-items">' + items.map(item => buildItemCardHTML(item, orderStatus, activeItemCount)).join('') + '</div>'
            : '<p class="text-muted text-center py-3">No items found.</p>';
    }
}

// ============================================
// ITEM CARD HTML
// ============================================
// FIX 2: Accept activeItemCount (default 99 so existing call-sites without the
// argument are unaffected). When only 1 active item remains, hide the individual
// cancel/void button — force use of the order-level cancel/void instead.
function buildItemCardHTML(item, orderStatus, activeItemCount = 99) {
    const userRole   = window.userRole || '';
    const isAdmin    = userRole === 'admin';
    const isCashier  = userRole === 'cashier';
    const canUpdate  = userRole === 'kitchen' || isAdmin;
    const orderDone  = ['completed', 'cancelled', 'voided'].includes(orderStatus);
    const itemStatus = item.item_status || 'pending';
    const isTerminal = ['completed', 'cancelled', 'voided'].includes(itemStatus);

    const displayConfig = {
        pending:   { label: 'Pending',   icon: 'fa-hourglass-half', cls: 'item-status-pending'   },
        preparing: { label: 'Preparing', icon: 'fa-bowl-food',       cls: 'item-status-preparing' },
        ready:     { label: 'Ready',     icon: 'fa-circle-check',    cls: 'item-status-ready'     },
        completed: { label: 'Completed', icon: 'fa-check-double',    cls: 'item-status-completed' },
        cancelled: { label: 'Cancelled', icon: 'fa-times-circle',    cls: 'item-status-cancelled' },
        voided:    { label: 'Voided',    icon: 'fa-ban',             cls: 'item-status-voided'    },
    };

    const sc    = displayConfig[itemStatus] || displayConfig.pending;
    const badge = `<span class="item-status-badge ${sc.cls}"><i class="fas ${sc.icon}"></i> ${sc.label}</span>`;

    let progressBtn = '';
    if (canUpdate && !orderDone && !isTerminal && itemStatus !== 'ready') {
        const nextStatus = itemStatus === 'pending' ? 'preparing' : 'ready';
        const btnConfig  = itemStatus === 'pending'
            ? { label: 'Start Cooking', icon: 'fa-play',  cls: 'item-action-start' }
            : { label: 'Mark Ready',    icon: 'fa-check', cls: 'item-action-ready' };
        progressBtn = `<button type="button"
                               class="item-status-btn ${btnConfig.cls}"
                               data-order-item-id="${item.order_item_id}"
                               data-next-status="${nextStatus}">
                           <i class="fas ${btnConfig.icon}"></i>
                           <span>${btnConfig.label}</span>
                       </button>`;
    }

    let adminActionBtn = '';
    // If only 1 active item remains on the order, hide individual cancel/void —
    // user must cancel/void the whole order instead.
    const onlyOneLeft = activeItemCount <= 1;
    const canCancel = (isAdmin || isCashier) && !orderDone && !isTerminal && itemStatus === 'pending' && !onlyOneLeft;
    const canVoid   = isAdmin && !orderDone && !isTerminal && itemStatus === 'ready' && !onlyOneLeft;

    if (canCancel) {
        adminActionBtn = `<button type="button"
                                  class="item-status-btn item-action-cancel-item"
                                  data-order-item-id="${item.order_item_id}"
                                  data-action="cancel_item"
                                  data-item-name="${escapeHtml(item.item_name || '')}"
                                  title="Cancel this item">
                              <i class="fas fa-times"></i>
                              <span>Cancel</span>
                          </button>`;
    } else if (canVoid) {
        adminActionBtn = `<button type="button"
                                  class="item-status-btn item-action-void-item"
                                  data-order-item-id="${item.order_item_id}"
                                  data-action="void_item"
                                  data-item-name="${escapeHtml(item.item_name || '')}"
                                  title="Void — ingredients written off as waste">
                              <i class="fas fa-ban"></i>
                              <span>Void</span>
                          </button>`;
    }

    const baseName    = escapeHtml(item.item_name || '');
    const variantName = item.variant_name_snapshot || item.current_variant_name || item.variant_name || null;
    const variantBit  = variantName ? `<span class="item-variant-label"> · ${escapeHtml(variantName)}</span>` : '';
    const addonsHTML  = (item.addons && item.addons.length > 0)
        ? `<div class="item-addons"><span class="item-addons-label">Add-ons:</span>
           ${item.addons.map(a => `<span class="item-addon-tag">${escapeHtml(a.addon_name)}${a.quantity > 1 ? ` ×${a.quantity}` : ''}</span>`).join('')}</div>`
        : '';
    const noteHTML = (item.item_notes && item.item_notes.trim())
        ? `<div class="item-card-note-inline"><span class="item-card-note-label">Note:</span><span class="item-card-note-text">${escapeHtml(item.item_notes)}</span></div>`
        : '';

    return `<div class="item-card item-card-${itemStatus}" data-item-id="${item.order_item_id}">
                <div class="item-card-header">
                    <div class="item-card-name">
                        <span class="item-name-text">${baseName}${variantBit}</span>
                        <span class="item-qty-badge">×${item.quantity || 1}</span>
                    </div>
                    <div class="item-card-right">
                        ${badge}
                        ${progressBtn}
                        ${adminActionBtn}
                    </div>
                </div>
                ${addonsHTML}${noteHTML}
            </div>`;
}

// ============================================
// ORDER DETAILS MODAL
// ============================================
function viewOrderDetails(orderId, orderNumber) {
    if (!orderDetailsModal) return;
    openDetailsOrderId = String(orderId);
    const loaderEl  = document.getElementById('orderDetailsLoader');
    const contentEl = document.getElementById('orderDetailsContent');
    const errorEl   = document.getElementById('orderDetailsError');
    if (loaderEl)  loaderEl.style.display  = 'block';
    if (contentEl) contentEl.style.display = 'none';
    if (errorEl)   errorEl.style.display   = 'none';
    const numEl = document.getElementById('detailOrderNumber');
    if (numEl) numEl.textContent = '#' + (orderNumber || '');
    const printBtn = document.getElementById('printReceiptBtn');
    if (printBtn) printBtn.style.display = 'none';
    orderDetailsModal.show();
    fetchAndPopulateOrderDetails(orderId);
}

async function fetchAndPopulateOrderDetails(orderId) {
    const loaderEl  = document.getElementById('orderDetailsLoader');
    const contentEl = document.getElementById('orderDetailsContent');
    const errorEl   = document.getElementById('orderDetailsError');
    try {
        const res  = await fetch(`${ORDER_DETAILS_URL}?order_id=${orderId}`, { headers: { 'Cache-Control': 'no-cache' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed to load');
        const d = data.data;
        populateOrderDetailsModal(d.order || {}, d.items || [], d.payment || null, d.cancel_audit || null, d.void_audit || null);
        if (loaderEl)  loaderEl.style.display  = 'none';
        if (contentEl) contentEl.style.display = 'block';
        if (errorEl)   errorEl.style.display   = 'none';
    } catch (err) {
        console.error('Order details error:', err);
        if (loaderEl)  loaderEl.style.display  = 'none';
        if (errorEl)   errorEl.style.display   = 'block';
        if (contentEl) contentEl.style.display = 'none';
    }
}

async function refreshOrderDetailsModal(orderId) {
    const modal = document.getElementById('orderDetailsModal');
    if (!modal || !modal.classList.contains('show')) return;
    try {
        const res  = await fetch(`${ORDER_DETAILS_URL}?order_id=${orderId}`, { headers: { 'Cache-Control': 'no-cache' } });
        const data = await res.json();
        if (!data.success) return;
        const d = data.data;
        populateOrderDetailsModal(d.order || {}, d.items || [], d.payment || null, d.cancel_audit || null, d.void_audit || null);
    } catch (err) { /* silent */ }
}

function populateOrderDetailsModal(order, items, payment, cancelAudit, voidAudit) {
    const set     = (id, val)  => { const el = document.getElementById(id); if (el) el.textContent = val; };
    const setHTML = (id, html) => { const el = document.getElementById(id); if (el) el.innerHTML  = html; };

    const numEl = document.getElementById('detailOrderNumber');
    if (numEl) numEl.textContent = '#' + (order.order_number || '');

    set('detailCustomer', order.customer_name || 'Walk-in');
    if (order.created_at) {
        set('detailDate', new Date(order.created_at).toLocaleString('en-PH', {
            year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
        }));
    }
    set('detailCashier', order.cashier_name || 'N/A');
    setHTML('detailOrderType', order.order_type ? getOrderTypeBadge(order.order_type) : '—');
    setHTML('detailStatus', getOrderStatusBadge(order.status));

    const tableWrap = document.getElementById('detailTableWrap');
    const tableEl   = document.getElementById('detailTable');
    if (order.table_name) {
        if (tableEl)   tableEl.innerHTML       = `<span class="table-number-badge"><i class="fas fa-chair"></i> ${escapeHtml(order.table_name)}</span>`;
        if (tableWrap) tableWrap.style.display = '';
    } else {
        if (tableWrap) tableWrap.style.display = 'none';
    }

    // Items table
    const itemsBody = document.getElementById('detailItemsBody');
    if (itemsBody) {
        itemsBody.innerHTML = '';
        items.forEach(item => {
            const itemSubtotal       = (parseFloat(item.unit_price || 0) * parseInt(item.quantity || 1)).toFixed(2);
            const displayItemName    = item.item_name_snapshot    || item.item_name    || '';
            const displayVariantName = item.variant_name_snapshot || item.current_variant_name || item.variant_name || null;
            const variantBit         = displayVariantName ? `<span class="od-variant-label"> · ${escapeHtml(displayVariantName)}</span>` : '';

            let itemStatusBit = '';
            if (['cancelled', 'voided'].includes(item.item_status)) {
                itemStatusBit = ` <span class="item-status-badge item-status-${item.item_status}" style="font-size:0.68rem;padding:0.12rem 0.45rem;vertical-align:middle;">${capitalize(item.item_status)}</span>`;
            }

            // FIX 3: Only show per-item reason pill for item-level cancel/void.
            // Order-level cancel/void writes the same reason to all items — that
            // reason is shown once in #detailCancelSection / #detailVoidSection.
            let reasonPill = '';
            if (item.item_status === 'cancelled' && item.cancel_reason && order.status !== 'cancelled') {
                reasonPill = `<div class="od-note-pill" style="background:rgba(214,64,69,0.08);border-color:rgba(214,64,69,0.3);color:#9e3a34;">
                    <i class="fas fa-times-circle" style="color:var(--danger);"></i>
                    <span style="font-style:normal;font-weight:600;">Cancel reason:</span> ${escapeHtml(item.cancel_reason)}
                </div>`;
            } else if (item.item_status === 'voided' && item.void_reason && order.status !== 'voided') {
                reasonPill = `<div class="od-note-pill" style="background:rgba(15,76,92,0.07);border-color:rgba(15,76,92,0.25);color:var(--primary-dark);">
                    <i class="fas fa-ban" style="color:var(--primary-dark);"></i>
                    <span style="font-style:normal;font-weight:600;">Void reason:</span> ${escapeHtml(item.void_reason)}
                </div>`;
            }

            const notePill = (item.item_notes && item.item_notes.trim())
                ? `<div class="od-note-pill"><i class="fas fa-pen-to-square"></i> ${escapeHtml(item.item_notes)}</div>` : '';

            let priceInfo = `₱${formatPrice(item.unit_price)}`;
            if (item.current_variant_price && parseFloat(item.current_variant_price) !== parseFloat(item.unit_price)) {
                const priceDiff = parseFloat(item.current_variant_price) - parseFloat(item.unit_price);
                const diffClass = priceDiff > 0 ? 'price-up' : 'price-down';
                const diffText  = priceDiff > 0 ? '+' : '';
                priceInfo = `₱${formatPrice(item.unit_price)} <small class="od-price-compare ${diffClass}">(${diffText}₱${formatPrice(Math.abs(priceDiff))})</small>`;
            }

            const tr = document.createElement('tr');
            tr.className = 'od-item-row';
            if (['cancelled', 'voided'].includes(item.item_status)) tr.style.opacity = '0.6';
            tr.innerHTML = `
                <td class="od-td od-td-item">
                    <span class="od-item-name" style="${item.item_status === 'cancelled' ? 'text-decoration:line-through;' : ''}">${escapeHtml(displayItemName)}${variantBit}</span>${itemStatusBit}
                    ${notePill}${reasonPill}
                </td>
                <td class="od-td od-td-center">${item.quantity || 1}</td>
                <td class="od-td od-td-right od-muted">${priceInfo}</td>
                <td class="od-td od-td-right od-item-subtotal"${['cancelled','voided'].includes(item.item_status) ? ' style="text-decoration:line-through;color:var(--warm-gray);"' : ''}>₱${itemSubtotal}</td>`;
            itemsBody.appendChild(tr);

            (item.addons || []).forEach(addon => {
                const addonSubtotal = (parseFloat(addon.price || 0) * parseInt(addon.quantity || 1)).toFixed(2);
                const addonTr = document.createElement('tr');
                addonTr.className = 'od-addon-row';
                addonTr.innerHTML = `
                    <td class="od-td od-td-addon"><i class="fas fa-corner-down-right od-addon-icon"></i>${escapeHtml(addon.addon_name)}</td>
                    <td class="od-td od-td-center od-muted">${addon.quantity || 1}</td>
                    <td class="od-td od-td-right od-muted">₱${formatPrice(addon.price)}</td>
                    <td class="od-td od-td-right od-muted">₱${addonSubtotal}</td>`;
                itemsBody.appendChild(addonTr);
            });
        });
    }

    // Totals
    const total    = parseFloat(order.total_amount || 0);
    const subtotal = total / 1.12;
    const vat      = total - subtotal;
    set('detailSubtotal', subtotal.toFixed(2));
    set('detailVat',      vat.toFixed(2));
    set('detailTotal',    total.toFixed(2));

    const totalDisplay = document.getElementById('detailTotalDisplay');
    if (totalDisplay) {
        totalDisplay.style.color = order.status === 'completed' ? 'var(--success)' : 'var(--danger)';
    }

    // Hide all audit sections
    const paySection    = document.getElementById('detailPaymentSection');
    const cancelSection = document.getElementById('detailCancelSection');
    const voidSection   = document.getElementById('detailVoidSection');
    if (paySection)    paySection.style.display    = 'none';
    if (cancelSection) cancelSection.style.display = 'none';
    if (voidSection)   voidSection.style.display   = 'none';

    // Show correct section
    if (payment && order.status === 'completed') {
        if (paySection) {
            paySection.style.display = '';
            set('detailPayMethod',  payment.payment_method || '—');
            set('detailAmountPaid', formatPrice(payment.amount_paid));
            set('detailChange',     formatPrice(payment.change_amount));
            const processedByRow = document.getElementById('detailProcessedByRow');
            const processedByEl  = document.getElementById('detailProcessedBy');
            if (processedByRow && processedByEl) {
                const cashierId     = parseInt(order.cashier_id);
                const processedById = parseInt(payment.processed_by);
                if (payment.processed_by && processedById !== cashierId && payment.processed_by_name) {
                    processedByEl.textContent    = payment.processed_by_name;
                    processedByRow.style.display = '';
                } else {
                    processedByRow.style.display = 'none';
                }
            }
        }
    } else if (order.status === 'cancelled' && cancelSection) {
        cancelSection.style.display = '';
        const reasonEl = document.getElementById('detailCancelReason');
        if (reasonEl) reasonEl.textContent = cancelAudit?.reason || '—';
    } else if (order.status === 'voided' && voidSection) {
        voidSection.style.display = '';
        const voidReasonEl = document.getElementById('detailVoidReason');
        const wasteCostEl  = document.getElementById('detailWasteCost');
        if (voidReasonEl) voidReasonEl.textContent = voidAudit?.reason || '—';
        if (wasteCostEl)  wasteCostEl.textContent  = voidAudit?.total_waste_cost !== undefined
            ? '₱' + formatPrice(voidAudit.total_waste_cost) : '—';
    }

    // Print button
    const printBtn = document.getElementById('printReceiptBtn');
    if (printBtn) {
        if (order.status === 'completed' && order.order_id) {
            printBtn.href          = `${window.location.pathname}?page=print_receipt&order_id=${order.order_id}`;
            printBtn.style.display = '';
        } else {
            printBtn.style.display = 'none';
        }
    }
}

// ============================================
// PAYMENT + CANCEL ORDER MODALS
// ============================================
function showPaymentModal(orderId, orderNumber, orderTotal) {
    if (!paymentModal) return;
    currentOrderTotal = parseFloat(orderTotal) || 0;
    document.getElementById('paymentOrderNumber').textContent = orderNumber || '';
    document.getElementById('paymentTotalAmount').textContent = currentOrderTotal.toFixed(2);
    document.getElementById('paymentOrderId').value           = orderId || '';
    document.getElementById('amountPaid').value               = '';
    document.getElementById('changeAmountHidden').value       = '0';
    document.getElementById('changeAlert')?.classList.add('pm-hidden');
    document.getElementById('insufficientAlert')?.classList.add('pm-hidden');
    document.getElementById('confirmPaymentBtn').disabled     = true;
    paymentModal.show();
}

async function showCancelOrderModal(orderId, orderNumber) {
    if (!cancelOrderModal) return;
    document.getElementById('cancelOrderNumber').textContent = orderNumber || '';
    document.getElementById('cancelOrderId').value           = orderId || '';

    // Load cancel order reasons from DB
    const sel = document.getElementById('cancelOrderReasonSelect');
    if (sel) {
        sel.innerHTML = '<option value="">— Loading reasons… —</option>';
        await loadReasons('cancel_order', 'cancelOrderReasonSelect');
    }

    window._cancelOrderReason?.reset();
    cancelOrderModal.show();
}

// ============================================
// EVENT LISTENERS
// ============================================
function setupEventListeners() {
    setupSidebarToggle();

    document.querySelectorAll('.cashier-view-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.cashier-view-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCashierView = this.getAttribute('data-view') || 'all';
            loadPage(1);
        });
    });

    document.getElementById('processedByFilter')?.addEventListener('change', function () {
        currentProcessedBy = parseInt(this.value) || 0;
        loadPage(1);
    });

    const dateFrom        = document.getElementById('dateFrom');
    const dateTo          = document.getElementById('dateTo');
    const btnAll          = document.getElementById('btnAll');
    const btnToday        = document.getElementById('btnToday');
    const btnYesterday    = document.getElementById('btnYesterday');
    const btnThisWeek     = document.getElementById('btnThisWeek');
    const btnCustom       = document.getElementById('btnCustom');
    const dateCustomRange = document.getElementById('dateCustomRange');

    function todayStr(offsetDays = 0) {
        const d = new Date();
        d.setDate(d.getDate() + offsetDays);
        return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    }
    function toLocalStr(dateObj) {
        return `${dateObj.getFullYear()}-${String(dateObj.getMonth()+1).padStart(2,'0')}-${String(dateObj.getDate()).padStart(2,'0')}`;
    }

    const today = todayStr();
    if (dateFrom) dateFrom.max = today;
    if (dateTo)   dateTo.max   = today;

    const allShortcutBtns = [btnAll, btnToday, btnYesterday, btnThisWeek, btnCustom];
    function setActiveShortcut(name) {
        allShortcutBtns.forEach(btn => { if (btn) btn.classList.remove('active'); });
        const map = { all: btnAll, today: btnToday, yesterday: btnYesterday, week: btnThisWeek, custom: btnCustom };
        if (map[name]) map[name].classList.add('active');
        if (dateCustomRange) dateCustomRange.style.display = name === 'custom' ? 'flex' : 'none';
    }

    function applyCustomDateFilter() {
        const from = dateFrom?.value || '';
        const to   = dateTo?.value   || '';
        if (from && to && from > to) {
            if (document.activeElement === dateFrom) { if (dateTo) dateTo.value = from; }
            else { if (dateFrom) dateFrom.value = to; }
        }
        currentDateFrom = dateFrom?.value || '';
        currentDateTo   = dateTo?.value   || '';
        if (dateFrom && currentDateTo)    dateFrom.max = currentDateTo;
        if (dateTo   && currentDateFrom)  dateTo.min   = currentDateFrom;
        if (dateFrom && !currentDateTo)   dateFrom.max = today;
        if (dateTo   && !currentDateFrom) dateTo.min   = '';
        loadPage(1);
    }

    dateFrom?.addEventListener('change', applyCustomDateFilter);
    dateTo?.addEventListener('change',   applyCustomDateFilter);

    btnAll?.addEventListener('click', () => {
        setActiveShortcut('all'); currentDateFrom = ''; currentDateTo = '';
        if (dateFrom) { dateFrom.value = ''; dateFrom.min = ''; dateFrom.max = today; }
        if (dateTo)   { dateTo.value   = ''; dateTo.min   = ''; dateTo.max   = today; }
        loadPage(1);
    });
    btnToday?.addEventListener('click', () => {
        setActiveShortcut('today'); currentDateFrom = today; currentDateTo = today;
        if (dateFrom) { dateFrom.value = today; dateFrom.min = ''; dateFrom.max = today; }
        if (dateTo)   { dateTo.value   = today; dateTo.min   = today; dateTo.max = today; }
        loadPage(1);
    });
    btnYesterday?.addEventListener('click', () => {
        setActiveShortcut('yesterday');
        const y = todayStr(-1); currentDateFrom = y; currentDateTo = y;
        if (dateFrom) { dateFrom.value = y; dateFrom.min = ''; dateFrom.max = today; }
        if (dateTo)   { dateTo.value   = y; dateTo.min   = y;  dateTo.max   = today; }
        loadPage(1);
    });
    btnThisWeek?.addEventListener('click', () => {
        setActiveShortcut('week');
        const now = new Date(), day = now.getDay();
        const mon = new Date(now); mon.setDate(now.getDate() - ((day + 6) % 7));
        const endOfWeek = new Date(mon); endOfWeek.setDate(mon.getDate() + 6);
        const sun = endOfWeek > now ? now : endOfWeek;
        const monStr = toLocalStr(mon), sunStr = toLocalStr(sun);
        currentDateFrom = monStr; currentDateTo = sunStr;
        if (dateFrom) { dateFrom.value = monStr; dateFrom.min = ''; dateFrom.max = sunStr; }
        if (dateTo)   { dateTo.value   = sunStr; dateTo.min   = monStr; dateTo.max = today; }
        loadPage(1);
    });
    btnCustom?.addEventListener('click', () => {
        setActiveShortcut('custom'); currentDateFrom = ''; currentDateTo = '';
        if (dateFrom) { dateFrom.value = ''; dateFrom.min = ''; dateFrom.max = today; }
        if (dateTo)   { dateTo.value   = ''; dateTo.min   = ''; dateTo.max   = today; }
        loadPage(1);
    });

    setActiveShortcut('today');
    currentDateFrom = today;
    currentDateTo   = today;

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let debounce;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounce);
            debounce = setTimeout(() => { currentSearch = this.value.toLowerCase().trim(); loadPage(1); }, 300);
        });
    }

    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-status') || 'all';
            syncProcessedByVisibility();
            loadPage(1);
        });
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.items-manage-btn')) {
            const btn = e.target.closest('.items-manage-btn');
            const row = btn.closest('tr');
            if (!row) return;
            openKitchenItemsModal(row.getAttribute('data-order-id'), row.getAttribute('data-order-number'), row.getAttribute('data-status'));
        }
        if (e.target.closest('.view-order-btn')) {
            const btn = e.target.closest('.view-order-btn');
            viewOrderDetails(btn.dataset.orderId, btn.dataset.orderNumber);
        }
        if (e.target.closest('.collect-payment-btn')) {
            const btn = e.target.closest('.collect-payment-btn');
            showPaymentModal(btn.dataset.orderId, btn.dataset.orderNumber, btn.dataset.orderTotal);
        }
        if (e.target.closest('.cancel-order-btn')) {
            const btn = e.target.closest('.cancel-order-btn');
            showCancelOrderModal(btn.dataset.orderId, btn.dataset.orderNumber);
        }
        if (e.target.closest('.void-order-btn')) {
            const btn = e.target.closest('.void-order-btn');
            if (btn.disabled) return;
            voidOrder(btn.dataset.orderId, btn.dataset.orderNumber, btn);
        }
        if (e.target.closest('.item-status-btn')) {
            const btn = e.target.closest('.item-status-btn');
            if (btn.disabled) return;
            if (btn.classList.contains('item-action-cancel-item') || btn.classList.contains('item-action-void-item')) {
                cancelVoidOrderItem(
                    btn.getAttribute('data-order-item-id'),
                    btn.getAttribute('data-action'),
                    btn.getAttribute('data-item-name'),
                    btn
                );
                return;
            }
            updateItemStatus(btn.getAttribute('data-order-item-id'), btn.getAttribute('data-next-status'), btn);
        }
    });

    const amountPaid = document.getElementById('amountPaid');
    if (amountPaid) {
        amountPaid.addEventListener('input', function () {
            const amount      = parseFloat(this.value) || 0;
            const changeAlert = document.getElementById('changeAlert');
            const insufAlert  = document.getElementById('insufficientAlert');
            const confirmBtn  = document.getElementById('confirmPaymentBtn');
            if (amount >= currentOrderTotal && amount > 0) {
                const change = amount - currentOrderTotal;
                document.getElementById('changeAmount').textContent = change.toFixed(2);
                document.getElementById('changeAmountHidden').value = change.toFixed(2);
                changeAlert?.classList.remove('pm-hidden');
                insufAlert?.classList.add('pm-hidden');
                if (confirmBtn) confirmBtn.disabled = false;
            } else {
                changeAlert?.classList.add('pm-hidden');
                if (amount > 0) insufAlert?.classList.remove('pm-hidden');
                else            insufAlert?.classList.add('pm-hidden');
                if (confirmBtn) confirmBtn.disabled = true;
            }
        });
    }

    document.addEventListener('visibilitychange', () => {
        document.hidden ? stopAutoRefresh() : startAutoRefresh();
    });

    setupModalBackdropCleanup();
}

// ============================================
// UTILITIES
// ============================================
window.capitalize = function(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

window.refreshOrders    = refreshOrders;
window.startAutoRefresh = startAutoRefresh;
window.stopAutoRefresh  = stopAutoRefresh;