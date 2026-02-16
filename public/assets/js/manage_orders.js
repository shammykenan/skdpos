/**
 * ============================================
 * COMPLETE ORDERS MANAGEMENT JAVASCRIPT
 * ============================================
 */

// ============================================
// CONFIGURATION
// ============================================
const REFRESH_INTERVAL = 5000; // 5 seconds
const AJAX_URL = '/coffee_shop/app/ajax/get_orders.php';

let currentFilter = 'all';
let currentSearch = '';
let refreshTimer = null;
let isRefreshing = false;
let currentOrderTotal = 0;

// Bootstrap modals
let paymentModal, updateStatusModal, cancelOrderModal, orderDetailsModal;

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modals safely
    try {
        const paymentModalEl = document.getElementById('paymentModal');
        const updateModalEl = document.getElementById('updateStatusModal');
        const cancelModalEl = document.getElementById('cancelOrderModal');
        const detailsModalEl = document.getElementById('orderDetailsModal');
        
        if (paymentModalEl && typeof bootstrap !== 'undefined') {
            paymentModal = new bootstrap.Modal(paymentModalEl);
        }
        if (updateModalEl && typeof bootstrap !== 'undefined') {
            updateStatusModal = new bootstrap.Modal(updateModalEl);
        }
        if (cancelModalEl && typeof bootstrap !== 'undefined') {
            cancelOrderModal = new bootstrap.Modal(cancelModalEl);
        }
        if (detailsModalEl && typeof bootstrap !== 'undefined') {
            orderDetailsModal = new bootstrap.Modal(detailsModalEl);
        }
    } catch (e) {
        console.error('Error initializing modals:', e);
    }
    
    // Start auto-refresh
    startAutoRefresh();
    
    // Setup event listeners
    setupEventListeners();
    
    // Initial filter
    filterOrders();
    
    console.log('✅ Orders management initialized');
});

// ============================================
// AUTO-REFRESH FUNCTIONS
// ============================================
async function refreshOrders() {
    if (isRefreshing) return;
    
    isRefreshing = true;
    
    try {
        const response = await fetch(AJAX_URL, {
            method: 'GET',
            headers: { 
                'Content-Type': 'application/json',
                'Cache-Control': 'no-cache'
            }
        });
        
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        
        const data = await response.json();
        
        if (!data.success) throw new Error(data.error || 'Unknown error');
        
        updateOrdersTable(data.data || []);
        updateFilterCounts(data.data || []);
        
    } catch (error) {
        console.error('❌ Refresh error:', error);
    } finally {
        isRefreshing = false;
    }
}

function startAutoRefresh() {
    stopAutoRefresh(); // Clear any existing timer
    refreshOrders(); // Immediate refresh
    refreshTimer = setInterval(refreshOrders, REFRESH_INTERVAL);
    console.log('🔄 Auto-refresh started');
}

function stopAutoRefresh() {
    if (refreshTimer) {
        clearInterval(refreshTimer);
        refreshTimer = null;
        console.log('⏸️ Auto-refresh stopped');
    }
}

