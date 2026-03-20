/**
 * ============================================
 * ORDERS MANAGEMENT — Pagination + AJAX
 * Orders sorted oldest-first (ASC).
 * 10 rows per page, pagination controls at bottom.
 * ============================================
 */

// ============================================
// CONFIGURATION
// ============================================
const REFRESH_INTERVAL = 5000;
const PAGE_LIMIT       = 10;
const AJAX_URL         = '/coffee_shop/app/ajax/get_orders.php';

// ── State ──────────────────────────────────
let currentFilter     = 'all';
let currentSearch     = '';
let currentPage       = 1;
let totalPages        = 1;
let filteredTotal     = 0;
let isLoading         = false;
let isRefreshing      = false;
let refreshTimer      = null;
let currentOrderTotal = 0;

// Bootstrap modals
let paymentModal, updateStatusModal, cancelOrderModal, orderDetailsModal;

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    try {
        if (typeof bootstrap !== 'undefined') {
            paymentModal      = new bootstrap.Modal(document.getElementById('paymentModal'));
            updateStatusModal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            cancelOrderModal  = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
            orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        }
    } catch (e) {
        console.error('Modal init error:', e);
    }

    setupEventListeners();
    loadPage(1);
    startAutoRefresh();

    console.log('✅ Orders management initialized (pagination)');
});

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

        patchExistingRows(data.data || []);
        updateFilterCounts(data.counts, data.total);

        const newTotal = data.filtered_total || 0;
        if (newTotal !== filteredTotal) {
            const grew = newTotal > filteredTotal;
            filteredTotal = newTotal;
            totalPages    = Math.max(1, Math.ceil(filteredTotal / PAGE_LIMIT));
            renderPagination();
            if (grew) showNewOrdersToast();
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

    tbody.innerHTML = orders.map(order => createOrderRowHTML(order)).join('');
}

function patchExistingRows(orders) {
    orders.forEach(order => {
        const row = document.querySelector(`tr[data-order-id="${order.order_id}"]`);
        if (!row || row.getAttribute('data-status') === order.status) return;
        row.setAttribute('data-status', order.status);
        const statusCell  = row.querySelector('td[data-label="Status"]');
        const actionsCell = row.querySelector('td[data-label="Actions"]');
        if (statusCell)  statusCell.innerHTML  = getStatusBadge(order.status);
        if (actionsCell) actionsCell.innerHTML = getActionButtonsHTML(order);
    });
}

// ============================================
// PAGINATION UI
// ============================================
function renderPagination() {
    // Find or create container placed right after .table-container
    let container = document.getElementById('paginationContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'paginationContainer';
        container.style.cssText = [
            'padding:1rem 1.5rem',
            'border-top:1px solid var(--border)',
            'display:flex',
            'align-items:center',
            'justify-content:space-between',
            'flex-wrap:wrap',
            'gap:0.75rem',
        ].join(';');
        const tableContainer = document.querySelector('.table-container');
        if (tableContainer) tableContainer.after(container);
    }

    // Hide pagination when there is only one page
    if (totalPages <= 1 && filteredTotal <= PAGE_LIMIT) {
        container.style.display = 'none';
        return;
    }
    container.style.display = 'flex';

    const from  = filteredTotal === 0 ? 0 : (currentPage - 1) * PAGE_LIMIT + 1;
    const to    = Math.min(currentPage * PAGE_LIMIT, filteredTotal);

    const infoHTML = `
        <span style="font-size:0.85rem;color:var(--warm-gray);">
            Showing <strong>${from}–${to}</strong> of <strong>${filteredTotal}</strong> orders
        </span>`;

    // Build page number buttons
    let btns = '';

    // Prev button
    btns += pageBtn('<i class="fas fa-chevron-left"></i>', currentPage - 1, currentPage === 1, false);

    // Smart page range: e.g. 1 … 4 5 6 … 12
    getPageRange(currentPage, totalPages).forEach(p => {
        if (p === '...') {
            btns += `<span style="padding:0 4px;color:var(--warm-gray);align-self:center;">…</span>`;
        } else {
            btns += pageBtn(p, p, false, p === currentPage);
        }
    });

    // Next button
    btns += pageBtn('<i class="fas fa-chevron-right"></i>', currentPage + 1, currentPage === totalPages, false);

    container.innerHTML = `
        ${infoHTML}
        <div style="display:flex;align-items:center;gap:0.35rem;">${btns}</div>`;

    // Attach click handlers
    container.querySelectorAll('.page-btn:not([disabled])').forEach(btn => {
        btn.addEventListener('click', () => loadPage(parseInt(btn.dataset.page)));
    });
}

