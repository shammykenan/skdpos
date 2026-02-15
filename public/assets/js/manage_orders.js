/**
 * ============================================
 * COMPLETE ORDERS MANAGEMENT JAVASCRIPT
 * ============================================
 * 
 * Includes:
 * - Auto-refresh with AJAX
 * - Order filtering and search
 * - Modal management (View, Update, Cancel, Payment)
 * - Event delegation for dynamic content
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

// ============================================
// BOOTSTRAP MODALS INITIALIZATION
// ============================================
let paymentModal, updateStatusModal, cancelOrderModal, orderDetailsModal;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modals
    const paymentModalEl = document.getElementById('paymentModal');
    const updateModalEl = document.getElementById('updateStatusModal');
    const cancelModalEl = document.getElementById('cancelOrderModal');
    const detailsModalEl = document.getElementById('orderDetailsModal');
    
    if (paymentModalEl) paymentModal = new bootstrap.Modal(paymentModalEl);
    if (updateModalEl) updateStatusModal = new bootstrap.Modal(updateModalEl);
    if (cancelModalEl) cancelOrderModal = new bootstrap.Modal(cancelModalEl);
    if (detailsModalEl) orderDetailsModal = new bootstrap.Modal(detailsModalEl);
    
    // Start auto-refresh
    startAutoRefresh();
    
    // Setup other event listeners
    setupEventListeners();
});

// ============================================
// AUTO-REFRESH FUNCTIONALITY
// ============================================

/**
 * Main refresh function
 */
async function refreshOrders() {
    if (isRefreshing) {
        console.log('Already refreshing, skipping...');
        return;
    }
    
    isRefreshing = true;
    showRefreshIndicator();
    
    try {
        const response = await fetch(AJAX_URL, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Unknown error occurred');
        }
        
        // Update the table
        updateOrdersTable(data.data);
        
        // Update filter counts
        updateFilterCounts(data.data);
        
        // Update last refresh time
        updateLastRefreshTime();
        
        console.log(`✅ Orders refreshed: ${data.count} orders at ${data.timestamp}`);
        
        hideRefreshIndicator();
        
    } catch (error) {
        console.error('❌ Refresh error:', error);
        hideRefreshIndicator();
    } finally {
        isRefreshing = false;
    }
}

/**
 * Update the orders table
 */