// ============================================
// TABLE UPDATE FUNCTIONS
// ============================================
function updateOrdersTable(orders) {
    const tbody = document.getElementById('ordersTableBody');
    if (!tbody) return;
    
    if (!orders || orders.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <h3>No Orders Yet</h3>
                        <p>Orders will appear here once customers start placing them.</p>
                    </div>
                </td>
            </tr>`;
        return;
    }
    
    tbody.innerHTML = orders.map(order => createOrderRowHTML(order)).join('');
    
    // Re-apply filters
    filterOrders();
}

function createOrderRowHTML(order) {
    const orderType = getOrderTypeBadge(order.order_type);
    const status = getStatusBadge(order.status);
    const items = getOrderItemsHTML(order.items || []);
    const notes = getOrderNotesHTML(order.items || []);
    const actions = getActionButtonsHTML(order);
    
    // Create search text for filtering
    const searchText = [
        order.order_number || '',
        order.customer_name || 'walk-in',
        order.table_number || ''
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
                <div class="fw-medium" style="color: var(--navy);">${escapeHtml(order.customer_name || 'Walk-in')}</div>
                <small class="text-muted">${formatDate(order.created_at)}</small>
                ${order.table_number ? `
                    <div>
                        <small class="text-muted">
                            <i class="fas fa-chair me-1"></i> Table ${escapeHtml(order.table_number)}
                        </small>
                    </div>` : ''}
            </td>
            
            <td data-label="Order Type">${orderType}</td>
            <td data-label="Items">${items}</td>
            <td data-label="Notes">${notes}</td>
            <td data-label="Subtotal"><span class="price">₱${formatPrice(order.subtotal)}</span></td>
            <td data-label="Total"><span class="price">₱${formatPrice(order.total_amount)}</span></td>
            <td data-label="Status">${status}</td>
            <td data-label="Actions">${actions}</td>
        </tr>`;
}

function getOrderTypeBadge(orderType) {
    if (!orderType) return '<span class="order-type-missing">—</span>';
    
    const types = {
        'dine_in': { icon: 'fa-utensils', label: 'Dine In' },
        'takeout': { icon: 'fa-bag-shopping', label: 'Takeout' }
    };
    
    const type = types[orderType] || { icon: 'fa-question', label: orderType };
    return `<span class="order-type-badge ${orderType}">
        <i class="fas ${type.icon}"></i> ${type.label}
    </span>`;
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
            item.addons.forEach(addon => {
                html += `<li>${escapeHtml(addon.addon_name)} ×${addon.quantity || 1}</li>`;
            });
            html += '</ul>';
        }
        
        html += '</li>';
        return html;
    }).join('') + '</ul>';
}

function getOrderNotesHTML(items) {
    if (!items || items.length === 0) return '<span class="text-muted">—</span>';
    
    let notesHTML = '';
    
    items.forEach(item => {
        if (item.item_notes && item.item_notes.trim() !== '') {
            notesHTML += `
                <div class="order-item-note">
                    <span class="note-item-label">${escapeHtml(item.item_name)}:</span>
                    <span class="note-item-text">${escapeHtml(item.item_notes)}</span>
                </div>
            `;
        }
    });
    
    return notesHTML || '<span class="text-muted">—</span>';
}