function pageBtn(label, page, disabled, active) {
    const base   = 'class="page-btn" style="border:none;border-radius:8px;width:34px;height:34px;font-size:0.82rem;font-weight:600;cursor:pointer;transition:all .15s;';
    const style  = active   ? 'background:var(--primary);color:#fff;"'
                 : disabled ? 'background:#f0f0f0;color:#bbb;cursor:not-allowed;"'
                             : 'background:var(--light-bg);color:var(--navy);"';
    const dis    = (disabled || active) ? 'disabled' : '';
    return `<button ${base}${style} data-page="${page}" ${dis}>${label}</button>`;
}

function getPageRange(current, total) {
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    if (current <= 4)          return [1, 2, 3, 4, 5, '...', total];
    if (current >= total - 3)  return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
    return [1, '...', current - 1, current, current + 1, '...', total];
}

// ============================================
// FILTER COUNTS & LABEL
// ============================================
function updateFilterCounts(counts, total) {
    const data = {
        all:       total             ?? 0,
        pending:   counts?.pending   ?? 0,
        preparing: counts?.preparing ?? 0,
        completed: counts?.completed ?? 0,
        cancelled: counts?.cancelled ?? 0,
    };
    Object.keys(data).forEach(status => {
        const tab = document.querySelector(`.filter-tab[data-status="${status}"]`);
        if (tab) {
            const span = tab.querySelector('.filter-count');
            if (span) span.textContent = data[status];
        }
    });
}

function updateFilterLabel() {
    const map = {
        all: 'All Orders', pending: 'Pending Orders',
        preparing: 'Preparing Orders', completed: 'Completed Orders', cancelled: 'Cancelled Orders',
    };
    const el = document.getElementById('currentFilterLabel');
    if (el) el.textContent = map[currentFilter] || 'Orders';
}

// ============================================
// SPINNER & ERROR
// ============================================
function showTableSpinner(show) {
    let spinner = document.getElementById('tableSpinner');
    if (show) {
        if (!spinner) {
            spinner = document.createElement('tr');
            spinner.id = 'tableSpinner';
            spinner.innerHTML = `
                <td colspan="9" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm me-2"
                         style="color:var(--primary);" role="status"></div>
                    <span class="text-muted" style="font-size:0.875rem;">Loading orders…</span>
                </td>`;
            const tbody = document.getElementById('ordersTableBody');
            if (tbody) tbody.appendChild(spinner);
        }
    } else {
        if (spinner) spinner.remove();
    }
}

