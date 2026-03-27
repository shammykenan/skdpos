/**
 * ============================================
 * ORDERS MANAGEMENT — Item-level tracking
 * ============================================
 */

const REFRESH_INTERVAL    = 2000;
const PAGE_LIMIT          = 10;
const AJAX_URL            = '/coffee_shop/app/ajax/get_orders.php';
const ITEM_STATUS_URL     = '/coffee_shop/app/ajax/update_order_item_status.php';
const ORDER_DETAILS_URL   = '/coffee_shop/app/ajax/get_order_details.php';
const ADMIN_USERS_URL     = '/coffee_shop/app/ajax/get_admin_users.php';
const ORDER_ITEMS_URL     = '/coffee_shop/app/ajax/get_order_items.php';

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

let paymentModal, cancelOrderModal, orderDetailsModal, kitchenItemsModal;

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    try {
        if (typeof bootstrap !== 'undefined') {
            paymentModal      = new bootstrap.Modal(document.getElementById('paymentModal'));
            cancelOrderModal  = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
            orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            kitchenItemsModal = new bootstrap.Modal(document.getElementById('kitchenItemsModal'));
        }
    } catch (e) {
        console.error('Modal init error:', e);
    }

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

    setupEventListeners();
    loadPage(1);
    startAutoRefresh();

    if (window.userRole === 'admin') {
        loadAdminUsers();
    }
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
    } catch (err) {
        console.error('Failed to load admin users:', err);
    }
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
        wrapper.style.display = 'none';
        currentProcessedBy    = 0;
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
        console.error('❌ Load error:', err);
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

        if (openDetailsOrderId) {
            refreshOrderDetailsModal(openDetailsOrderId);
        }

        if (kitchenModalOrderId) {
            refreshKitchenModal(kitchenModalOrderId);
        }

    } catch (err) {
        console.error('❌ Refresh error:', err);
    } finally {
        isRefreshing = false;
    }
}

function startAutoRefresh() {
    stopAutoRefresh();
    refreshTimer = setInterval(refreshOrders, REFRESH_INTERVAL);
}
function stopAutoRefresh() {
    if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; }
}

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
    if (currentFilter === 'completed' && currentProcessedBy > 0) {
        params.set('processed_by', currentProcessedBy);
    }
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