function getActionButtonsHTML(order) {
    const userRole = window.userRole || '';
    const orderId = order.order_id;
    const orderNumber = order.order_number || '';
    const currentStatus = order.status || 'pending';
    
    let html = `<div class="action-buttons">
        <button type="button"
                class="btn btn-sm view-order-btn"
                style="background: var(--lavender); color: var(--navy);"
                data-order-id="${orderId}"
                data-order-number="${escapeHtml(orderNumber)}">
            <i class="fas fa-eye"></i> View Details
        </button>`;
    
    if (currentStatus !== 'cancelled' && currentStatus !== 'completed') {
        let canUpdate = false;
        let allowedOptions = [];
        let disabledReason = '';
        
        // Role-based permissions
        if (userRole === 'admin') {
            canUpdate = true;
            allowedOptions = currentStatus === 'pending' ? ['preparing', 'completed'] : ['completed'];
        } else if (userRole === 'kitchen') {
            if (currentStatus === 'pending') {
                canUpdate = true;
                allowedOptions = ['preparing'];
            } else {
                disabledReason = 'Kitchen can only start pending orders';
            }
        } else if (userRole === 'cashier') {
            if (currentStatus === 'preparing') {
                canUpdate = true;
                allowedOptions = ['completed'];
            } else if (currentStatus === 'pending') {
                disabledReason = 'Cashier cannot start orders';
            }
        }
        
        html += `<div class="status-form">`;
        
        if (canUpdate && allowedOptions.length > 0) {
            html += `<select class="form-select status-select"
                        data-order-id="${orderId}"
                        data-current-status="${currentStatus}">`;
            
            if (allowedOptions.includes('preparing')) {
                html += `<option value="preparing" ${currentStatus === 'preparing' ? 'selected' : ''}>Preparing</option>`;
            }
            if (allowedOptions.includes('completed')) {
                html += `<option value="completed" ${currentStatus === 'completed' ? 'selected' : ''}>Completed</option>`;
            }
            
            html += `</select>
                <button type="button"
                        class="btn btn-primary btn-sm update-status-btn"
                        data-order-id="${orderId}"
                        data-order-number="${escapeHtml(orderNumber)}">
                    <i class="fas fa-check"></i> Update
                </button>`;
        } else {
            html += `<button type="button" class="btn btn-secondary btn-sm" disabled
                        title="${escapeHtml(disabledReason)}">
                    <i class="fas fa-ban"></i>
                    ${escapeHtml(disabledReason || 'Action not available')}
                </button>`;
        }
        
        html += `</div>`;
        
        // Cancel button
        if ((userRole === 'admin' || userRole === 'cashier') && currentStatus === 'pending') {
            html += `<button type="button"
                        class="btn btn-danger btn-sm w-100 cancel-order-btn"
                        data-order-id="${orderId}"
                        data-order-number="${escapeHtml(orderNumber)}">
                    <i class="fas fa-times"></i> Cancel Order
                </button>`;
        } else if (currentStatus === 'preparing') {
            html += `<span class="badge bg-warning"
                      style="padding:0.5rem 1rem;border-radius:30px;color:#1a202c;display:inline-block;width:100%;text-align:center;">
                    <i class="fas fa-info-circle me-1"></i> Cannot cancel
                </span>`;
        }
        
    } else if (currentStatus === 'completed') {
        html += `<span class="badge bg-success"
                  style="padding:0.5rem 1rem;border-radius:30px;display:inline-block;width:100%;text-align:center;">
                <i class="fas fa-check-circle me-1"></i> Payment Received
            </span>`;
    } else if (currentStatus === 'cancelled') {
        html += `<span class="badge bg-secondary"
                  style="padding:0.5rem 1rem;border-radius:30px;display:inline-block;width:100%;text-align:center;">
                <i class="fas fa-ban me-1"></i> Order Cancelled
            </span>`;
    }
    
    html += '</div>';
    return html;
}

function updateFilterCounts(orders) {
    const counts = {
        all: orders.length,
        pending: orders.filter(o => o.status === 'pending').length,
        preparing: orders.filter(o => o.status === 'preparing').length,
        completed: orders.filter(o => o.status === 'completed').length,
        cancelled: orders.filter(o => o.status === 'cancelled').length
    };
    
    Object.keys(counts).forEach(status => {
        const tab = document.querySelector(`.filter-tab[data-status="${status}"]`);
        if (tab) {
            const countSpan = tab.querySelector('.filter-count');
            if (countSpan) countSpan.textContent = counts[status];
        }
    });
}