function showError() {
    const tbody = document.getElementById('ordersTableBody');
    if (tbody) tbody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center py-4 text-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                Failed to load orders. Please refresh the page.
            </td>
        </tr>`;
}

// ============================================
// NEW ORDERS TOAST
// ============================================
function showNewOrdersToast() {
    let toast = document.getElementById('newOrdersToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'newOrdersToast';
        toast.style.cssText = [
            'position:fixed', 'bottom:1.5rem', 'right:1.5rem', 'z-index:9999',
            'background:var(--primary,#4a90e2)', 'color:#fff',
            'padding:0.75rem 1.25rem', 'border-radius:12px',
            'box-shadow:0 4px 16px rgba(0,0,0,0.18)',
            'font-size:0.9rem', 'font-weight:600', 'cursor:pointer',
            'display:flex', 'align-items:center', 'gap:0.5rem',
        ].join(';');
        toast.innerHTML = `<i class="fas fa-bell"></i> New orders arrived — click to refresh`;
        toast.addEventListener('click', () => { loadPage(1); toast.remove(); });
        document.body.appendChild(toast);
    }
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.remove(), 8000);
}

// ============================================
// ROW HTML BUILDERS
// ============================================
function createOrderRowHTML(order) {
    const searchText = [
        order.order_number  || '',
        order.customer_name || 'walk-in',
        order.table_number  || '',
    ].join(' ').toLowerCase();

    return `
        <tr data-status="${(order.status || 'pending').toLowerCase()}"
            data-search-text="${escapeHtml(searchText)}"
            data-order-total="${order.total_amount || 0}"
            data-order-id="${order.order_id}"
            data-order-number="${escapeHtml(order.order_number || '')}">
            <td data-label="Order #">
                <span class="order-number">#${escapeHtml(order.order_number || '')}</span>
            </td>
            <td data-label="Customer">
                <div class="fw-medium" style="color:var(--navy);">${escapeHtml(order.customer_name || 'Walk-in')}</div>
                <small class="text-muted">${formatDate(order.created_at)}</small>
                ${order.table_number ? `<div><small class="text-muted"><i class="fas fa-chair me-1"></i>Table ${escapeHtml(order.table_number)}</small></div>` : ''}
            </td>
            <td data-label="Order Type">${getOrderTypeBadge(order.order_type)}</td>
            <td data-label="Items">${getOrderItemsHTML(order.items || [])}</td>
            <td data-label="Notes">${getOrderNotesHTML(order.items || [])}</td>
            <td data-label="Subtotal"><span class="price">₱${formatPrice(order.subtotal)}</span></td>
            <td data-label="Total"><span class="price">₱${formatPrice(order.total_amount)}</span></td>
            <td data-label="Status">${getStatusBadge(order.status)}</td>
            <td data-label="Actions">${getActionButtonsHTML(order)}</td>
        </tr>`;
}

function getOrderTypeBadge(orderType) {
    if (!orderType) return '<span class="order-type-missing">—</span>';
    const types = {
        dine_in: { icon: 'fa-utensils',     label: 'Dine In' },
        takeout: { icon: 'fa-bag-shopping', label: 'Takeout' },
    };
    const t = types[orderType] || { icon: 'fa-question', label: orderType };
    return `<span class="order-type-badge ${orderType}"><i class="fas ${t.icon}"></i> ${t.label}</span>`;
}

function getStatusBadge(status) {
    if (!status) status = 'pending';
    return `<span class="status-badge status-${status.toLowerCase()}">${capitalize(status)}</span>`;
}

function getOrderItemsHTML(items) {
    if (!items || items.length === 0) return '<span class="text-muted">No items</span>';
    return '<ul class="order-items">' + items.map(item => {
        let html = `<li>
            <span class="item-name">${escapeHtml(item.item_name)}</span>
            <span class="item-qty">×${item.quantity || 1}</span>`;
        if (item.addons && item.addons.length > 0) {
            html += '<ul class="addon-list">';
            item.addons.forEach(a => { html += `<li>${escapeHtml(a.addon_name)} ×${a.quantity || 1}</li>`; });
            html += '</ul>';
        }
        return html + '</li>';
    }).join('') + '</ul>';
}

function getOrderNotesHTML(items) {
    if (!items || items.length === 0) return '<span class="text-muted">—</span>';
    const notes = items
        .filter(i => i.item_notes && i.item_notes.trim())
        .map(i => `<div class="order-item-note">
            <span class="note-item-label">${escapeHtml(i.item_name)}:</span>
            <span class="note-item-text">${escapeHtml(i.item_notes)}</span>
        </div>`).join('');
    return notes || '<span class="text-muted">—</span>';
}

function getActionButtonsHTML(order) {
    const userRole    = window.userRole || '';
    const orderId     = order.order_id;
    const orderNumber = order.order_number || '';
    const status      = order.status || 'pending';

    let html = `<div class="action-buttons">
        <button type="button" class="btn btn-sm view-order-btn"
                style="background:var(--lavender);color:var(--navy);"
                data-order-id="${orderId}"
                data-order-number="${escapeHtml(orderNumber)}">
            <i class="fas fa-eye"></i> View Details
        </button>`;

    if (status !== 'cancelled' && status !== 'completed') {
        let canUpdate = false, allowedOptions = [], disabledReason = '';

        if (userRole === 'admin') {
            canUpdate      = true;
            allowedOptions = status === 'pending' ? ['preparing', 'completed'] : ['completed'];
        } else if (userRole === 'kitchen') {
            if (status === 'pending') { canUpdate = true; allowedOptions = ['preparing']; }
            else disabledReason = 'Kitchen can only start pending orders';
        } else if (userRole === 'cashier') {
            if (status === 'preparing') { canUpdate = true; allowedOptions = ['completed']; }
            else if (status === 'pending') disabledReason = 'Cashier cannot start orders';
        }

        html += `<div class="status-form">`;
        if (canUpdate && allowedOptions.length > 0) {
            html += `<select class="form-select status-select"
                        data-order-id="${orderId}" data-current-status="${status}">`;
            if (allowedOptions.includes('preparing'))
                html += `<option value="preparing" ${status === 'preparing' ? 'selected' : ''}>Preparing</option>`;
            if (allowedOptions.includes('completed'))
                html += `<option value="completed" ${status === 'completed' ? 'selected' : ''}>Completed</option>`;
            html += `</select>
                <button type="button" class="btn btn-primary btn-sm update-status-btn"
                        data-order-id="${orderId}"
                        data-order-number="${escapeHtml(orderNumber)}">
                    <i class="fas fa-check"></i> Update
                </button>`;
        } else {
            html += `<button type="button" class="btn btn-secondary btn-sm" disabled
                        title="${escapeHtml(disabledReason)}">
                    <i class="fas fa-ban"></i> ${escapeHtml(disabledReason || 'Action not available')}
                </button>`;
        }
        html += `</div>`;

        if ((userRole === 'admin' || userRole === 'cashier') && status === 'pending') {
            html += `<button type="button" class="btn btn-danger btn-sm w-100 cancel-order-btn"
                        data-order-id="${orderId}"
                        data-order-number="${escapeHtml(orderNumber)}">
                    <i class="fas fa-times"></i> Cancel Order
                </button>`;
        } else if (status === 'preparing') {
            html += `<span class="badge bg-warning"
                      style="padding:0.5rem 1rem;border-radius:30px;color:#1a202c;display:inline-block;width:100%;text-align:center;">
                    <i class="fas fa-info-circle me-1"></i> Cannot cancel
                </span>`;
        }

    } else if (status === 'completed') {
        html += `<span class="badge bg-success"
                  style="padding:0.5rem 1rem;border-radius:30px;display:inline-block;width:100%;text-align:center;">
                <i class="fas fa-check-circle me-1"></i> Payment Received
            </span>`;
    } else {
        html += `<span class="badge bg-secondary"
                  style="padding:0.5rem 1rem;border-radius:30px;display:inline-block;width:100%;text-align:center;">
                <i class="fas fa-ban me-1"></i> Order Cancelled
            </span>`;
    }

    html += '</div>';
    return html;
}

// ============================================
// MODAL FUNCTIONS
// ============================================
function showPaymentModal(orderId, orderNumber, orderTotal) {
    if (!paymentModal) return;
    currentOrderTotal = orderTotal || 0;
    document.getElementById('paymentOrderNumber').textContent  = orderNumber || '';
    document.getElementById('paymentTotalAmount').textContent  = currentOrderTotal.toFixed(2);
    document.getElementById('paymentOrderId').value            = orderId || '';
    document.getElementById('amountPaid').value                = '';
    document.getElementById('changeAlert').style.display       = 'none';
    document.getElementById('insufficientAlert').style.display = 'none';
    document.getElementById('confirmPaymentBtn').disabled      = true;
    paymentModal.show();
}

function showUpdateStatusModal(orderId, orderNumber, newStatus) {
    if (!updateStatusModal) return;
    document.getElementById('updateOrderNumber').textContent = orderNumber || '';
    document.getElementById('updateNewStatus').textContent   = capitalize(newStatus || '');
    document.getElementById('updateOrderId').value           = orderId || '';
    document.getElementById('updateStatusValue').value       = newStatus || '';
    updateStatusModal.show();
}

function showCancelOrderModal(orderId, orderNumber) {
    if (!cancelOrderModal) return;
    document.getElementById('cancelOrderNumber').textContent = orderNumber || '';
    document.getElementById('cancelOrderId').value           = orderId || '';
    cancelOrderModal.show();
}

function viewOrderDetails(orderId, orderNumber) {
    if (!orderDetailsModal) return;
    const loaderEl  = document.getElementById('orderDetailsLoader');
    const contentEl = document.getElementById('orderDetailsContent');
    const errorEl   = document.getElementById('orderDetailsError');

    if (loaderEl)  loaderEl.style.display  = 'block';
    if (contentEl) contentEl.style.display = 'none';
    if (errorEl)   errorEl.style.display   = 'none';

    const detailOrderNumber = document.getElementById('detailOrderNumber');
    if (detailOrderNumber) detailOrderNumber.textContent = '#' + (orderNumber || '');

    const printBtn = document.getElementById('printReceiptBtn');
    if (printBtn) printBtn.style.display = 'none';

    orderDetailsModal.show();

    fetch(`/coffee_shop/app/ajax/get_order_details.php?order_id=${orderId}`)
        .then(r => r.json())
        .then(res => {
            if (loaderEl) loaderEl.style.display = 'none';
            if (!res.success) { if (errorEl) errorEl.style.display = 'block'; return; }
            populateOrderDetailsModal(res.data.order || {}, res.data.items || [], res.data.payment || {});
            if (contentEl) contentEl.style.display = 'block';
        })
        .catch(() => {
            if (loaderEl) loaderEl.style.display = 'none';
            if (errorEl)  errorEl.style.display  = 'block';
        });
}

function populateOrderDetailsModal(order, items, payment) {
    const set     = (id, val)  => { const el = document.getElementById(id); if (el) el.textContent = val; };
    const setHTML = (id, html) => { const el = document.getElementById(id); if (el) el.innerHTML  = html; };

    set('detailCustomer', order.customer_name || 'Walk-in');
    if (order.created_at) {
        set('detailDate', new Date(order.created_at).toLocaleString('en-PH', {
            year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'
        }));
    }
    set('detailCashier', order.cashier_name || 'N/A');
    setHTML('detailOrderType', order.order_type ? getOrderTypeBadge(order.order_type) : '—');
    setHTML('detailStatus', getStatusBadge(order.status));

    const tableWrap = document.getElementById('detailTableWrap');
    const tableEl   = document.getElementById('detailTable');
    if (order.table_number) {
        if (tableEl)   tableEl.textContent     = 'Table ' + order.table_number;
        if (tableWrap) tableWrap.style.display = '';
    } else {
        if (tableWrap) tableWrap.style.display = 'none';
    }

    const itemsBody = document.getElementById('detailItemsBody');
    if (itemsBody) {
        itemsBody.innerHTML = '';
        items.forEach(item => {
            const itemSubtotal = (parseFloat(item.unit_price || 0) * parseInt(item.quantity || 1)).toFixed(2);
            const notePill = (item.item_notes && item.item_notes.trim())
                ? `<div class="detail-note-pill"><i class="fas fa-pen-to-square"></i> ${escapeHtml(item.item_notes)}</div>`
                : '';
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #edf2f7';
            tr.innerHTML = `
                <td style="padding:.85rem 1rem;vertical-align:top;">
                    <span style="font-weight:600;color:var(--navy);">${escapeHtml(item.item_name)}</span>${notePill}
                </td>
                <td style="padding:.85rem 1rem;text-align:center;color:var(--warm-gray);">${item.quantity || 1}</td>
                <td style="padding:.85rem 1rem;text-align:right;color:var(--warm-gray);">₱${formatPrice(item.unit_price)}</td>
                <td style="padding:.85rem 1rem;text-align:right;font-weight:700;color:var(--success);">₱${itemSubtotal}</td>`;
            itemsBody.appendChild(tr);

            (item.addons || []).forEach(addon => {
                const addonSubtotal = (parseFloat(addon.unit_price || 0) * parseInt(addon.quantity || 1)).toFixed(2);
                const addonTr = document.createElement('tr');
                addonTr.innerHTML = `
                    <td style="padding:.5rem 1rem .5rem 2rem;border-bottom:1px dashed #edf2f7;color:var(--warm-gray);font-size:.82rem;">
                        <i class="fas fa-level-up-alt fa-rotate-90 me-1" style="color:var(--primary-light);font-size:.7rem;"></i>
                        ${escapeHtml(addon.addon_name)}
                    </td>
                    <td style="padding:.5rem 1rem;border-bottom:1px dashed #edf2f7;text-align:center;color:var(--warm-gray);font-size:.82rem;">${addon.quantity || 1}</td>
                    <td style="padding:.5rem 1rem;border-bottom:1px dashed #edf2f7;text-align:right;color:var(--warm-gray);font-size:.82rem;">₱${formatPrice(addon.unit_price)}</td>
                    <td style="padding:.5rem 1rem;border-bottom:1px dashed #edf2f7;text-align:right;color:var(--warm-gray);font-size:.82rem;">₱${addonSubtotal}</td>`;
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

    const paySection = document.getElementById('detailPaymentSection');
    if (paySection) {
        if (payment && order.status === 'completed') {
            paySection.style.display = '';
            set('detailPayMethod',  payment.payment_method || '—');
            set('detailAmountPaid', formatPrice(payment.amount_paid));
            set('detailChange',     formatPrice(payment.change_amount));
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
// EVENT LISTENERS
// ============================================
function setupEventListeners() {
    // Mobile sidebar
    const menuToggle     = document.getElementById('menuToggle');
    const sidebar        = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    if (menuToggle && sidebar && sidebarOverlay) {
        menuToggle.addEventListener('click', e => {
            e.preventDefault();
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        });
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.style.overflow = '';
        });
    }

    // Search — debounced 300 ms, resets to page 1
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let debounce;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounce);
            debounce = setTimeout(() => {
                currentSearch = this.value.toLowerCase().trim();
                loadPage(1);
            }, 300);
        });
    }

    // Filter tabs — reset to page 1
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-status') || 'all';
            loadPage(1);
        });
    });

    // Event delegation for dynamic buttons
    document.addEventListener('click', function (e) {
        if (e.target.closest('.view-order-btn')) {
            const btn = e.target.closest('.view-order-btn');
            viewOrderDetails(btn.dataset.orderId, btn.dataset.orderNumber);
        }

        if (e.target.closest('.update-status-btn')) {
            const btn           = e.target.closest('.update-status-btn');
            const row           = btn.closest('tr');
            const select        = row.querySelector('.status-select');
            const newStatus     = select?.value;
            const currentStatus = select?.getAttribute('data-current-status');
            if (!newStatus || newStatus === currentStatus) return;

            if (newStatus === 'completed') {
                showPaymentModal(btn.dataset.orderId, btn.dataset.orderNumber,
                    parseFloat(row.getAttribute('data-order-total') || 0));
            } else {
                showUpdateStatusModal(btn.dataset.orderId, btn.dataset.orderNumber, newStatus);
            }
        }

        if (e.target.closest('.cancel-order-btn')) {
            const btn = e.target.closest('.cancel-order-btn');
            showCancelOrderModal(btn.dataset.orderId, btn.dataset.orderNumber);
        }
    });

    // Payment amount calculation
    const amountPaid = document.getElementById('amountPaid');
    if (amountPaid) {
        amountPaid.addEventListener('input', function () {
            const amount      = parseFloat(this.value) || 0;
            const changeAlert = document.getElementById('changeAlert');
            const insufAlert  = document.getElementById('insufficientAlert');
            const confirmBtn  = document.getElementById('confirmPaymentBtn');

            if (amount >= currentOrderTotal) {
                const change = amount - currentOrderTotal;
                document.getElementById('changeAmount').textContent = change.toFixed(2);
                document.getElementById('changeAmountHidden').value = change.toFixed(2);
                if (changeAlert) changeAlert.style.display = 'flex';
                if (insufAlert)  insufAlert.style.display  = 'none';
                if (confirmBtn)  confirmBtn.disabled       = false;
            } else {
                if (changeAlert) changeAlert.style.display = 'none';
                if (insufAlert)  insufAlert.style.display  = 'flex';
                if (confirmBtn)  confirmBtn.disabled       = true;
            }
        });
    }

    // Pause refresh when tab is hidden
    document.addEventListener('visibilitychange', () => {
        document.hidden ? stopAutoRefresh() : startAutoRefresh();
    });

    // Modal backdrop cleanup
    document.addEventListener('hidden.bs.modal', () => {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow     = '';
        document.body.style.paddingRight = '';
    });

    // Flash modals (success / error from PHP session)
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
// UTILITY FUNCTIONS
// ============================================
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '';
    try {
        return new Date(dateString).toLocaleString('en-US', {
            month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit'
        });
    } catch { return ''; }
}

function formatPrice(amount) {
    const n = parseFloat(amount || 0);
    return isNaN(n) ? '0.00' : n.toFixed(2);
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

// Globals
window.refreshOrders    = refreshOrders;
window.startAutoRefresh = startAutoRefresh;
window.stopAutoRefresh  = stopAutoRefresh;

console.log('✅ Orders management script fully loaded (pagination)');