function updateOrdersTable(orders) {
    const tbody = document.getElementById('ordersTableBody');
    if (!tbody) {
        console.error('Orders table body not found');
        return;
    }
    
    if (orders.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <h3>No Orders Yet</h3>
                        <p>Orders will appear here once customers start placing them.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    // Build HTML for all rows
    tbody.innerHTML = orders.map(order => createOrderRowHTML(order)).join('');
    
    // Re-apply current filters
    filterOrders();
}

/**
 * Create HTML for a single order row
 */
function createOrderRowHTML(order) {
    const orderType = getOrderTypeBadge(order.order_type);
    const status = getStatusBadge(order.status);
    const items = getOrderItemsHTML(order.items || []);
    const actions = getActionButtonsHTML(order);
    
    return `
        <tr data-status="${order.status.toLowerCase()}" 
            data-search-text="${(order.order_number + ' ' + (order.customer_name || 'walk-in') + ' ' + (order.table_number || '')).toLowerCase()}"
            data-order-total="${order.total_amount}"
            data-order-id="${order.order_id}"
            data-order-number="${escapeHtml(order.order_number)}">
            
            <td data-label="Order #">
                <span class="order-number">#${escapeHtml(order.order_number)}</span>
            </td>
            
            <td data-label="Customer">
                <div class="fw-medium" style="color: var(--navy);">${escapeHtml(order.customer_name || 'Walk-in')}</div>
                <small class="text-muted">${formatDate(order.created_at)}</small>
                ${order.table_number ? `<div><small class="text-muted"><i class="fas fa-chair me-1"></i> Table ${escapeHtml(order.table_number)}</small></div>` : ''}
            </td>
            
            <td data-label="Order Type">${orderType}</td>
            <td data-label="Items">${items}</td>
            <td data-label="Subtotal"><span class="price">₱${formatPrice(order.subtotal)}</span></td>
            <td data-label="Total"><span class="price">₱${formatPrice(order.total_amount)}</span></td>
            <td data-label="Status">${status}</td>
            <td data-label="Actions">${actions}</td>
        </tr>
    `;
}

/**
 * Get order type badge HTML
 */
function getOrderTypeBadge(orderType) {
    if (!orderType) {
        return '<span class="order-type-missing">—</span>';
    }
    
    const types = {
        'dine_in': { icon: 'fa-utensils', label: 'Dine In' },
        'takeout': { icon: 'fa-bag-shopping', label: 'Takeout' }
    };
    
    const type = types[orderType] || { icon: 'fa-question', label: orderType };
    return `<span class="order-type-badge ${orderType}">
        <i class="fas ${type.icon}"></i>
        ${type.label}
    </span>`;
}

/**
 * Get status badge HTML
 */
function getStatusBadge(status) {
    const statusLower = status.toLowerCase();
    return `<span class="status-badge status-${statusLower}">${capitalize(status)}</span>`;
}

/**
 * Get order items HTML
 */
function getOrderItemsHTML(items) {
    if (!items || items.length === 0) {
        return '<span class="text-muted">No items</span>';
    }
    
    const itemsHtml = items.map(item => {
        let html = `<li>
            <span class="item-name">${escapeHtml(item.item_name)}</span>
            <span class="item-qty">×${item.quantity}</span>`;
        
        if (item.addons && item.addons.length > 0) {
            html += '<ul class="addon-list">';
            item.addons.forEach(addon => {
                html += `<li>${escapeHtml(addon.addon_name)} ×${addon.quantity}</li>`;
            });
            html += '</ul>';
        }
        
        html += '</li>';
        return html;
    }).join('');
    
    return `<ul class="order-items">${itemsHtml}</ul>`;
}

/**
 * Get action buttons HTML
 */
function getActionButtonsHTML(order) {
    let html = `<div class="action-buttons">
        <button type="button"
                class="btn btn-sm view-order-btn"
                style="background: var(--lavender); color: var(--navy);"
                data-order-id="${order.order_id}"
                data-order-number="${escapeHtml(order.order_number)}">
            <i class="fas fa-eye"></i> View Details
        </button>`;
    
    if (order.status !== 'cancelled' && order.status !== 'completed') {
        if (order.status === 'pending') {
            html += `
                <div class="status-form">
                    <select class="form-select status-select" data-order-id="${order.order_id}" data-current-status="${order.status}">
                        <option value="preparing">Preparing</option>
                        <option value="completed">Completed</option>
                    </select>
                    <button type="button" class="btn btn-primary btn-sm update-status-btn" 
                            data-order-id="${order.order_id}" 
                            data-order-number="${escapeHtml(order.order_number)}">
                        <i class="fas fa-check"></i> Update
                    </button>
                    <button type="button" class="btn btn-danger btn-sm w-100 cancel-order-btn" 
                            data-order-id="${order.order_id}" 
                            data-order-number="${escapeHtml(order.order_number)}">
                        <i class="fas fa-times"></i> Cancel Order
                    </button>
                </div>`;
        } else if (order.status === 'preparing') {
            html += `
                <div class="status-form">
                    <select class="form-select status-select" data-order-id="${order.order_id}" data-current-status="${order.status}">
                        <option value="completed">Completed</option>
                    </select>
                    <button type="button" class="btn btn-primary btn-sm update-status-btn" 
                            data-order-id="${order.order_id}" 
                            data-order-number="${escapeHtml(order.order_number)}">
                        <i class="fas fa-check"></i> Update
                    </button>
                    <span class="badge bg-warning" style="padding: 0.5rem 1rem; border-radius: 30px; color: #1a202c; margin-top: 0.5rem;">
                        <i class="fas fa-info-circle me-1"></i> Cannot cancel - in preparation
                    </span>
                </div>`;
        }
    } else if (order.status === 'completed') {
        html += `<span class="badge bg-success" style="padding: 0.5rem 1rem; border-radius: 30px;">
            <i class="fas fa-check-circle me-1"></i> Payment Received
        </span>`;
    } else if (order.status === 'cancelled') {
        html += `<span class="badge bg-secondary" style="padding: 0.5rem 1rem; border-radius: 30px;">
            <i class="fas fa-ban me-1"></i> Order Cancelled
        </span>`;
    }
    
    html += '</div>';
    return html;
}

/**
 * Update filter counts
 */
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
            if (countSpan) {
                countSpan.textContent = counts[status];
            }
        }
    });
}

/**
 * Start auto-refresh
 */
function startAutoRefresh() {
    console.log('🔄 Starting auto-refresh...');
    refreshOrders(); // Initial refresh
    refreshTimer = setInterval(refreshOrders, REFRESH_INTERVAL);
}

/**
 * Stop auto-refresh
 */
function stopAutoRefresh() {
    console.log('⏸️ Stopping auto-refresh...');
    if (refreshTimer) {
        clearInterval(refreshTimer);
        refreshTimer = null;
    }
}

/**
 * Show/hide refresh indicator
 */