// ============================================
// FILTERING AND SEARCH
// ============================================
function filterOrders() {
    const rows = document.querySelectorAll('#ordersTableBody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        const searchText = row.getAttribute('data-search-text') || '';
        
        const statusMatch = currentFilter === 'all' || status === currentFilter;
        const searchMatch = currentSearch === '' || searchText.includes(currentSearch);
        
        if (statusMatch && searchMatch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    const table = document.querySelector('.orders-table');
    const noResults = document.getElementById('noResultsMessage');
    
    if (table && noResults) {
        const totalRows = document.querySelectorAll('#ordersTableBody tr').length;
        if (visibleCount === 0 && totalRows > 0) {
            table.style.display = 'none';
            noResults.classList.remove('d-none');
        } else {
            table.style.display = '';
            noResults.classList.add('d-none');
        }
    }
    
    // Update filter label
    updateFilterLabel();
}

function updateFilterLabel() {
    const labelMap = {
        'all': 'All Orders',
        'pending': 'Pending Orders',
        'preparing': 'Preparing Orders',
        'completed': 'Completed Orders',
        'cancelled': 'Cancelled Orders'
    };
    
    const labelEl = document.getElementById('currentFilterLabel');
    if (labelEl) labelEl.textContent = labelMap[currentFilter] || 'Orders';
}

// ============================================
// MODAL FUNCTIONS
// ============================================
function showPaymentModal(orderId, orderNumber, orderTotal) {
    if (!paymentModal) return;
    
    currentOrderTotal = orderTotal || 0;
    
    document.getElementById('paymentOrderNumber').textContent = orderNumber || '';
    document.getElementById('paymentTotalAmount').textContent = currentOrderTotal.toFixed(2);
    document.getElementById('paymentOrderId').value = orderId || '';
    document.getElementById('amountPaid').value = '';
    document.getElementById('changeAlert').style.display = 'none';
    document.getElementById('insufficientAlert').style.display = 'none';
    document.getElementById('confirmPaymentBtn').disabled = true;
    
    paymentModal.show();
}

function showUpdateStatusModal(orderId, orderNumber, newStatus) {
    if (!updateStatusModal) return;
    
    document.getElementById('updateOrderNumber').textContent = orderNumber || '';
    document.getElementById('updateNewStatus').textContent = capitalize(newStatus || '');
    document.getElementById('updateOrderId').value = orderId || '';
    document.getElementById('updateStatusValue').value = newStatus || '';
    
    updateStatusModal.show();
}

function showCancelOrderModal(orderId, orderNumber) {
    if (!cancelOrderModal) return;
    
    document.getElementById('cancelOrderNumber').textContent = orderNumber || '';
    document.getElementById('cancelOrderId').value = orderId || '';
    
    cancelOrderModal.show();
}

function viewOrderDetails(orderId, orderNumber) {
    if (!orderDetailsModal) return;
    
    const loaderEl = document.getElementById('orderDetailsLoader');
    const contentEl = document.getElementById('orderDetailsContent');
    const errorEl = document.getElementById('orderDetailsError');
    
    if (loaderEl) loaderEl.style.display = 'block';
    if (contentEl) contentEl.style.display = 'none';
    if (errorEl) errorEl.style.display = 'none';
    
    const detailOrderNumber = document.getElementById('detailOrderNumber');
    if (detailOrderNumber) detailOrderNumber.textContent = '#' + (orderNumber || '');
    
    const printBtn = document.getElementById('printReceiptBtn');
    if (printBtn) printBtn.style.display = 'none';
    
    orderDetailsModal.show();
    
    // Fetch order details
    fetch(`/coffee_shop/app/ajax/get_order_details.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(res => {
            if (loaderEl) loaderEl.style.display = 'none';
            
            if (!res.success) {
                if (errorEl) errorEl.style.display = 'block';
                throw new Error(res.error || 'Failed to load order details');
            }
            
            populateOrderDetailsModal(res.data.order || {}, res.data.items || [], res.data.payment || {});
            if (contentEl) contentEl.style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading order details:', error);
            if (loaderEl) loaderEl.style.display = 'none';
            if (errorEl) errorEl.style.display = 'block';
        });
}

function populateOrderDetailsModal(order, items, payment) {
    // Meta information
    const customerEl = document.getElementById('detailCustomer');
    if (customerEl) customerEl.textContent = order.customer_name || 'Walk-in';
    
    const dateEl = document.getElementById('detailDate');
    if (dateEl && order.created_at) {
        dateEl.textContent = new Date(order.created_at).toLocaleString('en-PH', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }
    
    const cashierEl = document.getElementById('detailCashier');
    if (cashierEl) cashierEl.textContent = order.cashier_name || 'N/A';
    
    const orderTypeEl = document.getElementById('detailOrderType');
    if (orderTypeEl) orderTypeEl.innerHTML = order.order_type ? getOrderTypeBadge(order.order_type) : '—';
    
    const statusEl = document.getElementById('detailStatus');
    if (statusEl) statusEl.innerHTML = getStatusBadge(order.status);
    
    const tableWrap = document.getElementById('detailTableWrap');
    const tableEl = document.getElementById('detailTable');
    if (order.table_number) {
        if (tableEl) tableEl.textContent = 'Table ' + order.table_number;
        if (tableWrap) tableWrap.style.display = '';
    } else {
        if (tableWrap) tableWrap.style.display = 'none';
    }
    
    // Items
    const itemsBody = document.getElementById('detailItemsBody');
    if (itemsBody) {
        itemsBody.innerHTML = '';
        
        items.forEach(item => {
            const itemSubtotal = (parseFloat(item.unit_price || 0) * parseInt(item.quantity || 1)).toFixed(2);
            
            const notePill = (item.item_notes && item.item_notes.trim())
                ? `<div class="detail-note-pill">
                       <i class="fas fa-pen-to-square"></i>
                       ${escapeHtml(item.item_notes)}
                   </div>`
                : '';
            
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #edf2f7';
            tr.innerHTML = `
                <td style="padding: 0.85rem 1rem; vertical-align: top;">
                    <span style="font-weight: 600; color: var(--navy);">${escapeHtml(item.item_name)}</span>
                    ${notePill}
                </td>
                <td style="padding: 0.85rem 1rem; text-align: center; color: var(--warm-gray);">
                    ${item.quantity || 1}
                </td>
                <td style="padding: 0.85rem 1rem; text-align: right; color: var(--warm-gray);">
                    ₱${formatPrice(item.unit_price)}
                </td>
                <td style="padding: 0.85rem 1rem; text-align: right; font-weight: 700; color: var(--success);">
                    ₱${itemSubtotal}
                </td>`;
            itemsBody.appendChild(tr);
            
            // Add-ons
            if (item.addons && item.addons.length > 0) {
                item.addons.forEach(addon => {
                    const addonSubtotal = (parseFloat(addon.unit_price || 0) * parseInt(addon.quantity || 1)).toFixed(2);
                    const addonTr = document.createElement('tr');
                    addonTr.innerHTML = `
                        <td style="padding: 0.5rem 1rem 0.5rem 2rem; border-bottom: 1px dashed #edf2f7; color: var(--warm-gray); font-size: 0.82rem;">
                            <i class="fas fa-level-up-alt fa-rotate-90 me-1" style="color: var(--primary-light); font-size: 0.7rem;"></i>
                            ${escapeHtml(addon.addon_name)}
                        </td>
                        <td style="padding: 0.5rem 1rem; border-bottom: 1px dashed #edf2f7; text-align: center; color: var(--warm-gray); font-size: 0.82rem;">
                            ${addon.quantity || 1}
                        </td>
                        <td style="padding: 0.5rem 1rem; border-bottom: 1px dashed #edf2f7; text-align: right; color: var(--warm-gray); font-size: 0.82rem;">
                            ₱${formatPrice(addon.unit_price)}
                        </td>
                        <td style="padding: 0.5rem 1rem; border-bottom: 1px dashed #edf2f7; text-align: right; color: var(--warm-gray); font-size: 0.82rem;">
                            ₱${addonSubtotal}
                        </td>`;
                    itemsBody.appendChild(addonTr);
                });
            }
        });
    }
    
    // Totals
    const total = parseFloat(order.total_amount || 0);
    const subtotal = total / 1.12;
    const vat = total - subtotal;
    
    const subtotalEl = document.getElementById('detailSubtotal');
    if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2);
    
    const vatEl = document.getElementById('detailVat');
    if (vatEl) vatEl.textContent = vat.toFixed(2);
    
    const totalEl = document.getElementById('detailTotal');
    if (totalEl) totalEl.textContent = total.toFixed(2);
    
    // Payment
    const paymentSection = document.getElementById('detailPaymentSection');
    if (paymentSection) {
        if (payment && order.status === 'completed') {
            paymentSection.style.display = '';
            
            const payMethodEl = document.getElementById('detailPayMethod');
            if (payMethodEl) payMethodEl.textContent = payment.payment_method || '—';
            
            const amountPaidEl = document.getElementById('detailAmountPaid');
            if (amountPaidEl) amountPaidEl.textContent = formatPrice(payment.amount_paid);
            
            const changeEl = document.getElementById('detailChange');
            if (changeEl) changeEl.textContent = formatPrice(payment.change_amount);
        } else {
            paymentSection.style.display = 'none';
        }
    }
    
    // Print button
    const printBtn = document.getElementById('printReceiptBtn');
    if (printBtn) {
        if (order.status === 'completed' && order.order_id) {
            printBtn.href = `${window.location.pathname}?page=print_receipt&order_id=${order.order_id}`;
            printBtn.style.display = '';
        } else {
            printBtn.style.display = 'none';
        }
    }
}

// ============================================
// EVENT DELEGATION
// ============================================
function setupEventListeners() {
    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (menuToggle && sidebar && sidebarOverlay) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.style.overflow = '';
        });
    }
    
    // Search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentSearch = this.value.toLowerCase().trim();
            filterOrders();
        });
    }
    
    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-status') || 'all';
            filterOrders();
        });
    });
    
    // Global click delegation for dynamic buttons
    document.addEventListener('click', function(e) {
        // View order details
        if (e.target.closest('.view-order-btn')) {
            const btn = e.target.closest('.view-order-btn');
            viewOrderDetails(btn.getAttribute('data-order-id'), btn.getAttribute('data-order-number'));
        }
        
        // Update status button
        if (e.target.closest('.update-status-btn')) {
            const btn = e.target.closest('.update-status-btn');
            const row = btn.closest('tr');
            const select = row.querySelector('.status-select');
            const newStatus = select ? select.value : null;
            const currentStatus = select ? select.getAttribute('data-current-status') : null;
            
            if (!newStatus || newStatus === currentStatus) return;
            
            if (newStatus === 'completed') {
                showPaymentModal(
                    btn.getAttribute('data-order-id'),
                    btn.getAttribute('data-order-number'),
                    parseFloat(row.getAttribute('data-order-total') || 0)
                );
            } else {
                showUpdateStatusModal(
                    btn.getAttribute('data-order-id'),
                    btn.getAttribute('data-order-number'),
                    newStatus
                );
            }
        }
        
        // Cancel order button
        if (e.target.closest('.cancel-order-btn')) {
            const btn = e.target.closest('.cancel-order-btn');
            showCancelOrderModal(btn.getAttribute('data-order-id'), btn.getAttribute('data-order-number'));
        }
    });
    
    // Payment amount calculation
    const amountPaid = document.getElementById('amountPaid');
    if (amountPaid) {
        amountPaid.addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            const changeAlert = document.getElementById('changeAlert');
            const insufficientAlert = document.getElementById('insufficientAlert');
            const confirmBtn = document.getElementById('confirmPaymentBtn');
            
            if (amount >= currentOrderTotal) {
                const change = amount - currentOrderTotal;
                document.getElementById('changeAmount').textContent = change.toFixed(2);
                document.getElementById('changeAmountHidden').value = change.toFixed(2);
                if (changeAlert) changeAlert.style.display = 'flex';
                if (insufficientAlert) insufficientAlert.style.display = 'none';
                if (confirmBtn) confirmBtn.disabled = false;
            } else {
                if (changeAlert) changeAlert.style.display = 'none';
                if (insufficientAlert) insufficientAlert.style.display = 'flex';
                if (confirmBtn) confirmBtn.disabled = true;
            }
        });
    }
    
    // Pause refresh when tab is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
    
    // Modal cleanup
    document.addEventListener('hidden.bs.modal', function() {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
    
    // Show flash modals
    const successModal = document.getElementById('successModal');
    if (successModal && typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(successModal);
        modal.show();
        setTimeout(() => modal.hide(), 3000);
    }
    
    const errorModal = document.getElementById('errorModal');
    if (errorModal && typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(errorModal);
        modal.show();
        setTimeout(() => modal.hide(), 3000);
    }
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
            month: 'short', day: 'numeric', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    } catch {
        return '';
    }
}

function formatPrice(amount) {
    const num = parseFloat(amount || 0);
    return isNaN(num) ? '0.00' : num.toFixed(2);
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

// ============================================
// EXPORT FUNCTIONS TO GLOBAL SCOPE
// ============================================
window.refreshOrders = refreshOrders;
window.startAutoRefresh = startAutoRefresh;
window.stopAutoRefresh = stopAutoRefresh;
window.filterOrders = filterOrders;

console.log('✅ Orders management script fully loaded');