// ============================================
// SMART PATCH
// ============================================
function patchTableRows(orders) {
    const tbody = document.getElementById('ordersTableBody');
    if (!tbody) return;

    orders.forEach(order => {
        const row = tbody.querySelector(`tr[data-order-id="${order.order_id}"]`);
        if (!row) return;

        const currentStatus = row.getAttribute('data-status');
        const newStatus     = (order.status || 'pending').toLowerCase();

        if (currentStatus !== newStatus) {
            row.setAttribute('data-status', newStatus);
            const statusCell  = row.querySelector('td[data-label="Status"]');
            const actionsCell = row.querySelector('td[data-label="Actions"]');
            const totalSpan   = row.querySelector('td[data-label="Total"] span');
            if (statusCell)  statusCell.innerHTML  = getOrderStatusBadge(newStatus);
            if (actionsCell) actionsCell.innerHTML = getOrderActionsHTML(order);
            if (totalSpan)   totalSpan.className   = newStatus === 'completed' ? 'price-total-paid' : 'price-total-unpaid';
        }

        if (order.items) {
            const itemsCell = row.querySelector('.items-cell');
            if (itemsCell) {
                const tally   = { pending: 0, preparing: 0, ready: 0 };
                order.items.forEach(i => { const s = i.item_status || 'pending'; tally[s] = (tally[s] || 0) + 1; });
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
    const style = active ? 'background:var(--primary);color:#fff;"' : disabled ? 'background:#f0f0f0;color:#bbb;cursor:not-allowed;"' : 'background:var(--light-bg);color:var(--navy);"';
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
    const data = { all: total ?? 0, pending: counts?.pending ?? 0, preparing: counts?.preparing ?? 0, ready: counts?.ready ?? 0, completed: counts?.completed ?? 0, cancelled: counts?.cancelled ?? 0 };
    Object.keys(data).forEach(status => {
        const span = document.querySelector(`.filter-tab[data-status="${status}"] .filter-count`);
        if (span) span.textContent = data[status];
    });
}

function updateFilterLabel() {
    const map = { all: 'All Orders', pending: 'Pending Orders', preparing: 'Preparing Orders', ready: 'Ready Orders', completed: 'Completed Orders', cancelled: 'Cancelled Orders' };
    const el  = document.getElementById('currentFilterLabel');
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
// ROW HTML — item name chips include variant
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

/**
 * Build the item chips in a table row.
 * Shows "Item Name · Variant" when a variant exists.
 */
function getOrderItemsHTML(items, orderStatus, orderId) {
    if (!items || items.length === 0) return '<span class="text-muted fst-italic" style="font-size:0.8rem;">No items</span>';

    const tally   = { pending: 0, preparing: 0, ready: 0 };
    items.forEach(i => { const s = i.item_status || 'pending'; tally[s] = (tally[s] || 0) + 1; });
    const dotsCfg = [
        { key: 'pending',   color: '#f59e0b', icon: 'fa-hourglass-half' },
        { key: 'preparing', color: '#3b82f6', icon: 'fa-bowl-food'      },
        { key: 'ready',     color: '#10b981', icon: 'fa-circle-check'   },
    ];
    const dotsHTML = dotsCfg.filter(d => tally[d.key] > 0)
        .map(d => `<span class="item-tally-dot" style="background:${d.color}18;color:${d.color};border:1px solid ${d.color}44;"><i class="fas ${d.icon}" style="font-size:0.6rem;"></i> ${tally[d.key]}</span>`)
        .join('');

    // Name chips: show "ItemName · Variant" when variant exists, prioritize snapshot data for historical accuracy
    const namePreview = items.slice(0, 2).map(i => {
        const baseName    = escapeHtml(i.item_name_snapshot || i.item_name || '');
        // Use snapshot variant name first (historical accuracy), fall back to current variant name
        const variantName = i.variant_name_snapshot || i.current_variant_name || i.variant_name || null;
        const qty         = parseInt(i.quantity) > 1 ? ` ×${i.quantity}` : '';
        const variantBit  = variantName
            ? `<span style="opacity:0.65;font-weight:400;"> · ${escapeHtml(variantName)}</span>`
            : '';
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
    } else if (status === 'pending' && (isAdmin || isCashier)) {
        html += `<button type="button" class="btn btn-danger btn-sm cancel-order-btn"
                         data-order-id="${orderId}" data-order-number="${escapeHtml(orderNum)}">
                     <i class="fas fa-times"></i> Cancel</button>`;
    } else if ((status === 'pending' || status === 'preparing') && isCashier && !isAdmin) {
        html += `<span class="badge bg-success action-status-badge" style="font-size:0.75rem;border-radius:20px;display:flex;align-items:center;justify-content:center;padding:0.4rem 0.8rem;width:100%;box-sizing:border-box;"></i> In Kitchen</span>`;
    } else if (status === 'completed') {
        html += `<span class="badge bg-success action-status-badge" style="font-size:0.75rem;border-radius:20px;display:flex;align-items:center;justify-content:center;padding:0.4rem 0.8rem;width:100%;box-sizing:border-box;"></i> Paid</span>`;
    } else if (status === 'cancelled') {
        html += `<span class="badge bg-success action-status-badge" style="font-size:0.75rem;border-radius:20px;display:flex;align-items:center;justify-content:center;padding:0.4rem 0.8rem;width:100%;box-sizing:border-box;"></i> Cancelled</span>`;
    }

    html += '</div>';
    return html;
}

// ============================================
// ITEM STATUS UPDATE
// ============================================
async function updateItemStatus(orderItemId, nextStatus, btn) {
    if (btn) {
        btn.disabled  = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Updating…</span>';
    }

    try {
        const formData = new FormData();
        formData.append('order_item_id', orderItemId);
        formData.append('status', nextStatus);

        const res  = await fetch(ITEM_STATUS_URL, { method: 'POST', body: formData });
        const data = await res.json();

        if (!data.success) throw new Error(data.error || 'Update failed');

        if (kitchenModalOrderId) {
            await refreshKitchenModal(kitchenModalOrderId);
        }

        const row = document.querySelector(`tr[data-order-id="${data.order_id}"]`);
        if (row && data.order_status) {
            row.setAttribute('data-status', data.order_status);
            const statusCell  = row.querySelector('td[data-label="Status"]');
            const actionsCell = row.querySelector('td[data-label="Actions"]');
            const totalSpan   = row.querySelector('td[data-label="Total"] span');
            if (statusCell) statusCell.innerHTML = getOrderStatusBadge(data.order_status);
            if (totalSpan)  totalSpan.className  = data.order_status === 'completed' ? 'price-total-paid' : 'price-total-unpaid';
            if (actionsCell) {
                actionsCell.innerHTML = getOrderActionsHTML({
                    order_id:     data.order_id,
                    order_number: row.getAttribute('data-order-number'),
                    total_amount: row.getAttribute('data-order-total'),
                    status:       data.order_status,
                });
            }
        }

    } catch (err) {
        console.error('❌ Item status update error:', err);
        if (btn) {
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <span>Retry</span>';
        }
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
    if (bodyEl) bodyEl.innerHTML = `
        <div class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm mb-2" style="color:var(--primary);" role="status"></div>
            <p class="mb-0" style="font-size:0.85rem;">Loading…</p>
        </div>`;
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
        const data = await res.json();
        if (!data.success) return;
        renderKitchenModal(data.order, data.items);
    } catch (err) {
        console.error('❌ Kitchen modal refresh error:', err);
    }
}

function renderKitchenModal(order, items) {
    const modal = document.getElementById('kitchenItemsModal');
    if (!modal || !modal.classList.contains('show')) return;

    const orderStatus = order.status || 'pending';

    const headerEl = document.getElementById('kitchenModalHeader');
    if (headerEl) {
        headerEl.innerHTML = `
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,0.15);
                            display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
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
        bodyEl.innerHTML = items.length
            ? '<div class="kitchen-modal-items">' + items.map(item => buildItemCardHTML(item, orderStatus)).join('') + '</div>'
            : '<p class="text-muted text-center py-3">No items found.</p>';
    }
}

/**
 * Build a kitchen item card.
 * Shows "ItemName · VariantName" when a variant exists.
 */
function buildItemCardHTML(item, orderStatus) {
    const userRole   = window.userRole || '';
    const canUpdate  = userRole === 'kitchen' || userRole === 'admin';
    const orderDone  = orderStatus === 'completed' || orderStatus === 'cancelled';
    const itemStatus = item.item_status || 'pending';
    const isReady    = itemStatus === 'ready' || itemStatus === 'completed' || itemStatus === 'cancelled';

    const displayConfig = {
        pending:   { label: 'Pending',   icon: 'fa-hourglass-half', cls: 'item-status-pending'   },
        preparing: { label: 'Preparing', icon: 'fa-bowl-food',       cls: 'item-status-preparing' },
        ready:     { label: 'Ready',     icon: 'fa-circle-check',    cls: 'item-status-ready'     },
        completed: { label: 'Completed', icon: 'fa-check-double',    cls: 'item-status-completed' },
        cancelled: { label: 'Cancelled', icon: 'fa-ban',             cls: 'item-status-cancelled' },
    };

    const sc    = displayConfig[itemStatus] || displayConfig.pending;
    const badge = `<span class="item-status-badge ${sc.cls}"><i class="fas ${sc.icon}"></i> ${sc.label}</span>`;

    let actionBtn = '';
    if (canUpdate && !orderDone && !isReady) {
        const nextStatus = itemStatus === 'pending' ? 'preparing' : 'ready';
        const btnConfig  = itemStatus === 'pending'
            ? { label: 'Start Cooking', icon: 'fa-play',  cls: 'item-action-start' }
            : { label: 'Mark Ready',    icon: 'fa-check', cls: 'item-action-ready' };
        actionBtn = `<button type="button"
                             class="item-status-btn ${btnConfig.cls}"
                             data-order-item-id="${item.order_item_id}"
                             data-next-status="${nextStatus}">
                         <i class="fas ${btnConfig.icon}"></i>
                         <span>${btnConfig.label}</span>
                     </button>`;
    }

    // Item display name: "Item Name · Variant" if variant exists, prioritize snapshot data
    const baseName   = escapeHtml(item.item_name || '');
    // Use snapshot variant name first (historical accuracy), fall back to current variant name
    const variantName = item.variant_name_snapshot || item.current_variant_name || item.variant_name || null;
    const variantBit  = variantName
        ? `<span class="item-variant-label"> · ${escapeHtml(variantName)}</span>`
        : '';

    const addonsHTML = (item.addons && item.addons.length > 0)
        ? `<div class="item-addons"><span class="item-addons-label">Add-ons:</span>
           ${item.addons.map(a => `<span class="item-addon-tag">${escapeHtml(a.addon_name)}${a.quantity > 1 ? ` ×${a.quantity}` : ''}</span>`).join('')}</div>`
        : '';

    const noteHTML = (item.item_notes && item.item_notes.trim())
        ? `<div class="item-card-note-inline"><span class="item-card-note-label">Note:</span><span class="item-card-note-text">${escapeHtml(item.item_notes)}</span></div>`
        : '';

    return `<div class="item-card item-card-${itemStatus}"
                 data-item-id="${item.order_item_id}">
                <div class="item-card-header">
                    <div class="item-card-name">
                        <span class="item-name-text">${baseName}${variantBit}</span>
                        <span class="item-qty-badge">×${item.quantity || 1}</span>
                    </div>
                    <div class="item-card-right">${badge}${actionBtn}</div>
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
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch (e) { console.error('❌ Raw PHP response:', text); throw new Error('Invalid JSON from server'); }

        if (!data.success) throw new Error(data.message || 'Failed to load');

        populateOrderDetailsModal(data.data.order || {}, data.data.items || [], data.data.payment || null);

        if (loaderEl)  loaderEl.style.display  = 'none';
        if (contentEl) contentEl.style.display = 'block';
        if (errorEl)   errorEl.style.display   = 'none';

    } catch (err) {
        console.error('❌ Order details error:', err);
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
        populateOrderDetailsModal(data.data.order || {}, data.data.items || [], data.data.payment || null);
    } catch (err) { /* silent */ }
}

/**
 * Populate the Order Details modal.
 * Each item row shows "Item Name · Variant" when a variant_name exists.
 */
function populateOrderDetailsModal(order, items, payment) {
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
    setHTML('detailStatus',    getOrderStatusBadge(order.status));

    const tableWrap = document.getElementById('detailTableWrap');
    const tableEl   = document.getElementById('detailTable');
    if (order.table_name) {
        if (tableEl)   tableEl.innerHTML       = `<span class="table-number-badge"><i class="fas fa-chair"></i> ${escapeHtml(order.table_name)}</span>`;
        if (tableWrap) tableWrap.style.display = '';
    } else {
        if (tableWrap) tableWrap.style.display = 'none';
    }

    const itemsBody = document.getElementById('detailItemsBody');
    if (itemsBody) {
        itemsBody.innerHTML = '';
        items.forEach(item => {
            const itemSubtotal = (parseFloat(item.unit_price || 0) * parseInt(item.quantity || 1)).toFixed(2);
            const notePill     = (item.item_notes && item.item_notes.trim())
                ? `<div class="od-note-pill"><i class="fas fa-pen-to-square"></i> ${escapeHtml(item.item_notes)}</div>` : '';

            // Resolve display name — prefer snapshots stored at order time, fall back to current variant name
            const displayItemName    = item.item_name_snapshot    || item.item_name    || '';
            const displayVariantName = item.variant_name_snapshot || item.current_variant_name || item.variant_name || null;
            const variantBit         = displayVariantName
                ? `<span class="od-variant-label"> · ${escapeHtml(displayVariantName)}</span>`
                : '';

            // Show variant price info if current variant price differs from order price
            let priceInfo = `₱${formatPrice(item.unit_price)}`;
            if (item.current_variant_price && parseFloat(item.current_variant_price) !== parseFloat(item.unit_price)) {
                const currentPrice = parseFloat(item.current_variant_price);
                const orderPrice = parseFloat(item.unit_price);
                const priceDiff = currentPrice - orderPrice;
                const diffClass = priceDiff > 0 ? 'price-up' : 'price-down';
                const diffText = priceDiff > 0 ? '+' : '';
                priceInfo = `₱${formatPrice(item.unit_price)} <small class="od-price-compare ${diffClass}" title="Current variant price: ₱${formatPrice(currentPrice)}">(${diffText}₱${formatPrice(Math.abs(priceDiff))})</small>`;
            }

            const tr = document.createElement('tr');
            tr.className = 'od-item-row';
            tr.innerHTML = `
                <td class="od-td od-td-item">
                    <span class="od-item-name">${escapeHtml(displayItemName)}${variantBit}</span>
                    ${notePill}
                </td>
                <td class="od-td od-td-center">${item.quantity || 1}</td>
                <td class="od-td od-td-right od-muted">${priceInfo}</td>
                <td class="od-td od-td-right od-item-subtotal">₱${itemSubtotal}</td>`;
            itemsBody.appendChild(tr);

            // Add-on rows
            (item.addons || []).forEach(addon => {
                const addonSubtotal = (parseFloat(addon.unit_price || 0) * parseInt(addon.quantity || 1)).toFixed(2);
                const addonTr = document.createElement('tr');
                addonTr.className = 'od-addon-row';
                addonTr.innerHTML = `
                    <td class="od-td od-td-addon"><i class="fas fa-corner-down-right od-addon-icon"></i>${escapeHtml(addon.addon_name)}</td>
                    <td class="od-td od-td-center od-muted">${addon.quantity || 1}</td>
                    <td class="od-td od-td-right od-muted">₱${formatPrice(addon.unit_price)}</td>
                    <td class="od-td od-td-right od-muted">₱${addonSubtotal}</td>`;
                itemsBody.appendChild(addonTr);
            });
        });
    }

    const total    = parseFloat(order.total_amount || 0);
    const subtotal = total / 1.12;
    const vat      = total - subtotal;
    set('detailSubtotal', subtotal.toFixed(2));
    set('detailVat',      vat.toFixed(2));
    set('detailTotal',    total.toFixed(2));

    const totalDisplay = document.getElementById('detailTotalDisplay');
    if (totalDisplay) totalDisplay.style.color = order.status === 'completed' ? 'var(--success)' : 'var(--danger)';

    const paySection = document.getElementById('detailPaymentSection');
    if (paySection) {
        if (payment && order.status === 'completed') {
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
        } else {
            paySection.style.display = 'none';
        }
    }

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
// PAYMENT + CANCEL MODALS
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

function showCancelOrderModal(orderId, orderNumber) {
    if (!cancelOrderModal) return;
    document.getElementById('cancelOrderNumber').textContent = orderNumber || '';
    document.getElementById('cancelOrderId').value           = orderId || '';
    cancelOrderModal.show();
}

// ============================================
// EVENT LISTENERS
// ============================================
function setupEventListeners() {
    // Use shared sidebar utility
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
        if (e.target.closest('.item-status-btn')) {
            const btn = e.target.closest('.item-status-btn');
            if (btn.disabled) return;
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
                else insufAlert?.classList.add('pm-hidden');
                if (confirmBtn) confirmBtn.disabled = true;
            }
        });
    }

    document.addEventListener('visibilitychange', () => {
        document.hidden ? stopAutoRefresh() : startAutoRefresh();
    });

    // Use shared modal backdrop cleanup utility
    setupModalBackdropCleanup();

    ['successModal', 'errorModal'].forEach(id => {
        const el = document.getElementById(id);
        if (el && typeof bootstrap !== 'undefined') {
            const m = new bootstrap.Modal(el);
            m.show();
            setTimeout(() => m.hide(), 3000);
        }
    });
}

// ============================================
// UTILITIES
// ============================================
// Utility functions are now loaded from common.js

window.capitalize = function(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

window.refreshOrders    = refreshOrders;
window.startAutoRefresh = startAutoRefresh;
window.stopAutoRefresh  = stopAutoRefresh;