function showRefreshIndicator() {
    const indicator = document.getElementById('refreshIndicator');
    if (indicator) indicator.classList.add('active');
}

function hideRefreshIndicator() {
    const indicator = document.getElementById('refreshIndicator');
    if (indicator) indicator.classList.remove('active');
}

/**
 * Update last refresh timestamp
 */
function updateLastRefreshTime() {
    const timestampEl = document.getElementById('lastRefreshTimestamp');
    if (timestampEl) {
        timestampEl.textContent = new Date().toLocaleTimeString();
    }
}

// ============================================
// FILTERING AND SEARCH
// ============================================

/**
 * Filter orders based on status and search
 */
function filterOrders() {
    const rows = document.querySelectorAll('#ordersTableBody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        const searchText = row.getAttribute('data-search-text');
        
        if (!status) return; // Skip rows without status (like empty state)
        
        const statusMatch = currentFilter === 'all' || status === currentFilter;
        const searchMatch = currentSearch === '' || (searchText && searchText.includes(currentSearch));
        
        if (statusMatch && searchMatch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show/hide "no results" message
    const table = document.querySelector('.orders-table');
    const noResults = document.getElementById('noResultsMessage');
    
    if (table && noResults) {
        if (visibleCount === 0 && document.querySelectorAll('#ordersTableBody tr').length > 0) {
            table.style.display = 'none';
            noResults.classList.remove('d-none');
        } else {
            table.style.display = '';
            noResults.classList.add('d-none');
        }
    }
}

/**
 * Update filter label
 */
function updateFilterLabel() {
    const labelMap = {
        'all': 'All Orders',
        'pending': 'Pending Orders',
        'preparing': 'Preparing Orders',
        'completed': 'Completed Orders',
        'cancelled': 'Cancelled Orders'
    };
    const labelEl = document.getElementById('currentFilterLabel');
    if (labelEl) {
        labelEl.textContent = labelMap[currentFilter] || 'Orders';
    }
}

// ============================================
// EVENT DELEGATION - HANDLES ALL BUTTON CLICKS
// ============================================

document.addEventListener('click', function(e) {
    
    // ===== VIEW ORDER BUTTON =====
    if (e.target.closest('.view-order-btn')) {
        const btn = e.target.closest('.view-order-btn');
        const orderId = btn.getAttribute('data-order-id');
        const orderNumber = btn.getAttribute('data-order-number');
        
        viewOrderDetails(orderId, orderNumber);
    }
    
    // ===== UPDATE STATUS BUTTON =====
    if (e.target.closest('.update-status-btn')) {
        const btn = e.target.closest('.update-status-btn');
        const orderId = btn.getAttribute('data-order-id');
        const orderNumber = btn.getAttribute('data-order-number');
        const row = btn.closest('tr');
        const select = row.querySelector('.status-select');
        const newStatus = select.value;
        const currentStatus = select.getAttribute('data-current-status');
        
        if (newStatus === currentStatus) return;
        
        if (newStatus === 'completed') {
            // Show payment modal
            const orderTotal = parseFloat(row.getAttribute('data-order-total'));
            showPaymentModal(orderId, orderNumber, orderTotal);
        } else {
            // Show update status modal
            showUpdateStatusModal(orderId, orderNumber, newStatus);
        }
    }
    
    // ===== CANCEL ORDER BUTTON =====
    if (e.target.closest('.cancel-order-btn')) {
        const btn = e.target.closest('.cancel-order-btn');
        const orderId = btn.getAttribute('data-order-id');
        const orderNumber = btn.getAttribute('data-order-number');
        
        showCancelOrderModal(orderId, orderNumber);
    }
    
    // ===== FILTER TABS =====
    if (e.target.closest('.filter-tab')) {
        const tab = e.target.closest('.filter-tab');
        
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        
        currentFilter = tab.getAttribute('data-status');
        updateFilterLabel();
        filterOrders();
    }
});

// ============================================
// MODAL FUNCTIONS
// ============================================

/**
 * Show payment modal
 */
function showPaymentModal(orderId, orderNumber, orderTotal) {
    if (!paymentModal) return;
    
    currentOrderTotal = orderTotal;
    
    document.getElementById('paymentOrderNumber').textContent = orderNumber;
    document.getElementById('paymentTotalAmount').textContent = orderTotal.toFixed(2);
    document.getElementById('paymentOrderId').value = orderId;
    
    const amountPaidInput = document.getElementById('amountPaid');
    amountPaidInput.value = '';
    document.getElementById('changeAlert').style.display = 'none';
    document.getElementById('insufficientAlert').style.display = 'none';
    document.getElementById('confirmPaymentBtn').disabled = true;
    
    paymentModal.show();
}

/**
 * Show update status modal
 */
function showUpdateStatusModal(orderId, orderNumber, newStatus) {
    if (!updateStatusModal) return;
    
    const newStatusText = capitalize(newStatus);
    
    document.getElementById('updateOrderNumber').textContent = orderNumber;
    document.getElementById('updateNewStatus').textContent = newStatusText;
    document.getElementById('updateOrderId').value = orderId;
    document.getElementById('updateStatusValue').value = newStatus;
    
    updateStatusModal.show();
}

/**
 * Show cancel order modal
 */
function showCancelOrderModal(orderId, orderNumber) {
    if (!cancelOrderModal) return;
    
    document.getElementById('cancelOrderNumber').textContent = orderNumber;
    document.getElementById('cancelOrderId').value = orderId;
    
    cancelOrderModal.show();
}

/**
 * View order details via AJAX
 */
function viewOrderDetails(orderId, orderNumber) {
    if (!orderDetailsModal) return;
    
    const loaderEl = document.getElementById('orderDetailsLoader');
    const contentEl = document.getElementById('orderDetailsContent');
    const errorEl = document.getElementById('orderDetailsError');
    
    // Show loader
    loaderEl.style.display = 'block';
    contentEl.style.display = 'none';
    errorEl.style.display = 'none';
    
    document.getElementById('detailOrderNumber').textContent = '#' + orderNumber;
    document.getElementById('printReceiptBtn').style.display = 'none';
    
    orderDetailsModal.show();
    
    // Fetch order details
    fetch(`/coffee_shop/app/ajax/get_order_details.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(res => {
            if (!res.success) {
                throw new Error(res.error || 'Failed to load order details');
            }
            
            const { order, items, payment } = res.data;
            
            // Populate modal
            populateOrderDetailsModal(order, items, payment);
            
            // Show content
            loaderEl.style.display = 'none';
            contentEl.style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading order details:', error);
            loaderEl.style.display = 'none';
            errorEl.style.display = 'block';
        });
}

/**
 * Populate order details modal
 */
function populateOrderDetailsModal(order, items, payment) {
    // Customer info
    document.getElementById('detailCustomer').textContent = order.customer_name || 'Walk-in';
    document.getElementById('detailDate').textContent = new Date(order.created_at).toLocaleString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
    document.getElementById('detailCashier').textContent = order.cashier_name || 'N/A';
    
    // Order type
    const orderTypeHtml = order.order_type ? getOrderTypeBadge(order.order_type) : '—';
    document.getElementById('detailOrderType').innerHTML = orderTypeHtml;
    
    // Status
    document.getElementById('detailStatus').innerHTML = getStatusBadge(order.status);
    
    // Table number
    if (order.table_number) {
        document.getElementById('detailTable').textContent = 'Table ' + order.table_number;
        document.getElementById('detailTableWrap').style.display = '';
    } else {
        document.getElementById('detailTableWrap').style.display = 'none';
    }
    
    // Items
    const itemsBody = document.getElementById('detailItemsBody');
    itemsBody.innerHTML = '';
    
    items.forEach(item => {
        const itemSubtotal = (parseFloat(item.unit_price) * parseInt(item.quantity)).toFixed(2);
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="padding: 0.85rem 1rem; border-bottom: 1px solid #edf2f7; font-weight: 600; color: var(--navy);">
                ${escapeHtml(item.item_name)}
            </td>
            <td style="padding: 0.85rem 1rem; border-bottom: 1px solid #edf2f7; text-align: center; color: var(--warm-gray);">
                ${item.quantity}
            </td>
            <td style="padding: 0.85rem 1rem; border-bottom: 1px solid #edf2f7; text-align: right; color: var(--warm-gray);">
                ₱${formatPrice(item.unit_price)}
            </td>
            <td style="padding: 0.85rem 1rem; border-bottom: 1px solid #edf2f7; text-align: right; font-weight: 700; color: var(--success);">
                ₱${itemSubtotal}
            </td>
        `;
        itemsBody.appendChild(tr);
        
        // Add addons
        if (item.addons && item.addons.length > 0) {
            item.addons.forEach(addon => {
                const addonSubtotal = (parseFloat(addon.unit_price) * parseInt(addon.quantity)).toFixed(2);
                const addonTr = document.createElement('tr');
                addonTr.innerHTML = `
                    <td style="padding: 0.5rem 1rem 0.5rem 2rem; border-bottom: 1px dashed #edf2f7; color: var(--warm-gray); font-size: 0.82rem;">
                        <i class="fas fa-level-up-alt fa-rotate-90 me-1" style="color: var(--primary-light); font-size: 0.7rem;"></i>
                        ${escapeHtml(addon.addon_name)}
                    </td>
                    <td style="padding: 0.5rem 1rem; border-bottom: 1px dashed #edf2f7; text-align: center; color: var(--warm-gray); font-size: 0.82rem;">
                        ${addon.quantity}
                    </td>
                    <td style="padding: 0.5rem 1rem; border-bottom: 1px dashed #edf2f7; text-align: right; color: var(--warm-gray); font-size: 0.82rem;">
                        ₱${formatPrice(addon.unit_price)}
                    </td>
                    <td style="padding: 0.5rem 1rem; border-bottom: 1px dashed #edf2f7; text-align: right; color: var(--warm-gray); font-size: 0.82rem;">
                        ₱${addonSubtotal}
                    </td>
                `;
                itemsBody.appendChild(addonTr);
            });
        }
    });
    
    // Totals
    const total = parseFloat(order.total_amount || 0);
    const subtotal = total / 1.12;
    const vat = total - subtotal;
    
    document.getElementById('detailSubtotal').textContent = subtotal.toFixed(2);
    document.getElementById('detailVat').textContent = vat.toFixed(2);
    document.getElementById('detailTotal').textContent = total.toFixed(2);
    
    // Payment info
    if (payment && order.status === 'completed') {
        document.getElementById('detailPaymentSection').style.display = '';
        document.getElementById('detailPayMethod').textContent = payment.payment_method || '—';
        document.getElementById('detailAmountPaid').textContent = formatPrice(payment.amount_paid);
        document.getElementById('detailChange').textContent = formatPrice(payment.change_amount);
    } else {
        document.getElementById('detailPaymentSection').style.display = 'none';
    }
    
    // Print button
    if (order.status === 'completed') {
        const printBtn = document.getElementById('printReceiptBtn');
        printBtn.href = `${window.location.pathname}?page=print_receipt&order_id=${order.order_id}`;
        printBtn.style.display = '';
    }
}

// ============================================
// SETUP EVENT LISTENERS
// ============================================

function setupEventListeners() {
    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }
    
    // Search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentSearch = this.value.toLowerCase();
            filterOrders();
        });
    }
    
    // Payment amount input
    const amountPaidInput = document.getElementById('amountPaid');
    if (amountPaidInput) {
        amountPaidInput.addEventListener('input', function() {
            const amountPaid = parseFloat(this.value) || 0;
            const changeAlert = document.getElementById('changeAlert');
            const insufficientAlert = document.getElementById('insufficientAlert');
            const confirmBtn = document.getElementById('confirmPaymentBtn');
            
            if (amountPaid >= currentOrderTotal) {
                const change = amountPaid - currentOrderTotal;
                document.getElementById('changeAmount').textContent = change.toFixed(2);
                document.getElementById('changeAmountHidden').value = change.toFixed(2);
                
                changeAlert.style.display = 'flex';
                insufficientAlert.style.display = 'none';
                confirmBtn.disabled = false;
            } else {
                changeAlert.style.display = 'none';
                insufficientAlert.style.display = 'flex';
                confirmBtn.disabled = true;
            }
        });
    }
    
    // Manual refresh button
    const manualRefreshBtn = document.getElementById('manualRefreshBtn');
    if (manualRefreshBtn) {
        manualRefreshBtn.addEventListener('click', function() {
            const icon = this.querySelector('.refresh-icon');
            if (icon) {
                icon.style.animation = 'spin 0.6s ease';
                setTimeout(() => icon.style.animation = '', 600);
            }
            refreshOrders();
        });
    }
    
    // Auto-refresh toggle
    const autoRefreshToggle = document.getElementById('autoRefreshToggle');
    if (autoRefreshToggle) {
        autoRefreshToggle.addEventListener('change', function() {
            if (this.checked) {
                startAutoRefresh();
            } else {
                stopAutoRefresh();
            }
        });
    }
    
    // Stop refresh when tab is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
    
    // Modal backdrop cleanup
    document.addEventListener('hidden.bs.modal', function() {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
    
    // Show success/error modals if present
    if (document.getElementById('successModal')) {
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    }
    if (document.getElementById('errorModal')) {
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatPrice(amount) {
    return parseFloat(amount || 0).toFixed(2);
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

// Export functions for external use
window.refreshOrders = refreshOrders;
window.startAutoRefresh = startAutoRefresh;
window.stopAutoRefresh = stopAutoRefresh;
window.filterOrders = filterOrders;

console.log('✅ Orders management script loaded successfully');