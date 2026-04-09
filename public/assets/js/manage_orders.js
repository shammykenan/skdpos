/**
 * ============================================
 * ORDERS MANAGEMENT — Item-level tracking
 * ============================================
 */

const REFRESH_INTERVAL     = 2000;
const PAGE_LIMIT           = 10;
const BASE_URL             = '/dinewithdinpos';
const AJAX_URL             = `${BASE_URL}/app/ajax/get_orders.php`;
const ITEM_STATUS_URL      = `${BASE_URL}/app/ajax/update_order_item_status.php`;
const ORDER_DETAILS_URL    = `${BASE_URL}/app/ajax/get_order_details.php`;
const ADMIN_USERS_URL      = `${BASE_URL}/app/ajax/get_admin_users.php`;
const ORDER_ITEMS_URL      = `${BASE_URL}/app/ajax/get_order_items.php`;
const CANCEL_VOID_ITEM_URL = `${BASE_URL}/app/ajax/cancel_items.php`;
const VOID_ORDER_URL       = `${BASE_URL}/app/ajax/void_order.php`;
const GET_REASONS_URL      = `${BASE_URL}/app/ajax/get_reasons.php`;
const MARK_SERVED_URL      = `${BASE_URL}/app/ajax/mark_served.php`;
const PROCESS_REFUND_URL   = `${BASE_URL}/app/ajax/process_refund.php`;

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
let currentOrdersCache = {};
let openDetailsOrderId = null;
let kitchenModalOrderId = null;

let orderDetailsModal, kitchenItemsModal,
    voidOrderModal, cancelVoidItemModal, finalReceiptModal,
    markServedModal, refundModal;

// pendingItemAction carries refund fields for void_item
let pendingItemAction = {
    orderItemId: null, action: null, itemName: null, btn: null,
    refundAmount: 0, refundMethod: 'cash', refundNote: '',
};

// Void ORDER modal state
let _voidOrderTotalPaid = 0;
let _voidOrderRefundCap = 0;
let _voidRefundOpen     = false;

// Void ITEM modal state
let _voidItemTotalPaid  = 0;
let _voidItemRefundCap  = 0;
let _voidItemRefundOpen = false;

// Post-completion refund modal state
let _refundOrderId     = null;
let _refundOrderNumber = '';
let _refundOrderTotal  = 0;
let _refundAlready     = 0;
let _refundRemaining   = 0;
let _refundItems       = [];
let _refundType        = 'full';

// Shared last-fetched refunds (all types) for receipt printing
window._lastOrderRefunds = null;

// ============================================
// VOID REFUND DROPDOWN TOGGLE (order-level)
// ============================================
function toggleVoidRefund() {
    _voidRefundOpen = !_voidRefundOpen;
    const panel   = document.getElementById('voidRefundPanel');
    const chevron = document.getElementById('voidRefundChevron');
    if (panel)   panel.style.display = _voidRefundOpen ? 'block' : 'none';
    if (chevron) chevron.className   = `fas fa-chevron-down${_voidRefundOpen ? ' open' : ''}`;
    if (!_voidRefundOpen) {
        const refAmt = document.getElementById('voidRefundAmount');
        if (refAmt) { refAmt.value = ''; refAmt.classList.remove('inp-invalid', 'inp-valid'); }
        const fb  = document.getElementById('voidRefundFeedback');
        const err = document.getElementById('voidRefundError');
        if (fb)  fb.style.display  = 'none';
        if (err) err.textContent   = '';
    }
}
window.toggleVoidRefund = toggleVoidRefund;

// ============================================
// REASON DROPDOWN HELPERS
// ============================================
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
            const opt = document.createElement('option');
            opt.value = r.reason_text; opt.textContent = r.reason_text;
            sel.appendChild(opt);
        });
    } catch (err) {
        console.error('loadReasons error:', err);
        sel.innerHTML = '<option value="">— Could not load reasons —</option>';
    }
}

function wireReasonDropdown(selectId, textareaId, errorId) {
    const sel = document.getElementById(selectId);
    const ta  = document.getElementById(textareaId);
    const err = document.getElementById(errorId);
    if (!sel) return { getReason: () => '', reset: () => {}, showError: () => {} };
    sel.addEventListener('change', function () {
        if (ta)  ta.style.display  = this.value === 'Other' ? '' : 'none';
        if (err) err.style.display = 'none';
        sel.style.borderColor = '';
    });
    return {
        getReason() {
            const v = sel.value;
            if (!v) return '';
            return v === 'Other' ? (ta ? ta.value.trim() : '') : v;
        },
        reset() {
            sel.value = ''; sel.style.borderColor = '';
            if (ta)  { ta.value = ''; ta.style.display = 'none'; }
            if (err) err.style.display = 'none';
        },
        showError() {
            if (err) err.style.display = 'block';
            sel.style.borderColor = 'var(--danger)'; sel.focus();
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
    catch (e) { console.error('Invalid JSON:', text); throw new Error('Invalid JSON from server'); }
}

// ============================================
// ERROR MODAL
// ============================================
function showErrorModal(title, message) {
    const existing = document.getElementById('dynamicErrorModal');
    if (existing) existing.remove();
    const html = `<div class="modal fade" id="dynamicErrorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>${escapeHtml(title)}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><p class="mb-0">${escapeHtml(message)}</p></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', html);
    const el = document.getElementById('dynamicErrorModal');
    if (el && typeof bootstrap !== 'undefined') {
        const m = new bootstrap.Modal(el); m.show();
        el.addEventListener('hidden.bs.modal', () => el.remove());
    }
}

// ============================================
// VOID REFUND PANEL HELPERS (order-level)
// ============================================
function resetVoidRefundPanel() {
    _voidOrderTotalPaid = 0;
    _voidOrderRefundCap = 0;
    _voidRefundOpen     = false;

    const panel     = document.getElementById('voidRefundPanel');
    const chevron   = document.getElementById('voidRefundChevron');
    const paidHint  = document.getElementById('voidRefundPaidHint');
    const presetsEl = document.getElementById('voidRefundPresets');

    if (panel)     panel.style.display  = 'none';
    if (chevron)   chevron.className    = 'fas fa-chevron-down';
    if (paidHint)  paidHint.textContent = 'Loading payment info…';
    if (presetsEl) presetsEl.innerHTML  = '';

    ['voidRefundAmount','voidRefundNote','voidRefundMethod','voidRefundFeedback','voidRefundError'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        if (id === 'voidRefundAmount')   { el.value = ''; el.disabled = false; el.classList.remove('inp-invalid','inp-valid'); }
        else if (id === 'voidRefundFeedback') el.style.display = 'none';
        else if (id === 'voidRefundError')    el.textContent   = '';
        else el.value = id === 'voidRefundMethod' ? 'cash' : '';
    });
}

function validateRefundInput() {
    const input    = document.getElementById('voidRefundAmount');
    const errEl    = document.getElementById('voidRefundError');
    const feedback = document.getElementById('voidRefundFeedback');
    if (!input) return true;

    const val = parseFloat(input.value) || 0;
    const cap = _voidOrderRefundCap > 0 ? _voidOrderRefundCap : _voidOrderTotalPaid;

    input.classList.remove('inp-invalid', 'inp-valid');

    if (val < 0) {
        input.classList.add('inp-invalid');
        if (errEl) errEl.textContent = 'Refund amount cannot be negative.';
        if (feedback) feedback.style.display = 'none';
        return false;
    }
    if (cap > 0 && val > cap + 0.005) {
        input.classList.add('inp-invalid');
        if (errEl) errEl.textContent = `Maximum refund is ₱${cap.toFixed(2)}.`;
        if (feedback) feedback.style.display = 'none';
        return false;
    }

    if (errEl) errEl.textContent = '';

    if (val > 0) {
        input.classList.add('inp-valid');
        if (feedback && _voidOrderTotalPaid > 0) {
            const keep = Math.max(0, _voidOrderTotalPaid - val);
            feedback.style.display    = 'block';
            feedback.style.background = keep > 0.005 ? 'rgba(44,122,90,0.10)' : 'rgba(15,76,92,0.07)';
            feedback.style.color      = keep > 0.005 ? 'var(--success)' : 'var(--primary-dark)';
            feedback.textContent      = keep > 0.005
                ? `Customer keeps ₱${keep.toFixed(2)} of the ₱${_voidOrderTotalPaid.toFixed(2)} paid.`
                : `Full refund of ₱${_voidOrderTotalPaid.toFixed(2)}.`;
        } else if (feedback) {
            feedback.style.display = 'none';
        }
    } else {
        input.classList.remove('inp-valid');
        if (feedback) feedback.style.display = 'none';
    }

    document.querySelectorAll('#voidRefundPresets .refund-preset-btn').forEach(btn => {
        btn.classList.toggle('active', Math.abs(parseFloat(btn.dataset.amount) - val) < 0.005);
    });

    return true;
}

function buildRefundPresets(items, totalPaid, refundCap) {
    const presetsEl = document.getElementById('voidRefundPresets');
    if (!presetsEl) return;
    presetsEl.innerHTML = '';

    if (refundCap <= 0) return;

    const addPreset = (label, amount, extraClass = '') => {
        const rounded = Math.round(amount * 100) / 100;
        if (rounded <= 0) return;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `refund-preset-btn${extraClass ? ' ' + extraClass : ''}`;
        btn.textContent = `${label}  ₱${rounded.toFixed(2)}`;
        btn.dataset.amount = rounded.toFixed(2);
        btn.addEventListener('click', function () {
            const refAmt = document.getElementById('voidRefundAmount');
            if (!refAmt) return;
            refAmt.value = rounded.toFixed(2);
            validateRefundInput();
        });
        presetsEl.appendChild(btn);
    };

    addPreset('Full', refundCap, 'full');
    [75, 50, 25].forEach(pct => addPreset(`${pct}%`, refundCap * pct / 100));

    // Per ready-item selling prices
    if (items && items.length > 0) {
        items.filter(i => i.item_status === 'ready').forEach(item => {
            const addonsTotal = (item.addons || []).reduce(
                (s, a) => s + parseFloat(a.price || 0) * parseInt(a.quantity || 1), 0
            );
            const lineTotal = (parseFloat(item.unit_price || 0) * parseInt(item.quantity || 1)) + addonsTotal;
            if (lineTotal > 0 && lineTotal <= refundCap) {
                const name = (item.item_name_snapshot || item.item_name || 'Item').substring(0, 14);
                addPreset(name, lineTotal);
            }
        });
    }
}

// ============================================
// VOID ITEM REFUND PANEL (injected into modal)
// ============================================
function _injectVoidItemRefundPanel(state) {
    document.getElementById('voidItemRefundSection')?.remove();

    const modalBody = document.querySelector('#cancelVoidItemModal .modal-body');
    if (!modalBody) return;

    if (state === 'loading') {
        const loadDiv = document.createElement('div');
        loadDiv.id = 'voidItemRefundSection';
        loadDiv.style.cssText = 'margin-top:1rem;padding:0.75rem 1rem;background:var(--light-bg);border-radius:12px;font-size:0.82rem;color:var(--warm-gray);text-align:center;';
        loadDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Checking payment info…';
        modalBody.appendChild(loadDiv);
        return;
    }

    if (state === 'error' || state === 'no-context') return;

    const paidHint = _voidItemTotalPaid > 0
        ? `Paid: ₱${_voidItemTotalPaid.toFixed(2)} · Max refund for this item: ₱${_voidItemRefundCap.toFixed(2)}`
        : 'No payment recorded — refund not applicable.';
    const disabled = _voidItemRefundCap <= 0;

    const section = document.createElement('div');
    section.id = 'voidItemRefundSection';
    section.style.cssText = 'margin-top:1rem;border:1.5px solid var(--border);border-radius:12px;overflow:hidden;';
    section.innerHTML = `
        <button type="button" id="voidItemRefundToggleBtn"
                style="width:100%;display:flex;align-items:center;justify-content:space-between;
                       padding:0.7rem 1rem;background:var(--light-bg);border:none;
                       cursor:pointer;font-family:inherit;transition:background 0.15s;">
            <div style="display:flex;align-items:center;gap:0.6rem;">
                <div style="width:28px;height:28px;background:rgba(214,64,69,0.10);border-radius:8px;
                            display:flex;align-items:center;justify-content:center;color:var(--danger);font-size:0.82rem;">
                    <i class="fas fa-rotate-left"></i>
                </div>
                <div style="text-align:left;">
                    <div style="font-size:0.83rem;font-weight:700;color:var(--navy);">
                        Issue a Refund
                        <span style="font-size:0.73rem;font-weight:400;color:var(--warm-gray);margin-left:0.3rem;">(optional)</span>
                    </div>
                    <div style="font-size:0.71rem;color:var(--warm-gray);margin-top:1px;">${paidHint}</div>
                </div>
            </div>
            <i class="fas fa-chevron-down" id="voidItemRefundChevron"
               style="color:var(--warm-gray);font-size:0.78rem;transition:transform 0.2s;"></i>
        </button>
        <div id="voidItemRefundPanel"
             style="display:none;padding:0.9rem 1rem;border-top:1.5px solid var(--border);background:white;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.6rem;margin-bottom:0.6rem;">
                <div>
                    <label style="font-size:0.74rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.3rem;">Refund Amount</label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:0;top:0;bottom:0;width:32px;display:flex;align-items:center;
                                     justify-content:center;font-weight:700;color:var(--warm-gray);font-size:0.88rem;
                                     background:var(--light-bg);border-radius:9px 0 0 9px;border:1.5px solid var(--border);border-right:none;">₱</span>
                        <input type="number" id="voidItemRefundAmount" min="0" step="0.01"
                               max="${_voidItemRefundCap > 0 ? _voidItemRefundCap.toFixed(2) : '0'}"
                               placeholder="${disabled ? 'No payment on file' : `0.00 – ${_voidItemRefundCap.toFixed(2)}`}"
                               ${disabled ? 'disabled' : ''}
                               style="width:100%;padding:0.45rem 0.5rem 0.45rem 2rem;border:1.5px solid var(--border);
                                      border-radius:9px;font-size:0.85rem;font-family:inherit;outline:none;transition:border-color 0.15s;">
                    </div>
                    <div id="voidItemRefundError" style="font-size:0.71rem;color:var(--danger);margin-top:3px;min-height:1rem;"></div>
                </div>
                <div>
                    <label style="font-size:0.74rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.3rem;">Method</label>
                    <select id="voidItemRefundMethod"
                            style="width:100%;padding:0.45rem 0.6rem;border:1.5px solid var(--border);border-radius:9px;
                                   font-size:0.83rem;font-family:inherit;background:white;color:var(--dark);outline:none;">
                        <option value="cash">Cash</option>
                    </select>
                </div>
            </div>
            <div>
                <label style="font-size:0.74rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.3rem;">
                    Note <span style="font-weight:400;color:var(--warm-gray);">(optional)</span>
                </label>
                <input type="text" id="voidItemRefundNote" maxlength="200"
                       placeholder="e.g. Item incorrect, customer refunded…"
                       style="width:100%;padding:0.45rem 0.6rem;border:1.5px solid var(--border);
                              border-radius:9px;font-size:0.81rem;font-family:inherit;outline:none;">
            </div>
            ${!disabled ? `
            <div style="margin-top:0.65rem;">
                <div style="font-size:0.71rem;font-weight:700;color:var(--warm-gray);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.35rem;">Quick select</div>
                <div id="voidItemRefundPresets" style="display:flex;gap:0.35rem;flex-wrap:wrap;"></div>
            </div>` : ''}
        </div>`;

    modalBody.appendChild(section);

    if (!disabled) {
        const presetsEl = document.getElementById('voidItemRefundPresets');
        const addPreset = (label, amount, extraClass = '') => {
            const rounded = Math.round(amount * 100) / 100;
            if (rounded <= 0) return;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `refund-preset-btn${extraClass ? ' ' + extraClass : ''}`;
            btn.textContent = `${label}  ₱${rounded.toFixed(2)}`;
            btn.dataset.amount = rounded.toFixed(2);
            btn.addEventListener('click', function () {
                const inp = document.getElementById('voidItemRefundAmount');
                if (inp) { inp.value = rounded.toFixed(2); _validateVoidItemRefund(); }
                document.querySelectorAll('#voidItemRefundPresets .refund-preset-btn')
                    .forEach(b => b.classList.toggle('active', b === btn));
            });
            presetsEl?.appendChild(btn);
        };
        addPreset('Full', _voidItemRefundCap, 'full');
        [75, 50, 25].forEach(pct => addPreset(`${pct}%`, _voidItemRefundCap * pct / 100));
    }

    document.getElementById('voidItemRefundToggleBtn')?.addEventListener('click', function () {
        _voidItemRefundOpen = !_voidItemRefundOpen;
        const panel   = document.getElementById('voidItemRefundPanel');
        const chevron = document.getElementById('voidItemRefundChevron');
        if (panel)   panel.style.display     = _voidItemRefundOpen ? 'block' : 'none';
        if (chevron) chevron.style.transform = _voidItemRefundOpen ? 'rotate(180deg)' : 'rotate(0deg)';
    });

    document.getElementById('voidItemRefundAmount')?.addEventListener('input', _validateVoidItemRefund);
}

function _validateVoidItemRefund() {
    const inp   = document.getElementById('voidItemRefundAmount');
    const errEl = document.getElementById('voidItemRefundError');
    if (!inp) return true;
    const val = parseFloat(inp.value) || 0;
    const cap = _voidItemRefundCap;
    inp.style.borderColor = '';
    if (errEl) errEl.textContent = '';
    if (val < 0) {
        inp.style.borderColor = 'var(--danger)';
        if (errEl) errEl.textContent = 'Cannot be negative.';
        return false;
    }
    if (cap > 0 && val > cap + 0.005) {
        inp.style.borderColor = 'var(--danger)';
        if (errEl) errEl.textContent = `Max refund for this item is ₱${cap.toFixed(2)}.`;
        return false;
    }
    if (val > 0) inp.style.borderColor = 'var(--success)';
    document.querySelectorAll('#voidItemRefundPresets .refund-preset-btn').forEach(btn => {
        btn.classList.toggle('active', Math.abs(parseFloat(btn.dataset.amount) - val) < 0.005);
    });
    return true;
}

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function () {

    try {
        if (typeof bootstrap !== 'undefined') {
                        orderDetailsModal   = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            kitchenItemsModal   = new bootstrap.Modal(document.getElementById('kitchenItemsModal'));
            voidOrderModal      = new bootstrap.Modal(document.getElementById('voidOrderModal'));
            cancelVoidItemModal = new bootstrap.Modal(document.getElementById('cancelVoidItemModal'));
            finalReceiptModal   = new bootstrap.Modal(document.getElementById('finalReceiptModal'));
            const msEl = document.getElementById('markServedModal');
            if (msEl) markServedModal = new bootstrap.Modal(msEl);
            const rmEl = document.getElementById('refundModal');
            if (rmEl) refundModal = new bootstrap.Modal(rmEl);
        }
    } catch (e) { console.error('Modal init error:', e); }

        window._voidOrderReason      = wireReasonDropdown('voidOrderReasonSelect',      'voidOrderReasonText',      'voidOrderReasonError');
    window._cancelVoidItemReason = wireReasonDropdown('cancelVoidItemReasonSelect', 'cancelVoidItemReasonText', 'cancelVoidItemReasonError');

    document.getElementById('voidRefundAmount')?.addEventListener('input', validateRefundInput);

    document.getElementById('orderDetailsModal')?.addEventListener('hidden.bs.modal', () => { openDetailsOrderId = null; });
    document.getElementById('kitchenItemsModal')?.addEventListener('hidden.bs.modal', function () {
        kitchenModalOrderId = null;
        const focused = document.activeElement;
        if (focused && this.contains(focused)) focused.blur();
    });
    document.getElementById('kitchenItemsModal')?.addEventListener('show.bs.modal', function () { this.removeAttribute('aria-hidden'); });
        document.getElementById('voidOrderModal')?.addEventListener('show.bs.modal', () => { window._voidOrderReason?.reset(); resetVoidRefundPanel(); });
    document.getElementById('cancelVoidItemModal')?.addEventListener('show.bs.modal', () => { window._cancelVoidItemReason?.reset(); });
    document.getElementById('cancelVoidItemModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('voidItemRefundSection')?.remove();
        _voidItemTotalPaid  = 0;
        _voidItemRefundCap  = 0;
        _voidItemRefundOpen = false;
    });

    // Void Order confirm
    document.getElementById('confirmVoidOrderBtn')?.addEventListener('click', async function () {
        const reason = window._voidOrderReason?.getReason() || '';
        if (!reason) { window._voidOrderReason?.showError(); return; }

        let refAmt = 0, refMethod = 'cash', refNote = '';
        if (_voidRefundOpen) {
            if (!validateRefundInput()) return;
            refAmt    = parseFloat(document.getElementById('voidRefundAmount')?.value || 0) || 0;
            refMethod = document.getElementById('voidRefundMethod')?.value || 'cash';
            refNote   = document.getElementById('voidRefundNote')?.value?.trim() || '';
        }

        const orderId  = this.getAttribute('data-order-id');
        const orderNum = this.getAttribute('data-order-number');
        voidOrderModal?.hide();
        await executeVoidOrder(orderId, orderNum, reason, refAmt, refMethod, refNote);
    });

    // Cancel/Void Item confirm
    document.getElementById('confirmCancelVoidItemBtn')?.addEventListener('click', async function () {
        const reason = window._cancelVoidItemReason?.getReason() || '';
        if (!reason) { window._cancelVoidItemReason?.showError(); return; }

        let refundAmount = 0, refundMethod = 'cash', refundNote = '';
        if (pendingItemAction.action === 'void_item' && _voidItemRefundOpen) {
            if (!_validateVoidItemRefund()) return;
            refundAmount = parseFloat(document.getElementById('voidItemRefundAmount')?.value || 0) || 0;
            refundMethod = document.getElementById('voidItemRefundMethod')?.value || 'cash';
            refundNote   = document.getElementById('voidItemRefundNote')?.value?.trim() || '';
        }

        pendingItemAction.refundAmount = refundAmount;
        pendingItemAction.refundMethod = refundMethod;
        pendingItemAction.refundNote   = refundNote;
        cancelVoidItemModal?.hide();

        if (pendingItemAction.orderItemId) {
            await executeVoidRefundItem(
                pendingItemAction.orderItemId, pendingItemAction.action,
                pendingItemAction.btn, reason,
                pendingItemAction.refundAmount, pendingItemAction.refundMethod, pendingItemAction.refundNote
            );
            pendingItemAction = { orderItemId: null, action: null, itemName: null, btn: null, refundAmount: 0, refundMethod: 'cash', refundNote: '' };
        }
    });

    // Mark Served confirm
    document.getElementById('confirmMarkServedBtn')?.addEventListener('click', async function () {
        const orderId  = this.getAttribute('data-order-id');
        const orderNum = this.getAttribute('data-order-number');
        markServedModal?.hide();
        await executeMarkAsServed(orderId, orderNum);
    });

    // Refund modal type toggle
    document.querySelectorAll('.refund-type-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            _refundType = this.dataset.type;
            document.querySelectorAll('.refund-type-btn').forEach(b => {
                const active = b.dataset.type === _refundType;
                const color  = _refundType === 'full' ? 'var(--success)' : 'var(--primary)';
                b.classList.toggle('active', active);
                b.style.background  = active ? color : 'white';
                b.style.color       = active ? 'white' : 'var(--navy)';
                b.style.borderColor = active ? color : 'var(--border)';
            });
            document.getElementById('refundFullPanel').style.display    = _refundType === 'full'    ? 'block' : 'none';
            document.getElementById('refundPartialPanel').style.display = _refundType === 'partial' ? 'block' : 'none';
        });
    });

    document.getElementById('refundReasonSelect')?.addEventListener('change', function () {
        const ta = document.getElementById('refundReasonText');
        if (ta) ta.style.display = this.value === 'Other' ? 'block' : 'none';
        document.getElementById('refundReasonError').style.display = 'none';
    });

    document.getElementById('confirmRefundBtn')?.addEventListener('click', async function () { await executeRefund(); });

    setupEventListeners();
    loadPage(1);
    startAutoRefresh();
    if (window.userRole === 'admin') loadAdminUsers();

    ['successModal', 'errorModal'].forEach(id => {
        const el = document.getElementById(id);
        if (el && typeof bootstrap !== 'undefined') {
            const m = new bootstrap.Modal(el); m.show(); setTimeout(() => m.hide(), 3000);
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
            const opt = document.createElement('option');
            opt.value = admin.user_id; opt.textContent = admin.full_name;
            select.appendChild(opt);
        });
    } catch (err) { console.error('Failed to load admin users:', err); }
}

function syncProcessedByVisibility() {
    const wrapper = document.getElementById('processedByWrapper');
    if (!wrapper) return;
    if (currentFilter === 'completed' && window.userRole === 'admin') {
        wrapper.style.display = 'flex';
    } else {
        wrapper.style.display = 'none'; currentProcessedBy = 0;
        const select = document.getElementById('processedByFilter');
        if (select) select.value = '0';
    }
}

// ============================================
// LOAD PAGE
// ============================================
async function loadPage(page) {
    if (isLoading) return;
    isLoading = true; showTableSpinner(true);
    try {
        const offset = (page - 1) * PAGE_LIMIT;
        const res    = await fetch(buildURL(offset, PAGE_LIMIT), { headers: { 'Cache-Control': 'no-cache' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Unknown error');
        currentPage = page; filteredTotal = data.filtered_total || 0;
        totalPages  = Math.max(1, Math.ceil(filteredTotal / PAGE_LIMIT));
        renderTableRows(data.data || []);
        updateFilterCounts(data.counts, data.total);
        renderPagination(); updateFilterLabel();
    } catch (err) { console.error('Load error:', err); showError(); }
    finally { showTableSpinner(false); isLoading = false; }
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
        const newTotal  = data.filtered_total || 0;
        const freshData = data.data || [];
        const tbody     = document.getElementById('ordersTableBody');
        const existing  = tbody ? tbody.querySelectorAll('tr[data-order-id]').length : 0;
        if (newTotal !== filteredTotal || existing !== freshData.length) {
            filteredTotal = newTotal; totalPages = Math.max(1, Math.ceil(filteredTotal / PAGE_LIMIT));
            renderTableRows(freshData); renderPagination();
        } else { patchTableRows(freshData); }
        if (openDetailsOrderId)  refreshOrderDetailsModal(openDetailsOrderId);
        if (kitchenModalOrderId) refreshKitchenModal(kitchenModalOrderId);
    } catch (err) { console.error('Refresh error:', err); }
    finally { isRefreshing = false; }
}

function startAutoRefresh() { stopAutoRefresh(); refreshTimer = setInterval(refreshOrders, REFRESH_INTERVAL); }
function stopAutoRefresh()  { if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; } }

function buildURL(offset, limit) {
    const params = new URLSearchParams({ limit: limit ?? PAGE_LIMIT, offset: offset ?? 0, status: currentFilter, search: currentSearch });
    if (currentDateFrom) params.set('date_from', currentDateFrom);
    if (currentDateTo)   params.set('date_to',   currentDateTo);
    if (currentCashierView === 'mine') params.set('cashier_view', 'mine');
    if (currentFilter === 'completed' && currentProcessedBy > 0) params.set('processed_by', currentProcessedBy);
    return `${AJAX_URL}?${params}`;
}

// ============================================
// TABLE RENDERING
// ============================================
function renderTableRows(orders) {
    const tbody = document.getElementById('ordersTableBody');
    const table = document.querySelector('.orders-table');
    const noRes = document.getElementById('noResultsMessage');
    if (!tbody) return;
    if (!orders || orders.length === 0) {
        tbody.innerHTML = '';
        if (table) table.style.display = 'none';
        if (noRes) noRes.classList.remove('d-none');
        return;
    }
    if (table) table.style.display = '';
    if (noRes) noRes.classList.add('d-none');
    orders.forEach(o => { currentOrdersCache[String(o.order_id)] = o; });
    tbody.innerHTML = orders.map(order => createOrderRowHTML(order)).join('');
}

function patchTableRows(orders) {
    const tbody = document.getElementById('ordersTableBody');
    if (!tbody) return;
    orders.forEach(order => {
        const row = tbody.querySelector(`tr[data-order-id="${order.order_id}"]`);
        if (!row) return;
        const currentStatus = row.getAttribute('data-status');
        const newStatus     = (order.status || 'pending').toLowerCase();
        const newTotal      = parseFloat(order.total_amount || 0);
        const subtotalCell  = row.querySelector('td[data-label="Subtotal"]');
        if (subtotalCell) subtotalCell.innerHTML = `<span class="price-subtotal">₱${formatPrice(newTotal / 1.12)}</span><div class="price-vat">+₱${formatPrice(newTotal - newTotal / 1.12)} VAT (12%)</div>`;
        if (currentStatus !== newStatus) {
            row.setAttribute('data-status', newStatus);
            row.setAttribute('data-order-total', order.total_amount || 0);
            const statusCell  = row.querySelector('td[data-label="Status"]');
            const actionsCell = row.querySelector('td[data-label="Actions"]');
            const totalSpan   = row.querySelector('td[data-label="Total"] span');
            if (statusCell)  statusCell.innerHTML  = getOrderStatusBadge(newStatus);
            if (actionsCell) actionsCell.innerHTML = getOrderActionsHTML(order);
            if (totalSpan)   { totalSpan.className = newStatus === 'completed' ? 'price-total-paid' : 'price-total-unpaid'; totalSpan.textContent = '₱' + formatPrice(newTotal); }
        }
        if (order.items) {
            const itemsCell = row.querySelector('.items-cell');
            if (itemsCell) {
                const tally   = { pending: 0, preparing: 0, ready: 0 };
                order.items.forEach(i => { const s = i.item_status || 'pending'; if (tally[s] !== undefined) tally[s]++; });
                const dotsCfg = [{ key:'pending',color:'#f59e0b',icon:'fa-hourglass-half'},{key:'preparing',color:'#3b82f6',icon:'fa-bowl-food'},{key:'ready',color:'#10b981',icon:'fa-circle-check'}];
                const summaryRow = itemsCell.querySelector('.items-summary-row');
                if (summaryRow) {
                    const noteCount = order.items.filter(i => i.item_notes && i.item_notes.trim()).length;
                    const hasAddons = order.items.some(i => i.addons && i.addons.length > 0);
                    summaryRow.innerHTML = dotsCfg.filter(d => tally[d.key] > 0)
                        .map(d => `<span class="item-tally-dot" style="background:${d.color}18;color:${d.color};border:1px solid ${d.color}44;"><i class="fas ${d.icon}" style="font-size:0.6rem;"></i> ${tally[d.key]}</span>`)
                        .join('')
                        + (noteCount > 0 ? `<span class="items-has-notes-dot">&#9998; ${noteCount} note${noteCount > 1 ? 's' : ''}</span>` : '')
                        + (hasAddons ? `<span class="items-has-addons-dot"><i class="fas fa-plus-circle"></i></span>` : '');
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
        const tc = document.querySelector('.table-container'); if (tc) tc.after(container);
    }
    if (totalPages <= 1 && filteredTotal <= PAGE_LIMIT) { container.style.display = 'none'; return; }
    container.style.display = 'flex';
    const from = filteredTotal === 0 ? 0 : (currentPage - 1) * PAGE_LIMIT + 1;
    const to   = Math.min(currentPage * PAGE_LIMIT, filteredTotal);
    let btns = pageBtn('<i class="fas fa-chevron-left"></i>', currentPage - 1, currentPage === 1, false);
    getPageRange(currentPage, totalPages).forEach(p => { btns += p === '...' ? `<span style="padding:0 4px;color:var(--warm-gray);align-self:center;">…</span>` : pageBtn(p, p, false, p === currentPage); });
    btns += pageBtn('<i class="fas fa-chevron-right"></i>', currentPage + 1, currentPage === totalPages, false);
    container.innerHTML = `<span style="font-size:0.85rem;color:var(--warm-gray);">Showing <strong>${from}–${to}</strong> of <strong>${filteredTotal}</strong> orders</span><div style="display:flex;align-items:center;gap:0.35rem;">${btns}</div>`;
    container.querySelectorAll('.page-btn:not([disabled])').forEach(btn => { btn.addEventListener('click', () => loadPage(parseInt(btn.dataset.page))); });
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

function updateFilterCounts(counts, total) {
    const isAdmin = window.userRole === 'admin', isCashier = window.userRole === 'cashier';
    const data = { all: total ?? 0, pending: counts?.pending ?? 0, preparing: counts?.preparing ?? 0, ready: counts?.ready ?? 0, completed: counts?.completed ?? 0, cancelled: counts?.cancelled ?? 0 };
    if (isAdmin || isCashier) data.voided = counts?.voided ?? 0;
    Object.keys(data).forEach(s => { const span = document.querySelector(`.filter-tab[data-status="${s}"] .filter-count`); if (span) span.textContent = data[s]; });
}

function updateFilterLabel() {
    const map = { all:'All Orders', pending:'Pending Orders', preparing:'Preparing Orders', ready:'Ready Orders', completed:'Completed Orders', voided:'Voided Orders' };
    const el  = document.getElementById('currentFilterLabel'); if (el) el.textContent = map[currentFilter] || 'Orders';
}

function showTableSpinner(show) {
    let s = document.getElementById('tableSpinner');
    if (show) {
        if (!s) { s = document.createElement('tr'); s.id = 'tableSpinner'; s.innerHTML = `<td colspan="8" class="text-center py-4"><div class="spinner-border spinner-border-sm me-2" style="color:var(--primary);" role="status"></div><span class="text-muted" style="font-size:0.875rem;">Loading orders…</span></td>`; document.getElementById('ordersTableBody')?.appendChild(s); }
    } else { s?.remove(); }
}

function showError() {
    const t = document.getElementById('ordersTableBody');
    if (t) t.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger"><i class="fas fa-exclamation-circle me-2"></i>Failed to load orders. Please refresh the page.</td></tr>`;
}

// ============================================
// ROW HTML
// ============================================
function createOrderRowHTML(order) {
    const total = parseFloat(order.total_amount || 0);
    const searchText = [order.order_number || '', order.customer_name || 'walk-in', order.table_name || ''].join(' ').toLowerCase();
    return `<tr data-status="${(order.status || 'pending').toLowerCase()}" data-search-text="${escapeHtml(searchText)}" data-order-total="${order.total_amount || 0}" data-order-id="${order.order_id}" data-order-number="${escapeHtml(order.order_number || '')}">
        <td data-label="Order #"><span class="order-number">#${escapeHtml(order.order_number || '')}</span></td>
        <td data-label="Customer">
            <div class="fw-medium" style="color:var(--navy);">${escapeHtml(order.customer_name || 'Walk-in')}</div>
            <small class="text-muted">${formatDate(order.created_at)}</small>
            ${order.table_name ? `<div class="table-number-badge"><i class="fas fa-chair"></i>${escapeHtml(order.table_name)}</div>` : ''}
        </td>
        <td data-label="Order Type">${getOrderTypeBadge(order.order_type)}</td>
        <td data-label="Items">${getOrderItemsHTML(order.items || [], order.status, order.order_id)}</td>
        <td data-label="Subtotal"><span class="price-subtotal">₱${formatPrice(total / 1.12)}</span><div class="price-vat">+₱${formatPrice(total - total / 1.12)} VAT (12%)</div></td>
        <td data-label="Total"><span class="${order.status === 'completed' ? 'price-total-paid' : 'price-total-unpaid'}">₱${formatPrice(order.total_amount)}</span></td>
        <td data-label="Status">${getOrderStatusBadge(order.status)}</td>
        <td data-label="Actions">${getOrderActionsHTML(order)}</td>
    </tr>`;
}

function getOrderTypeBadge(t) {
    if (!t) return '<span class="order-type-missing">—</span>';
    const types = { dine_in: { icon:'fa-utensils', label:'Dine In' }, takeout: { icon:'fa-bag-shopping', label:'Takeout' } };
    const cfg = types[t] || { icon:'fa-question', label:t };
    return `<span class="order-type-badge ${t}"><i class="fas ${cfg.icon}"></i> ${cfg.label}</span>`;
}

function getOrderStatusBadge(status) {
    if (!status) status = 'pending';
    return `<span class="status-badge status-${status.toLowerCase()}">${capitalize(status)}</span>`;
}

function getOrderItemsHTML(items, orderStatus, orderId) {
    if (!items || items.length === 0) return '<span class="text-muted fst-italic" style="font-size:0.8rem;">No items</span>';
    const tally = { pending:0, preparing:0, ready:0 };
    items.forEach(i => { const s = i.item_status || 'pending'; if (tally[s] !== undefined) tally[s]++; });
    const dotsCfg = [{key:'pending',color:'#f59e0b',icon:'fa-hourglass-half'},{key:'preparing',color:'#3b82f6',icon:'fa-bowl-food'},{key:'ready',color:'#10b981',icon:'fa-circle-check'}];
    const dotsHTML = dotsCfg.filter(d => tally[d.key] > 0).map(d => `<span class="item-tally-dot" style="background:${d.color}18;color:${d.color};border:1px solid ${d.color}44;"><i class="fas ${d.icon}" style="font-size:0.6rem;"></i> ${tally[d.key]}</span>`).join('');
    const namePreview = items.slice(0, 2).map(i => {
        const bn = escapeHtml(i.item_name_snapshot || i.item_name || '');
        const vn = i.variant_name_snapshot || i.current_variant_name || null;
        const qty = parseInt(i.quantity) > 1 ? ` ×${i.quantity}` : '';
        return `<span class="item-name-chip">${bn}${vn ? `<span style="opacity:0.65;font-weight:400;"> · ${escapeHtml(vn)}</span>` : ''}${qty}</span>`;
    }).join('');
    const moreChip  = items.length > 2 ? `<span class="item-name-chip item-name-more">+${items.length - 2} more</span>` : '';
    const noteCount = items.filter(i => i.item_notes && i.item_notes.trim()).length;
    const hasAddons = items.some(i => i.addons && i.addons.length > 0);
    return `<div class="items-cell" data-order-id="${orderId}">
        <div class="items-summary-row">${dotsHTML}${noteCount > 0 ? `<span class="items-has-notes-dot">&#9998; ${noteCount} note${noteCount > 1 ? 's' : ''}</span>` : ''}${hasAddons ? `<span class="items-has-addons-dot"><i class="fas fa-plus-circle"></i></span>` : ''}</div>
        <div class="items-name-row">${namePreview}${moreChip}</div>
        <button type="button" class="items-manage-btn" data-order-id="${orderId}"><i class="fas fa-chevron-right"></i> Manage items</button>
    </div>`;
}

function getOrderActionsHTML(order) {
    const isAdmin = window.userRole === 'admin', isCashier = window.userRole === 'cashier';
    const status = order.status || 'pending', orderId = order.order_id, orderNum = order.order_number || '';
    let html = `<div class="action-buttons"><button type="button" class="btn btn-sm view-order-btn" style="background:var(--lavender);color:var(--navy);" data-order-id="${orderId}" data-order-number="${escapeHtml(orderNum)}"><i class="fas fa-eye"></i> View</button>`;
    if (status === 'ready' && (isAdmin || isCashier)) {
        html += `<button type="button" class="btn btn-success btn-sm mark-served-btn" data-order-id="${orderId}" data-order-number="${escapeHtml(orderNum)}"><i class="fas fa-concierge-bell"></i> Mark as Served</button>`;
        if (isAdmin) html += `<button type="button" class="btn btn-sm void-order-btn" style="background:rgba(15,76,92,0.07);color:var(--primary-dark);border:1.5px solid rgba(15,76,92,0.28);border-radius:8px;font-size:0.78rem;font-weight:600;" data-order-id="${orderId}" data-order-number="${escapeHtml(orderNum)}"><i class="fas fa-ban"></i> Void Order</button>`;
    } else if (status === 'pending' && (isAdmin || isCashier)) {
        html += `<button type="button" class="btn btn-sm void-order-btn" style="background:rgba(15,76,92,0.07);color:var(--primary-dark);border:1.5px solid rgba(15,76,92,0.28);border-radius:8px;font-size:0.78rem;font-weight:600;" data-order-id="${orderId}" data-order-number="${escapeHtml(orderNum)}"><i class="fas fa-ban"></i> Void Order</button>`;
    } else if ((status === 'pending' || status === 'preparing') && isCashier && !isAdmin) {
        html += `<span class="badge action-status-badge" style="background:rgba(94,155,158,0.12);color:var(--info);border:1px solid rgba(94,155,158,0.3);font-size:0.75rem;border-radius:20px;display:flex;align-items:center;justify-content:center;padding:0.4rem 0.8rem;">In Kitchen</span>`;
    } else if (status === 'completed') {
        html += `<span class="badge action-status-badge" style="background:rgba(44,122,90,0.12);color:var(--success);border:1px solid rgba(44,122,90,0.25);font-size:0.75rem;border-radius:20px;display:flex;align-items:center;justify-content:center;padding:0.4rem 0.8rem;">Served</span>`;
    } else if (status === 'cancelled') {
        html += `<span class="badge action-status-badge" style="background:rgba(214,64,69,0.10);color:var(--danger);border:1px solid rgba(214,64,69,0.25);font-size:0.75rem;border-radius:20px;display:flex;align-items:center;justify-content:center;padding:0.4rem 0.8rem;">Cancelled</span>`;
    } else if (status === 'voided') {
        html += `<span class="badge action-status-badge" style="background:rgba(15,76,92,0.10);color:var(--primary-dark);border:1px solid rgba(15,76,92,0.28);font-size:0.75rem;border-radius:20px;display:flex;align-items:center;justify-content:center;padding:0.4rem 0.8rem;">Voided</span>`;
    }
    return html + '</div>';
}

// ============================================
// MARK AS SERVED
// ============================================
async function markAsServed(orderId, orderNumber) {
    const numEl  = document.getElementById('markServedOrderNumber');
    const confEl = document.getElementById('confirmMarkServedBtn');
    if (numEl)  numEl.textContent = orderNumber || '';
    if (confEl) { confEl.setAttribute('data-order-id', orderId); confEl.setAttribute('data-order-number', orderNumber); }
    markServedModal?.show();
}

async function executeMarkAsServed(orderId, orderNumber) {
    try {
        const fd = new FormData(); fd.append('order_id', orderId);
        const res  = await fetch(MARK_SERVED_URL, { method: 'POST', body: fd });
        const data = await safeJSONParse(res);
        if (!data.success) throw new Error(data.error || 'Failed to mark as served.');
        const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
        if (row) {
            row.setAttribute('data-status', 'completed');
            const statusCell  = row.querySelector('td[data-label="Status"]');
            const actionsCell = row.querySelector('td[data-label="Actions"]');
            const totalSpan   = row.querySelector('td[data-label="Total"] span');
            if (statusCell)  statusCell.innerHTML  = getOrderStatusBadge('completed');
            if (totalSpan)   totalSpan.className   = 'price-total-paid';
            if (actionsCell) actionsCell.innerHTML = getOrderActionsHTML({ order_id:orderId, order_number:orderNumber, total_amount:row.getAttribute('data-order-total'), status:'completed' });
        }
        if (kitchenModalOrderId && String(kitchenModalOrderId) === String(orderId)) { kitchenItemsModal?.hide(); kitchenModalOrderId = null; }
    } catch (err) { console.error('Mark as served error:', err); showErrorModal('Mark as Served Failed', err.message); }
}

// ============================================
// ITEM STATUS UPDATE
// ============================================
async function updateItemStatus(orderItemId, nextStatus, btn) {
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Updating…</span>'; }
    try {
        const fd = new FormData(); fd.append('order_item_id', orderItemId); fd.append('status', nextStatus);
        const res = await fetch(ITEM_STATUS_URL, { method: 'POST', body: fd });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const raw = await res.text();
        let data; try { data = JSON.parse(raw); } catch { if (btn) { btn.disabled=false; btn.innerHTML='<i class="fas fa-exclamation-triangle"></i> <span>Retry</span>'; } return; }
        if (!data.success) throw new Error(data.error || 'Update failed');
        if (kitchenModalOrderId) await refreshKitchenModal(kitchenModalOrderId);
        const row = document.querySelector(`tr[data-order-id="${data.order_id}"]`);
        if (row && data.order_status) {
            row.setAttribute('data-status', data.order_status);
            const statusCell  = row.querySelector('td[data-label="Status"]');
            const actionsCell = row.querySelector('td[data-label="Actions"]');
            const totalSpan   = row.querySelector('td[data-label="Total"] span');
            if (statusCell)  statusCell.innerHTML = getOrderStatusBadge(data.order_status);
            if (totalSpan)   totalSpan.className  = data.order_status === 'completed' ? 'price-total-paid' : 'price-total-unpaid';
            if (actionsCell) actionsCell.innerHTML = getOrderActionsHTML({ order_id:data.order_id, order_number:row.getAttribute('data-order-number'), total_amount:row.getAttribute('data-order-total'), status:data.order_status });
        }
    } catch (err) { console.error('Item status update error:', err.message); if (btn) { btn.disabled=false; btn.innerHTML='<i class="fas fa-exclamation-triangle"></i> <span>Retry</span>'; } }
}

// ============================================
// VOID + REFUND ITEM
// ============================================
async function voidRefundOrderItem(orderItemId, action, itemName, btn) {
    // Always treat as void+refund - no cancel option anymore
    pendingItemAction = { orderItemId, action: 'void_item', itemName, btn, refundAmount:0, refundMethod:'cash', refundNote:'' };

    const titleEl=document.querySelector('#cancelVoidItemModal .modal-title'), iconEl=document.getElementById('cancelVoidItemIcon');
    const headingEl=document.getElementById('cancelVoidItemHeading'), bodyEl=document.getElementById('cancelVoidItemBody');
    const confirmEl=document.getElementById('confirmCancelVoidItemBtn'), selEl=document.getElementById('cancelVoidItemReasonSelect');

    window._cancelVoidItemReason?.reset();
    _voidItemTotalPaid=0; _voidItemRefundCap=0; _voidItemRefundOpen=false;
    document.getElementById('voidItemRefundSection')?.remove();

    // Always show void+refund interface
    if (titleEl)   titleEl.innerHTML  = `<i class="fas fa-ban me-2" style="color:var(--primary-dark);"></i> Void + Refund Item`;
    if (iconEl)    { iconEl.innerHTML = `<i class="fas fa-ban"></i>`; iconEl.style.background='rgba(15,76,92,0.10)'; iconEl.style.color='var(--primary-dark)'; }
    if (headingEl) headingEl.textContent = `Void & Refund "${itemName}"?`;
    if (bodyEl)    bodyEl.innerHTML   = `This item will be <strong>voided</strong> from the order and <strong>refunded</strong> to the customer.<br><strong>Waste cost</strong> will be calculated based on item preparation status.`;
    if (confirmEl) { confirmEl.className='btn'; confirmEl.style.cssText='background:var(--primary);color:white;'; confirmEl.innerHTML='<i class="fas fa-ban me-1"></i> Yes, Void & Refund Item'; }

    if (selEl) { selEl.innerHTML='<option value="">— Loading reasons… —</option>'; await loadReasons('void_item', 'cancelVoidItemReasonSelect'); }
    cancelVoidItemModal?.show();

    // Always inject refund panel
    _injectVoidItemRefundPanel('loading');
    try {
        const orderId = kitchenModalOrderId;
        if (orderId) {
            const res  = await fetch(`${ORDER_DETAILS_URL}?order_id=${orderId}`, { headers:{'Cache-Control':'no-cache'} });
            const data = await res.json();
            if (data.success) {
                const d = data.data;
                _voidItemTotalPaid = parseFloat(d.payment?.amount_paid || 0);
                const matchedItem  = (d.items || []).find(i => String(i.order_item_id) === String(orderItemId));
                let itemSellingTotal = 0;
                if (matchedItem) {
                    // Use subtotal directly for consistency with PHP backend
                    const itemSubtotal = parseFloat(matchedItem.subtotal || 0);
                    const addonsTotal = (matchedItem.addons || []).reduce((s, a) => s + parseFloat(a.price||0)*parseInt(a.quantity||1), 0);
                    itemSellingTotal = Math.round((itemSubtotal + addonsTotal) * 100) / 100;
                }
                _voidItemRefundCap = _voidItemTotalPaid > 0 ? Math.min(itemSellingTotal, _voidItemTotalPaid) : itemSellingTotal;
                _injectVoidItemRefundPanel('ready');
            } else { _injectVoidItemRefundPanel('error'); }
        } else { _injectVoidItemRefundPanel('no-context'); }
    } catch (err) { console.error('voidItem refund fetch error:', err); _injectVoidItemRefundPanel('error'); }
}

async function executeVoidRefundItem(orderItemId, action, btn, reason, refundAmount=0, refundMethod='cash', refundNote='') {
    if (btn) { btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> <span>Processing...</span>'; }
    try {
        const fd = new FormData();
        fd.append('order_item_id', orderItemId); fd.append('action', action); fd.append('reason', reason);
        if (action === 'void_item') {
            fd.append('refund_amount', (refundAmount||0).toFixed(2));
            fd.append('refund_method', refundMethod||'cash');
            fd.append('refund_note',   refundNote||'');
        }
        const res  = await fetch(CANCEL_VOID_ITEM_URL, { method:'POST', body:fd });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Operation failed');
        if (kitchenModalOrderId) await refreshKitchenModal(kitchenModalOrderId);
        const row = document.querySelector(`tr[data-order-id="${data.order_id}"]`);
        if (row && data.order_status) {
            row.setAttribute('data-status', data.order_status);
            const statusCell=row.querySelector('td[data-label="Status"]'), actionsCell=row.querySelector('td[data-label="Actions"]'), totalSpan=row.querySelector('td[data-label="Total"] span');
            if (statusCell) statusCell.innerHTML = getOrderStatusBadge(data.order_status);
            if (totalSpan) { totalSpan.className=data.order_status==='completed'?'price-total-paid':'price-total-unpaid'; if(data.new_total!==undefined){totalSpan.textContent='?'+formatPrice(data.new_total);row.setAttribute('data-order-total',data.new_total);} }
            if (actionsCell) actionsCell.innerHTML = getOrderActionsHTML({ order_id:data.order_id, order_number:row.getAttribute('data-order-number'), total_amount:data.new_total??row.getAttribute('data-order-total'), status:data.order_status });
        }
        if (openDetailsOrderId && String(openDetailsOrderId)===String(data.order_id)) await fetchAndPopulateOrderDetails(data.order_id);
    } catch (err) { console.error('Void+refund item error:', err); showErrorModal('Operation Failed', err.message); if(btn){btn.disabled=false;btn.innerHTML='<i class="fas fa-exclamation-triangle"></i> <span>Retry</span>';} }
}

// ============================================
// VOID ORDER
// ============================================
async function voidOrder(orderId, orderNumber) {
    const numEl=document.getElementById('voidOrderNumber'), confEl=document.getElementById('confirmVoidOrderBtn');
    if (numEl)  numEl.textContent = orderNumber || '';
    if (confEl) { confEl.setAttribute('data-order-id',orderId); confEl.setAttribute('data-order-number',orderNumber); }

    const sel = document.getElementById('voidOrderReasonSelect');
    if (sel) { sel.innerHTML='<option value="">— Loading reasons… —</option>'; await loadReasons('void_order','voidOrderReasonSelect'); }

    resetVoidRefundPanel();
    voidOrderModal?.show();

    const paidHintEl = document.getElementById('voidRefundPaidHint');
    const refAmtEl   = document.getElementById('voidRefundAmount');

    try {
        const res  = await fetch(`${ORDER_DETAILS_URL}?order_id=${orderId}`, { headers:{'Cache-Control':'no-cache'} });
        const resp = await res.json();
        if (!resp.success) throw new Error(resp.message || 'Failed to load');

        const d = resp.data;
        _voidOrderTotalPaid = parseFloat(d.payment?.amount_paid || 0);
        const orderStatus = d.status || 'pending';

        // Update payment status display
        const paymentStatusEl = document.getElementById('voidPaymentStatus');
        if (paymentStatusEl) {
            if (_voidOrderTotalPaid > 0) {
                paymentStatusEl.textContent = `Customer paid: ₱${_voidOrderTotalPaid.toFixed(2)} - refund available`;
                paymentStatusEl.style.color = 'var(--success)';
            } else {
                paymentStatusEl.textContent = 'No payment recorded - no refund needed';
                paymentStatusEl.style.color = 'var(--warm-gray)';
            }
        }

        // Compute selling total of items (all active items for pending orders, ready items for others)
        let itemsSellingTotal = 0;
        (d.items || []).forEach(item => {
            // Skip voided/cancelled items - they're already processed
            if (['voided', 'cancelled'].includes(item.item_status)) return;
            
            // For pending orders, include all active items since customer paid but order wasn't processed
            // For other statuses, only include ready items
            if (orderStatus === 'pending' || item.item_status === 'ready') {
                // Use subtotal directly for consistency with PHP backend
                const itemSubtotal = parseFloat(item.subtotal || 0);
                const addonsTotal = (item.addons || []).reduce((s,a) => s + parseFloat(a.price||0)*parseInt(a.quantity||1), 0);
                itemsSellingTotal += itemSubtotal + addonsTotal;
            }
        });
        itemsSellingTotal = Math.round(itemsSellingTotal * 100) / 100;

        _voidOrderRefundCap = _voidOrderTotalPaid > 0
            ? Math.min(itemsSellingTotal, _voidOrderTotalPaid)
            : itemsSellingTotal;

        if (paidHintEl) paidHintEl.textContent = _voidOrderTotalPaid > 0
            ? `Paid: ₱${_voidOrderTotalPaid.toFixed(2)} · Max refund: ₱${_voidOrderRefundCap.toFixed(2)}`
            : 'No payment recorded - refund not applicable.';

        if (refAmtEl) {
            refAmtEl.disabled    = _voidOrderRefundCap <= 0;
            refAmtEl.placeholder = _voidOrderRefundCap > 0 ? `0.00 - ${_voidOrderRefundCap.toFixed(2)}` : 'No payment on file';
            refAmtEl.max         = _voidOrderRefundCap > 0 ? _voidOrderRefundCap.toFixed(2) : '0';
        }

        buildRefundPresets(d.items || [], _voidOrderTotalPaid, _voidOrderRefundCap);

    } catch (err) {
        console.error('voidOrder fetch error:', err);
        if (paidHintEl) paidHintEl.textContent = 'Could not fetch payment info.';
        if (refAmtEl)   refAmtEl.disabled = true;
    }

    window._voidOrderReason?.reset();
}

async function executeVoidOrder(orderId, orderNumber, reason, refundAmount, refundMethod, refundNote) {
    try {
        const fd = new FormData();
        fd.append('order_id',      orderId);
        fd.append('reason',        reason);
        fd.append('refund_amount', (refundAmount||0).toFixed(2));
        fd.append('refund_method', refundMethod||'cash');
        fd.append('refund_note',   refundNote||'');
        const res  = await fetch(VOID_ORDER_URL, { method:'POST', body:fd });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Void failed');

        const row = document.querySelector(`tr[data-order-id="${data.order_id}"]`);
        if (row) {
            row.setAttribute('data-status','voided');
            const statusCell=row.querySelector('td[data-label="Status"]'), actionsCell=row.querySelector('td[data-label="Actions"]'), totalSpan=row.querySelector('td[data-label="Total"] span');
            if (statusCell) statusCell.innerHTML = getOrderStatusBadge('voided');
            if (totalSpan)  { totalSpan.className='price-total-unpaid'; totalSpan.textContent='₱0.00'; row.setAttribute('data-order-total','0'); }
            if (actionsCell) actionsCell.innerHTML = getOrderActionsHTML({ order_id:data.order_id, order_number:row.getAttribute('data-order-number'), total_amount:0, status:'voided' });
        }
        if (kitchenModalOrderId && String(kitchenModalOrderId)===String(orderId)) { kitchenItemsModal?.hide(); kitchenModalOrderId=null; }
        await refreshOrders();
    } catch (err) { console.error('Void order error:', err); showErrorModal('Void Failed', err.message); }
}

// ============================================
// POST-COMPLETION REFUND MODAL
// ============================================
async function openRefundModal(orderId, orderNumber) {
    _refundOrderId = orderId;
    _refundOrderNumber = orderNumber;
    _refundType = 'full';

    // Reset modal UI
    document.getElementById('refundModalOrderNumber').textContent = orderNumber || '';
    document.getElementById('refundModalError').style.display = 'none';
    document.getElementById('refundReasonError').style.display = 'none';
    document.getElementById('refundReasonSelect').value = '';
    document.getElementById('refundReasonText').value = '';
    document.getElementById('refundReasonText').style.display = 'none';
    document.getElementById('refundMethod').value = 'cash';
    document.getElementById('refundFullPanel').style.display = 'block';
    document.getElementById('refundPartialPanel').style.display = 'none';

    document.querySelectorAll('.refund-type-btn').forEach(b => {
        const active = b.dataset.type === 'full';
        b.classList.toggle('active', active);
        b.style.background  = active ? 'var(--success)' : 'white';
        b.style.color       = active ? 'white' : 'var(--navy)';
        b.style.borderColor = active ? 'var(--success)' : 'var(--border)';
    });

    refundModal?.show();

    try {
        const res  = await fetch(`${ORDER_DETAILS_URL}?order_id=${orderId}`, { headers:{'Cache-Control':'no-cache'} });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed to load order.');
        const d = data.data;

        _refundOrderTotal = parseFloat(d.order?.total_amount || 0);
        _refundAlready    = parseFloat(d.refunds?.total_refunded || 0);

        // Filter out cancelled or voided items
        _refundItems = (d.items || []).filter(i => !['cancelled','voided'].includes(i.item_status));

        // Calculate remaining refund per item and sum it
        _refundRemaining = _refundItems.reduce((sum, item) => {
            const refunded = parseFloat(item.refunded_amount || 0);
            const subtotal = parseFloat(item.subtotal || 0);
            return sum + Math.max(0, subtotal - refunded);
        }, 0);

        // Update modal UI
        document.getElementById('refundOrderTotal').textContent      = '₱' + formatPrice(_refundOrderTotal);
        document.getElementById('refundAlreadyRefunded').textContent = '₱' + formatPrice(_refundAlready);
        document.getElementById('refundRemaining').textContent       = '₱' + formatPrice(_refundRemaining);
        document.getElementById('refundFullAmount').textContent      = '₱' + formatPrice(_refundRemaining);

        buildRefundItemsList(_refundItems);

    } catch (err) {
        const errEl = document.getElementById('refundModalError');
        if (errEl) {
            errEl.textContent = 'Failed to load order details: ' + err.message;
            errEl.style.display = 'block';
        }
    }
}

function buildRefundItemsList(items) {
    const container = document.getElementById('refundItemsList');
    if (!container) return;
    if (!items || items.length === 0) { container.innerHTML='<div style="padding:1rem;text-align:center;color:var(--warm-gray);font-size:0.83rem;">No refundable items.</div>'; return; }
    container.innerHTML = items.map(item => {
        // Use subtotal directly for consistency with PHP backend
        const itemSubtotal = parseFloat(item.subtotal || 0);
        const addonsTotal = (item.addons||[]).reduce((s,a)=>s+parseFloat(a.price||0)*parseInt(a.quantity||1),0);
        const lineTotal   = itemSubtotal + addonsTotal;
        const label       = item.variant_name_snapshot?`${item.item_name_snapshot||item.item_name} · ${item.variant_name_snapshot}`:(item.item_name_snapshot||item.item_name||'');
        return `<div class="refund-item-row">
            <input type="checkbox" class="refund-item-check" data-item-id="${item.order_item_id}" data-max="${lineTotal.toFixed(2)}" id="ritem_${item.order_item_id}">
            <label for="ritem_${item.order_item_id}" style="flex:1;cursor:pointer;line-height:1.35;">
                <div style="font-weight:600;color:var(--navy);">${escapeHtml(label)}</div>
                <div style="font-size:0.76rem;color:var(--warm-gray);">×${item.quantity} · ₱${formatPrice(item.unit_price)} each${item.addons&&item.addons.length?' + add-ons':''}</div>
            </label>
            <div style="display:flex;align-items:center;gap:0.3rem;">
                <span style="font-size:0.75rem;color:var(--warm-gray);">₱</span>
                <input type="number" class="refund-item-amount" data-item-id="${item.order_item_id}" min="0.01" step="0.01" max="${lineTotal.toFixed(2)}" value="${lineTotal.toFixed(2)}" disabled placeholder="${lineTotal.toFixed(2)}">
            </div>
        </div>`;
    }).join('');
    container.querySelectorAll('.refund-item-check').forEach(cb => {
        cb.addEventListener('change', function () {
            const inp = container.querySelector(`.refund-item-amount[data-item-id="${this.dataset.itemId}"]`);
            if (inp) inp.disabled = !this.checked;
            updateRefundPartialTotal();
        });
    });
    container.querySelectorAll('.refund-item-amount').forEach(inp => { inp.addEventListener('input', updateRefundPartialTotal); });
}

function updateRefundPartialTotal() {
    let total = 0;
    document.querySelectorAll('.refund-item-check:checked').forEach(cb => {
        const inp = document.querySelector(`.refund-item-amount[data-item-id="${cb.dataset.itemId}"]`);
        if (inp) total += parseFloat(inp.value||0)||0;
    });
    const el = document.getElementById('refundPartialTotal'); if (el) el.textContent = '₱'+formatPrice(total);
}

async function executeRefund() {
    const errorEl = document.getElementById('refundModalError');
    errorEl.style.display = 'none';
    const reasonSel = document.getElementById('refundReasonSelect');
    const reasonTa  = document.getElementById('refundReasonText');
    let reason = reasonSel?.value || '';
    if (reason === 'Other') reason = reasonTa?.value.trim() || '';
    if (!reason) { document.getElementById('refundReasonError').style.display='block'; return; }
    const method = document.getElementById('refundMethod')?.value || 'cash';
    const btn    = document.getElementById('confirmRefundBtn');
    const fd     = new FormData();
    fd.append('order_id', _refundOrderId); fd.append('refund_type', _refundType); fd.append('reason', reason); fd.append('refund_method', method);
    if (_refundType === 'partial') {
        const selectedItems = [];
        document.querySelectorAll('.refund-item-check:checked').forEach(cb => {
            const inp = document.querySelector(`.refund-item-amount[data-item-id="${cb.dataset.itemId}"]`);
            const amt = parseFloat(inp?.value||0)||0;
            if (amt > 0) selectedItems.push({ order_item_id:parseInt(cb.dataset.itemId), amount:amt });
        });
        if (selectedItems.length === 0) { errorEl.textContent='Please select at least one item to refund.'; errorEl.style.display='block'; return; }
        fd.append('items', JSON.stringify(selectedItems));
    }
    if (btn) { btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin me-1"></i> Processing…'; }
    try {
        const res  = await fetch(PROCESS_REFUND_URL, { method:'POST', body:fd });
        const data = await safeJSONParse(res);
        if (!data.success) throw new Error(data.error || 'Refund failed.');
        
        refundModal?.hide();
        if (openDetailsOrderId) await fetchAndPopulateOrderDetails(openDetailsOrderId);
        await refreshOrders();
    } catch (err) { errorEl.textContent=err.message; errorEl.style.display='block'; }
    finally { if (btn) { btn.disabled=false; btn.innerHTML='<i class="fas fa-rotate-left me-1"></i> Confirm Refund'; } }
}

window.openRefundModal = openRefundModal;

// ============================================
// KITCHEN ITEMS MODAL
// ============================================
async function openKitchenItemsModal(orderId, orderNumber, orderStatus) {
    const modal=document.getElementById('kitchenItemsModal');
    if (!modal||!kitchenItemsModal) return;
    kitchenModalOrderId=String(orderId); modal.setAttribute('data-open-order-id',String(orderId));
    const bodyEl=document.getElementById('kitchenModalBody'), headerEl=document.getElementById('kitchenModalHeader');
    if (bodyEl)   bodyEl.innerHTML = `<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm mb-2" style="color:var(--primary);" role="status"></div><p class="mb-0" style="font-size:0.85rem;">Loading…</p></div>`;
    if (headerEl) headerEl.innerHTML = '';
    kitchenItemsModal.show();
    await refreshKitchenModal(orderId);
}

async function refreshKitchenModal(orderId) {
    const modal=document.getElementById('kitchenItemsModal');
    if (!modal||!modal.classList.contains('show')) return;
    if (String(kitchenModalOrderId)!==String(orderId)) return;
    try {
        const res=await fetch(`${ORDER_ITEMS_URL}?order_id=${orderId}`, { headers:{'Cache-Control':'no-cache'} });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data=await res.json(); if (!data.success) return;
        renderKitchenModal(data.order, data.items);
    } catch (err) { console.error('Kitchen modal refresh error:', err); }
}

function renderKitchenModal(order, items) {
    const modal=document.getElementById('kitchenItemsModal');
    if (!modal||!modal.classList.contains('show')) return;
    const orderStatus=order.status||'pending', headerEl=document.getElementById('kitchenModalHeader');
    if (headerEl) headerEl.innerHTML=`<div style="display:flex;align-items:center;gap:0.75rem;"><div style="width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:1.1rem;"><i class="fas fa-receipt"></i></div><div><div style="font-size:1.1rem;font-weight:700;color:white;">Order #${escapeHtml(order.order_number)}</div><div style="font-size:0.78rem;opacity:0.75;color:white;">${items.length} item${items.length!==1?'s':''} &nbsp;·&nbsp; ${order.order_type==='dine_in'?'Dine In':order.order_type==='takeout'?'Takeout':'—'}</div></div></div><div style="margin-left:auto;"><span class="kitchen-modal-status-pill kitchen-modal-status-${orderStatus}">${capitalize(orderStatus)}</span></div>`;
    const bodyEl=document.getElementById('kitchenModalBody');
    if (bodyEl) {
        const activeCount=items.filter(i=>!['cancelled','voided','completed'].includes(i.item_status||'pending')).length;
        // Pass payment info to item cards for proper void/refund logic
        const paymentInfo = order.payment || {};
        bodyEl.innerHTML=items.length?'<div class="kitchen-modal-items">'+items.map(item=>buildItemCardHTML(item,orderStatus,activeCount,paymentInfo)).join('')+'</div>':'<p class="text-muted text-center py-3">No items found.</p>';
    }
}

function buildItemCardHTML(item, orderStatus, activeItemCount=99, paymentInfo) {
    const isAdmin=window.userRole==='admin', isCashier=window.userRole==='cashier', canUpdate=window.userRole==='kitchen'||isAdmin;
    const orderDone=['completed','cancelled','voided'].includes(orderStatus);
    const itemStatus=item.item_status||'pending', isTerminal=['completed','cancelled','voided'].includes(itemStatus);
    const dc={pending:{label:'Pending',icon:'fa-hourglass-half',cls:'item-status-pending'},preparing:{label:'Preparing',icon:'fa-bowl-food',cls:'item-status-preparing'},ready:{label:'Ready',icon:'fa-circle-check',cls:'item-status-ready'},completed:{label:'Completed',icon:'fa-check-double',cls:'item-status-completed'},cancelled:{label:'Cancelled',icon:'fa-times-circle',cls:'item-status-cancelled'},voided:{label:'Voided',icon:'fa-ban',cls:'item-status-voided'}};
    const sc=dc[itemStatus]||dc.pending, badge=`<span class="item-status-badge ${sc.cls}"><i class="fas ${sc.icon}"></i> ${sc.label}</span>`;
    let progressBtn='';
    if (canUpdate&&!orderDone&&!isTerminal) {
        if (itemStatus==='pending') {
            const ns='preparing', bc={label:'Start Cooking',icon:'fa-play',cls:'item-action-start'};
            progressBtn=`<button type="button" class="item-status-btn ${bc.cls}" data-order-item-id="${item.order_item_id}" data-next-status="${ns}"><i class="fas ${bc.icon}"></i><span>${bc.label}</span></button>`;
        } else if (itemStatus==='preparing') {
            const ns='ready', bc={label:'Mark Ready',icon:'fa-check',cls:'item-action-ready'};
            progressBtn=`<button type="button" class="item-status-btn ${bc.cls}" data-order-item-id="${item.order_item_id}" data-next-status="${ns}"><i class="fas ${bc.icon}"></i><span>${bc.label}</span></button>`;
        }
    }
    const onlyOneLeft=activeItemCount<=1;
    const hasPayment = paymentInfo && parseFloat(paymentInfo.amount_paid || 0) > 0;
    let adminBtn='';
    // Show void options for all items when payment is made (pay-first system)
    if (hasPayment && (isAdmin||isCashier) && !orderDone && !isTerminal && !onlyOneLeft) {
        adminBtn=`<button type="button" class="item-status-btn item-action-void-item" data-order-item-id="${item.order_item_id}" data-action="void_item" data-item-name="${escapeHtml(item.item_name||'')}"><i class="fas fa-ban"></i><span>Void</span></button>`;
    } 
    // For unpaid orders, keep existing logic (cancel for pending items)
    else if (!hasPayment && (isAdmin||isCashier)&&!orderDone&&!isTerminal&&itemStatus==='pending'&&!onlyOneLeft) {
        adminBtn=`<button type="button" class="item-status-btn item-action-void-item" data-order-item-id="${item.order_item_id}" data-action="void_item" data-item-name="${escapeHtml(item.item_name||'')}"><i class="fas fa-ban"></i><span>Void</span></button>`;
    } 
    // For unpaid orders with ready items, show void
    else if (!hasPayment && isAdmin&&!orderDone&&!isTerminal&&itemStatus==='ready'&&!onlyOneLeft) {
        adminBtn=`<button type="button" class="item-status-btn item-action-void-item" data-order-item-id="${item.order_item_id}" data-action="void_item" data-item-name="${escapeHtml(item.item_name||'')}"><i class="fas fa-ban"></i><span>Void</span></button>`;
    }
    const bn=escapeHtml(item.item_name||''), vn=item.variant_name_snapshot||item.current_variant_name||item.variant_name||null;
    const addonsHTML=(item.addons&&item.addons.length)?`<div class="item-addons"><span class="item-addons-label">Add-ons:</span>${item.addons.map(a=>`<span class="item-addon-tag">${escapeHtml(a.addon_name)}${a.quantity>1?` ×${a.quantity}`:''}</span>`).join('')}</div>`:'';
    const noteHTML=(item.item_notes&&item.item_notes.trim())?`<div class="item-card-note-inline"><span class="item-card-note-label">Note:</span><span class="item-card-note-text">${escapeHtml(item.item_notes)}</span></div>`:'';
    // Add waste cost highlighting for voided items
    const wasteHighlight = (itemStatus === 'voided' && item.waste_cost === null) 
        ? '<div class="waste-indicator">No waste cost</div>' 
        : (item.waste_cost > 0 
            ? `<div class="waste-indicator">Waste: ${peso(item.waste_cost)}</div>` 
            : '');

    return `<div class="item-card item-card-${itemStatus}" data-item-id="${item.order_item_id}"><div class="item-card-header"><div class="item-card-name"><span class="item-name-text">${bn}${vn?`<span class="item-variant-label"> · ${escapeHtml(vn)}</span>`:''}</span><span class="item-qty-badge">×${item.quantity||1}</span></div><div class="item-card-right">${badge}${progressBtn}${adminBtn}</div></div>${addonsHTML}${noteHTML}${wasteHighlight}</div>`;
}

// ============================================
// ORDER DETAILS MODAL
// ============================================
function viewOrderDetails(orderId, orderNumber) {
    if (!orderDetailsModal) return;
    openDetailsOrderId=String(orderId);
    ['orderDetailsLoader','orderDetailsContent','orderDetailsError'].forEach((id,i)=>{ const el=document.getElementById(id); if(el) el.style.display=i===0?'block':'none'; });
    const numEl=document.getElementById('detailOrderNumber'); if(numEl) numEl.textContent='#'+(orderNumber||'');
    const printBtn=document.getElementById('printReceiptBtn'), refundBtn=document.getElementById('refundOrderBtn');
    if (printBtn)  printBtn.style.display  = 'none';
    if (refundBtn) refundBtn.style.display = 'none';
    orderDetailsModal.show();
    fetchAndPopulateOrderDetails(orderId);
}

async function fetchAndPopulateOrderDetails(orderId) {
    const loaderEl=document.getElementById('orderDetailsLoader'), contentEl=document.getElementById('orderDetailsContent'), errorEl=document.getElementById('orderDetailsError');
    try {
        const res=await fetch(`${ORDER_DETAILS_URL}?order_id=${orderId}`, { headers:{'Cache-Control':'no-cache'} });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data=await res.json(); if (!data.success) throw new Error(data.message||'Failed to load');
        const d=data.data;
        // Always pass d.refunds (unified table). d.refund (old payments lookup) intentionally ignored.
        populateOrderDetailsModal(d.order||{}, d.items||[], d.payment||null, d.cancel_audit||null, d.void_audit||null, d.refunds||null);
        if(loaderEl) loaderEl.style.display='none'; if(contentEl) contentEl.style.display='block'; if(errorEl) errorEl.style.display='none';
    } catch (err) {
        console.error('Order details error:', err);
        if(loaderEl) loaderEl.style.display='none'; if(errorEl) errorEl.style.display='block'; if(contentEl) contentEl.style.display='none';
    }
}

async function refreshOrderDetailsModal(orderId) {
    const modal=document.getElementById('orderDetailsModal');
    if (!modal||!modal.classList.contains('show')) return;
    try {
        const res=await fetch(`${ORDER_DETAILS_URL}?order_id=${orderId}`, { headers:{'Cache-Control':'no-cache'} });
        const data=await res.json(); if (!data.success) return;
        const d=data.data;
        populateOrderDetailsModal(d.order||{}, d.items||[], d.payment||null, d.cancel_audit||null, d.void_audit||null, d.refunds||null);
    } catch (err) { /* silent */ }
}

// ─────────────────────────────────────────────────────────────────────────────
// populateOrderDetailsModal
//
// refunds = d.refunds from get_order_details.php
// Shape: { rows: [...], total_refunded: float, count: int } | null
//
// ALL refund types shown in detailRefundsHistorySection:
//   'void_order' → whole order voided, refund issued
//   'void_item'  → single item voided, partial refund issued
//   'post_sale'  → post-completion admin refund
//
// Receipt (printFinalReceipt / printVoidReceipt):
//   - Completed orders → printFinalReceipt (shows payment + refunds if any)
//   - Voided orders    → printVoidReceipt  (shows waste, refund amount, net)
// ─────────────────────────────────────────────────────────────────────────────
function populateOrderDetailsModal(order, items, payment, cancelAudit, voidAudit, refunds) {
    window._lastOrderRefunds = refunds || null;

    const set=(id,val)=>{ const el=document.getElementById(id); if(el) el.textContent=val; };
    const setHTML=(id,html)=>{ const el=document.getElementById(id); if(el) el.innerHTML=html; };

    const numEl=document.getElementById('detailOrderNumber'); if(numEl) numEl.textContent='#'+(order.order_number||'');
    set('detailCustomer', order.customer_name||'Walk-in');
    if (order.created_at) set('detailDate', new Date(order.created_at).toLocaleString('en-PH',{year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}));
    set('detailCashier', order.cashier_name||'N/A');
    setHTML('detailOrderType', order.order_type ? getOrderTypeBadge(order.order_type) : '—');
    setHTML('detailStatus', getOrderStatusBadge(order.status));

    const tableWrap=document.getElementById('detailTableWrap'), tableEl=document.getElementById('detailTable');
    if (order.table_name) { if(tableEl) tableEl.innerHTML=`<span class="table-number-badge"><i class="fas fa-chair"></i> ${escapeHtml(order.table_name)}</span>`; if(tableWrap) tableWrap.style.display=''; }
    else { if(tableWrap) tableWrap.style.display='none'; }

    const itemsBody=document.getElementById('detailItemsBody');
    if (itemsBody) {
        itemsBody.innerHTML='';
        items.forEach(item => {
            const itemSub=(parseFloat(item.unit_price||0)*parseInt(item.quantity||1)).toFixed(2);
            const dn=item.item_name_snapshot||item.item_name||'', dvn=item.variant_name_snapshot||item.current_variant_name||null;
            const vBit=dvn?`<span class="od-variant-label"> · ${escapeHtml(dvn)}</span>`:'';
            let statusBit='', reasonPill='', notePill='';
            if (['cancelled','voided'].includes(item.item_status)) statusBit=` <span class="item-status-badge item-status-${item.item_status}" style="font-size:0.68rem;padding:0.12rem 0.45rem;vertical-align:middle;">${capitalize(item.item_status)}</span>`;
            if (item.item_status==='cancelled'&&item.cancel_reason&&order.status!=='cancelled') reasonPill=`<div class="od-note-pill" style="background:rgba(214,64,69,0.08);border-color:rgba(214,64,69,0.3);color:#9e3a34;"><i class="fas fa-times-circle" style="color:var(--danger);"></i><span style="font-style:normal;font-weight:600;">Cancel reason:</span> ${escapeHtml(item.cancel_reason)}</div>`;
            else if (item.item_status==='voided'&&item.void_reason&&order.status!=='voided') reasonPill=`<div class="od-note-pill" style="background:rgba(15,76,92,0.07);border-color:rgba(15,76,92,0.25);color:var(--primary-dark);"><i class="fas fa-ban" style="color:var(--primary-dark);"></i><span style="font-style:normal;font-weight:600;">Void reason:</span> ${escapeHtml(item.void_reason)}</div>`;
            if (item.item_notes&&item.item_notes.trim()) notePill=`<div class="od-note-pill"><i class="fas fa-pen-to-square"></i> ${escapeHtml(item.item_notes)}</div>`;
            const tr=document.createElement('tr'); tr.className='od-item-row';
            if (['cancelled','voided'].includes(item.item_status)) tr.style.opacity='0.6';
            tr.innerHTML=`<td class="od-td od-td-item"><span class="od-item-name" style="${item.item_status==='cancelled'?'text-decoration:line-through;':''}">${escapeHtml(dn)}${vBit}</span>${statusBit}${notePill}${reasonPill}</td><td class="od-td od-td-center">${item.quantity||1}</td><td class="od-td od-td-right od-muted">₱${formatPrice(item.unit_price)}</td><td class="od-td od-td-right od-item-subtotal"${['cancelled','voided'].includes(item.item_status)?' style="text-decoration:line-through;color:var(--warm-gray);"':''}>₱${itemSub}</td>`;
            itemsBody.appendChild(tr);
            (item.addons||[]).forEach(addon=>{
                const aStmt=(parseFloat(addon.price||0)*parseInt(addon.quantity||1)).toFixed(2);
                const aTr=document.createElement('tr'); aTr.className='od-addon-row';
                aTr.innerHTML=`<td class="od-td od-td-addon"><i class="fas fa-corner-down-right od-addon-icon"></i>${escapeHtml(addon.addon_name)}</td><td class="od-td od-td-center od-muted">${addon.quantity||1}</td><td class="od-td od-td-right od-muted">₱${formatPrice(addon.price)}</td><td class="od-td od-td-right od-muted">₱${aStmt}</td>`;
                itemsBody.appendChild(aTr);
            });
        });
    }

    const total=parseFloat(order.total_amount||0), subtotal=total/1.12, vat=total-subtotal;
    set('detailSubtotal', subtotal.toFixed(2)); set('detailVat', vat.toFixed(2)); set('detailTotal', total.toFixed(2));
    const totalDisplay=document.getElementById('detailTotalDisplay');
    if (totalDisplay) totalDisplay.style.color=order.status==='completed'?'var(--success)':'var(--danger)';

    // Hide all audit sections
    ['detailPaymentSection','detailCancelSection','detailVoidSection','detailRefundSection','detailRefundsHistorySection'].forEach(id=>{
        const el=document.getElementById(id); if(el) el.style.display='none';
    });

    // Clear any existing waste cost display to prevent duplicates
    const existingWasteRows = document.querySelectorAll('.od-payment-row[data-waste-cost]');
    existingWasteRows.forEach(row => row.remove());

    if (payment && order.status==='completed') {
        const sec=document.getElementById('detailPaymentSection');
        if (sec) {
            sec.style.display='';
            set('detailPayMethod',  payment.payment_method||'—');
            set('detailAmountPaid', formatPrice(payment.amount_paid));
            set('detailChange',     formatPrice(payment.change_amount));
            const pbRow=document.getElementById('detailProcessedByRow'), pbEl=document.getElementById('detailProcessedBy');
            if (pbRow&&pbEl) {
                if (payment.processed_by&&parseInt(payment.processed_by)!==parseInt(order.cashier_id)&&payment.processed_by_name) { pbEl.textContent=payment.processed_by_name; pbRow.style.display=''; }
                else pbRow.style.display='none';
            }
            
            // Calculate and display waste cost for refunded items in completed orders
            if (refunds && refunds.rows && refunds.rows.length > 0) {
                // Check if any refunds are post_sale type (refunded completed orders) OR void_order type (full refunds)
                const hasRefundedItems = refunds.rows.some(r => r.refund_type === 'post_sale' || r.refund_type === 'void_order');
                if (hasRefundedItems) {
                    // Calculate total waste cost from items with waste_cost
                    const totalWasteCost = items.reduce((sum, item) => {
                        if (item.waste_cost && item.waste_cost > 0) {
                            return sum + parseFloat(item.waste_cost);
                        }
                        return sum;
                    }, 0);
                    
                    if (totalWasteCost > 0) {
                        // Add waste cost display after payment details
                        const wasteHtml = `
                            <div class="od-payment-row" data-waste-cost="true" style="margin-top:8px;padding-top:8px;border-top:1px solid var(--border);">
                                <span style="color:var(--secondary);font-weight:600;">
                                    <i class="fas fa-trash-alt" style="margin-right:0.3rem;"></i>
                                    Waste Cost (Refunded Items)
                                </span>
                                <span style="font-weight:700;color:var(--secondary);">₱${formatPrice(totalWasteCost)}</span>
                            </div>
                        `;
                        // Insert waste cost after the change amount row
                        const changeRow = sec.querySelector('.od-payment-row:last-child');
                        if (changeRow) {
                            changeRow.insertAdjacentHTML('afterend', wasteHtml);
                        }
                    }
                }
            }
        }
    } else if (order.status==='cancelled') {
        const sec=document.getElementById('detailCancelSection');
        if (sec) { sec.style.display=''; set('detailCancelReason', cancelAudit?.reason||'—'); }
    } else if (order.status==='voided') {
        const sec=document.getElementById('detailVoidSection');
        if (sec) {
            sec.style.display='';
            set('detailVoidReason', voidAudit?.reason||'—');
            const wcEl=document.getElementById('detailWasteCost');
            if (wcEl) wcEl.textContent=voidAudit?.total_waste_cost!==undefined?'₱'+formatPrice(voidAudit.total_waste_cost):'—';
        }
    }

    // ── Unified refunds history — ALL types, ALL statuses ────────────────────
    // Source: d.refunds from refunds table (void_order, void_item, post_sale)
    // Shown whenever any refund exists, regardless of order status.
    const refundHistSection=document.getElementById('detailRefundsHistorySection');
    if (refundHistSection) {
        if (refunds && refunds.rows && refunds.rows.length > 0) {
            refundHistSection.style.display='';
            const typeLabel = type => type==='void_order'?'Void refund':type==='void_item'?'Item void refund':'Refund';
            const list=document.getElementById('detailRefundsList');
            if (list) {
                list.innerHTML = refunds.rows.map(r => `
                    <div class="od-payment-row">
                        <span style="color:var(--warm-gray);font-size:0.79rem;">
                            ${typeLabel(r.refund_type)} · ${escapeHtml((r.refund_method||'cash').toUpperCase())}
                            ${r.note ? `<span style="font-weight:400;"> — ${escapeHtml(r.note)}</span>` : ''}
                        </span>
                        <span style="font-weight:700;color:var(--danger);">−₱${formatPrice(r.amount)}</span>
                    </div>
                    <div class="od-payment-row" style="margin-top:-4px;margin-bottom:4px;">
                        <span style="font-size:0.73rem;color:var(--warm-gray);padding-left:0.5rem;">
                            ${escapeHtml(r.reason)} · ${escapeHtml(r.processed_by_name||'—')}
                        </span>
                        <span style="font-size:0.73rem;color:var(--warm-gray);">
                            ${new Date(r.created_at).toLocaleDateString('en-PH',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'})}
                        </span>
                    </div>`).join('<hr style="margin:2px 0;border-color:var(--border);">');
                const totalEl=document.getElementById('detailRefundsTotalAmount');
                if (totalEl) totalEl.textContent='₱'+formatPrice(refunds.total_refunded);
            }
        } else {
            refundHistSection.style.display='none';
        }
    }

    // ── Print / Refund buttons ────────────────────────────────────────────────
    const printBtn=document.getElementById('printReceiptBtn');
    if (printBtn) {
        if (order.status==='completed' || order.status==='voided') {
            printBtn.style.display='';
            printBtn.removeAttribute('href'); printBtn.setAttribute('target','');
            if (order.status==='completed') {
                printBtn.onclick=()=>{ printFinalReceipt(order,items,payment,refunds); return false; };
            } else {
                // voided — show void receipt with waste + refund info
                printBtn.onclick=()=>{ printVoidReceipt(order,items,voidAudit,refunds); return false; };
            }
        } else { printBtn.style.display='none'; printBtn.onclick=null; }
    }

    const refundBtn=document.getElementById('refundOrderBtn');
    if (refundBtn) {
        if (order.status==='completed'&&window.userRole==='admin') {
            refundBtn.style.display='flex';
            refundBtn.onclick=()=>{ orderDetailsModal?.hide(); setTimeout(()=>openRefundModal(order.order_id,order.order_number),300); };
        } else { refundBtn.style.display='none'; refundBtn.onclick=null; }
    }
}

// ============================================
// RECEIPT HELPERS
// ============================================

/**
 * Build the refund section HTML for any receipt.
 * Shows each refund row and a net total line.
 *
 * @param {number}      orderTotal  - original order total (before refunds)
 * @param {object|null} refunds     - d.refunds: { rows, total_refunded }
 * @returns {string}  HTML string to embed in the receipt
 */
function _buildReceiptRefundsSection(orderTotal, refunds) {
    if (!refunds || !refunds.rows || refunds.rows.length === 0) return '';

    const totalRefunded = parseFloat(refunds.total_refunded || 0);
    const net           = Math.max(0, orderTotal - totalRefunded);

    const typeLabel = type =>
        type === 'void_order' ? 'Void Refund'
        : type === 'void_item'  ? 'Item Void Refund'
        : 'Refund';

    const rows = refunds.rows.map(r =>
        `<div style="display:flex;justify-content:space-between;font-size:10px;margin-bottom:2px;">
            <span>${typeLabel(r.refund_type)} — ${escapeHtml(r.reason)}</span>
            <span>−₱${parseFloat(r.amount).toFixed(2)}</span>
        </div>`
    ).join('');

    return `
        <hr style="border:none;border-top:1px dashed #000;margin:5px 0;">
        <div style="font-size:10px;font-weight:bold;margin-bottom:3px;text-transform:uppercase;">Refunds Issued</div>
        ${rows}
        <div style="display:flex;justify-content:space-between;font-size:10px;font-weight:bold;border-top:1px solid #000;margin-top:3px;padding-top:3px;">
            <span>Total Refunded:</span>
            <span>−₱${totalRefunded.toFixed(2)}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:bold;margin-top:3px;">
            <span>Net Amount:</span>
            <span>₱${net.toFixed(2)}</span>
        </div>`;
}

// ============================================
// PRINT FINAL RECEIPT (completed orders)
// ============================================
function printFinalReceipt(order, items, payment, refunds) {
    const orderNumber = order.order_number || '—';
    const customer    = order.customer_name || 'Walk-in';
    const cashier     = order.cashier_name  || '—';
    const tableName   = order.table_name    || null;
    const orderType   = order.order_type === 'dine_in' ? 'Dine In' : 'Take Out';

    const grossTotal = parseFloat(order.total_amount || 0);
    const amountPaid = parseFloat(payment?.amount_paid || grossTotal);
    const changeAmt  = parseFloat(payment?.change_amount || 0);
    const payMethod  = (payment?.payment_method || 'cash').toUpperCase();

    const now     = order.completed_at ? new Date(order.completed_at) : new Date();
    const dateStr = now.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    const timeStr = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true });

    let itemsHTML = '';
    items.forEach(item => {
        if (['cancelled', 'voided'].includes(item.item_status)) return;
        const addonsTotal = (item.addons || []).reduce((s, a) => s + parseFloat(a.price || 0) * parseInt(a.quantity || 1), 0);
        const lineTotal   = (parseFloat(item.unit_price || 0) + addonsTotal) * parseInt(item.quantity || 1);
        const label       = item.variant_name_snapshot
            ? `${item.item_name_snapshot || item.item_name} · ${item.variant_name_snapshot}`
            : (item.item_name_snapshot || item.item_name || '');
        itemsHTML += `<div style="display:flex;margin-bottom:3px;">
            <span style="flex:2.5;word-break:break-word;padding-right:4px;">${escapeHtml(label)}</span>
            <span style="flex:0.5;text-align:center;">${item.quantity || 1}</span>
            <span style="flex:1;text-align:right;">${parseFloat(item.unit_price || 0).toFixed(2)}</span>
            <span style="flex:1;text-align:right;font-weight:bold;">${lineTotal.toFixed(2)}</span>
        </div>`;
        (item.addons || []).forEach(a => {
            const at = parseFloat(a.price || 0) * parseInt(a.quantity || 1);
            itemsHTML += `<div style="display:flex;font-size:10px;padding:1px 0 1px 10px;opacity:0.8;">
                <span style="flex:1;">+ ${escapeHtml(a.addon_name)}${a.quantity > 1 ? ` ×${a.quantity}` : ''}</span>
                <span style="text-align:right;">${at.toFixed(2)}</span>
            </div>`;
        });
        if (item.item_notes && item.item_notes.trim()) {
            itemsHTML += `<div style="font-size:9px;font-style:italic;padding:1px 0 3px 10px;border-left:1px solid #999;margin-bottom:2px;opacity:0.8;">Note: ${escapeHtml(item.item_notes)}</div>`;
        }
    });

    const refundsSection = _buildReceiptRefundsSection(grossTotal, refunds || window._lastOrderRefunds);

    const html = `
        <div style="text-align:center;">
            <div style="font-size:13px;font-weight:bold;">DINE WITH DIN CAFETERIA</div>
            <div style="font-size:10px;">Karuhatan, Valenzuela City</div>
            <div style="font-size:10px;">(123) 456-7890</div>
            <div style="font-size:11px;font-weight:bold;margin-top:4px;">SALES RECEIPT</div>
            <div style="font-size:10px;">[ PAID ]</div>
        </div>
        <hr style="border:none;border-top:1px dashed #000;margin:5px 0;">
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Receipt No.:</span><span style="font-weight:bold;">${escapeHtml(orderNumber)}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Date:</span><span>${dateStr}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Time:</span><span>${timeStr}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Cashier:</span><span>${escapeHtml(cashier)}</span></div>
        ${customer !== 'Walk-in' ? `<div style="display:flex;justify-content:space-between;font-size:10px;"><span>Customer:</span><span>${escapeHtml(customer)}</span></div>` : ''}
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Order Type:</span><span>${orderType}</span></div>
        ${tableName ? `<div style="display:flex;justify-content:space-between;font-size:10px;"><span>Table:</span><span>${escapeHtml(tableName)}</span></div>` : ''}
        <hr style="border:none;border-top:1px dashed #000;margin:5px 0;">
        <div style="display:flex;font-size:10px;font-weight:bold;border-bottom:1px solid #000;padding-bottom:3px;margin-bottom:4px;text-transform:uppercase;">
            <span style="flex:2.5;">Item</span>
            <span style="flex:0.5;text-align:center;">Qty</span>
            <span style="flex:1;text-align:right;">Price</span>
            <span style="flex:1;text-align:right;">Total</span>
        </div>
        ${itemsHTML}
        <hr style="border:none;border-top:3px double #000;margin:5px 0;">
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Subtotal:</span><span>PHP ${grossTotal.toFixed(2)}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:bold;"><span>TOTAL:</span><span>PHP ${grossTotal.toFixed(2)}</span></div>
        <hr style="border:none;border-top:1px dashed #000;margin:5px 0;">
        <div style="border:1px solid #000;padding:5px 6px;font-size:11px;">
            <div style="display:flex;justify-content:space-between;"><span>Method:</span><span>${payMethod}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>Cash tendered:</span><span>PHP ${amountPaid.toFixed(2)}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>Change:</span><span>PHP ${changeAmt.toFixed(2)}</span></div>
        </div>
        ${refundsSection}
        <hr style="border:none;border-top:1px dashed #000;margin:5px 0;">
        <div style="text-align:center;font-size:10px;line-height:1.7;">
            <div style="font-size:12px;font-weight:bold;text-transform:uppercase;">Thank you for dining with us!</div>
            <div>Please come again.</div>
            <div style="border-top:1px dashed #000;margin-top:5px;padding-top:4px;font-size:9px;">
                Transaction: ${escapeHtml(orderNumber)}<br>
                Printed: ${dateStr} ${timeStr}
            </div>
        </div>`;

    _showReceiptModal(orderNumber, html);
}

// ============================================
// PRINT VOID RECEIPT (voided orders)
// ============================================
function printVoidReceipt(order, items, voidAudit, refunds) {
    const orderNumber = order.order_number || '—';
    const customer    = order.customer_name || 'Walk-in';
    const cashier     = order.cashier_name  || '—';
    const tableName   = order.table_name    || null;
    const orderType   = order.order_type === 'dine_in' ? 'Dine In' : 'Take Out';

    const now     = order.created_at ? new Date(order.created_at) : new Date();
    const dateStr = now.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    const timeStr = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true });

    let sellingTotal = 0;
    let itemsHTML    = '';
    items.forEach(item => {
        if (item.item_status !== 'voided') return;
        const addonsTotal = (item.addons || []).reduce((s, a) => s + parseFloat(a.price || 0) * parseInt(a.quantity || 1), 0);
        const lineTotal   = (parseFloat(item.unit_price || 0) + addonsTotal) * parseInt(item.quantity || 1);
        sellingTotal     += lineTotal;
        const label       = item.variant_name_snapshot
            ? `${item.item_name_snapshot || item.item_name} · ${item.variant_name_snapshot}`
            : (item.item_name_snapshot || item.item_name || '');
        itemsHTML += `<div style="display:flex;margin-bottom:3px;text-decoration:line-through;opacity:0.7;">
            <span style="flex:2.5;word-break:break-word;padding-right:4px;">${escapeHtml(label)}</span>
            <span style="flex:0.5;text-align:center;">${item.quantity || 1}</span>
            <span style="flex:1;text-align:right;">${parseFloat(item.unit_price || 0).toFixed(2)}</span>
            <span style="flex:1;text-align:right;">${lineTotal.toFixed(2)}</span>
        </div>`;
    });

    const wasteCost     = parseFloat(voidAudit?.total_waste_cost || 0);
    const totalRefunded = parseFloat(refunds?.total_refunded || 0);
    const netLoss       = Math.max(0, wasteCost - totalRefunded);
    const refundsSection = _buildReceiptRefundsSection(sellingTotal, refunds || window._lastOrderRefunds);

    const html = `
        <div style="text-align:center;">
            <div style="font-size:13px;font-weight:bold;">DINE WITH DIN CAFETERIA</div>
            <div style="font-size:10px;">Karuhatan, Valenzuela City</div>
            <div style="font-size:10px;">(123) 456-7890</div>
            <div style="font-size:11px;font-weight:bold;margin-top:4px;">VOID ORDER RECORD</div>
            <div style="font-size:10px;">[ VOIDED ]</div>
        </div>
        <hr style="border:none;border-top:1px dashed #000;margin:5px 0;">
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Order No.:</span><span style="font-weight:bold;">${escapeHtml(orderNumber)}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Date:</span><span>${dateStr}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Time:</span><span>${timeStr}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Cashier:</span><span>${escapeHtml(cashier)}</span></div>
        ${customer !== 'Walk-in' ? `<div style="display:flex;justify-content:space-between;font-size:10px;"><span>Customer:</span><span>${escapeHtml(customer)}</span></div>` : ''}
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Order Type:</span><span>${orderType}</span></div>
        ${tableName ? `<div style="display:flex;justify-content:space-between;font-size:10px;"><span>Table:</span><span>${escapeHtml(tableName)}</span></div>` : ''}
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Void Reason:</span><span style="font-weight:600;max-width:55%;text-align:right;">${escapeHtml(voidAudit?.reason || '—')}</span></div>
        <hr style="border:none;border-top:1px dashed #000;margin:5px 0;">
        <div style="display:flex;font-size:10px;font-weight:bold;border-bottom:1px solid #000;padding-bottom:3px;margin-bottom:4px;text-transform:uppercase;">
            <span style="flex:2.5;">Item</span>
            <span style="flex:0.5;text-align:center;">Qty</span>
            <span style="flex:1;text-align:right;">Price</span>
            <span style="flex:1;text-align:right;">Total</span>
        </div>
        ${itemsHTML}
        <hr style="border:none;border-top:3px double #000;margin:5px 0;">
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Selling Value:</span><span>PHP ${sellingTotal.toFixed(2)}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:10px;"><span>Waste Cost (ingredients):</span><span style="color:#d64045;">PHP ${wasteCost.toFixed(2)}</span></div>
        ${refundsSection}
        <hr style="border:none;border-top:1px dashed #000;margin:5px 0;">
        <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:bold;">
            <span>Net Loss:</span>
            <span style="color:#d64045;">PHP ${netLoss.toFixed(2)}</span>
        </div>
        <hr style="border:none;border-top:1px dashed #000;margin:5px 0;">
        <div style="text-align:center;font-size:10px;line-height:1.7;">
            <div style="font-size:12px;font-weight:bold;text-transform:uppercase;">— Internal Document —</div>
            <div>Void processed by: ${escapeHtml(cashier)}</div>
            <div style="border-top:1px dashed #000;margin-top:5px;padding-top:4px;font-size:9px;">
                Transaction: ${escapeHtml(orderNumber)}<br>
                Printed: ${dateStr} ${timeStr}
            </div>
        </div>`;

    _showReceiptModal(orderNumber, html);
}

function _showReceiptModal(orderNumber, html) {
    const printArea = document.getElementById('finalReceiptPrintArea');
    const numEl     = document.getElementById('finalReceiptOrderNumber');
    if (printArea) printArea.innerHTML = html;
    if (numEl)     numEl.textContent   = '#' + orderNumber;
    orderDetailsModal?.hide();

    setTimeout(() => {
        finalReceiptModal?.show();
        // Auto-print using hidden iframe — same as POS
        setTimeout(() => _printReceiptHTML(html), 400);
    }, 250);
}
function _printReceiptHTML(html) {
    const old = document.getElementById('receiptPrintFrame');
    if (old) old.remove();

    const iframe = document.createElement('iframe');
    iframe.id = 'receiptPrintFrame';
    iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:58mm;height:1px;border:none;visibility:hidden;';
    document.body.appendChild(iframe);

    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open();
    doc.write(`<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  @page { size: 58mm auto; margin: 3mm 2mm; }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'Courier New', Courier, monospace;
    font-size: 10px;
    line-height: 1.55;
    color: #000;
    width: 54mm;
    background: white;
  }
</style>
</head>
<body>${html}</body>
</html>`);
    doc.close();

    setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        iframe.contentWindow.onafterprint = () => iframe.remove();
        setTimeout(() => { if (iframe.parentNode) iframe.remove(); }, 30000);
    }, 300);
}
// ============================================
// CANCEL ORDER MODAL
// ============================================
async function showCancelOrderModal(orderId, orderNumber) {
    if (!cancelOrderModal) return;
    document.getElementById('cancelOrderNumber').textContent = orderNumber||'';
    document.getElementById('cancelOrderId').value           = orderId||'';
    const sel=document.getElementById('cancelOrderReasonSelect');
    if (sel) { sel.innerHTML='<option value="">— Loading reasons… —</option>'; await loadReasons('cancel_order','cancelOrderReasonSelect'); }
    window._cancelOrderReason?.reset(); cancelOrderModal.show();
}

// ============================================
// EVENT LISTENERS
// ============================================
function setupEventListeners() {
    setupSidebarToggle();

    document.querySelectorAll('.cashier-view-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.cashier-view-btn').forEach(b=>b.classList.remove('active'));
            this.classList.add('active'); currentCashierView=this.getAttribute('data-view')||'all'; loadPage(1);
        });
    });

    document.getElementById('processedByFilter')?.addEventListener('change', function () { currentProcessedBy=parseInt(this.value)||0; loadPage(1); });

    const dateFrom=document.getElementById('dateFrom'), dateTo=document.getElementById('dateTo');
    const btnAll=document.getElementById('btnAll'), btnToday=document.getElementById('btnToday');
    const btnYesterday=document.getElementById('btnYesterday'), btnThisWeek=document.getElementById('btnThisWeek');
    const btnCustom=document.getElementById('btnCustom'), dateCustomRange=document.getElementById('dateCustomRange');

    function todayStr(off=0) { const d=new Date(); d.setDate(d.getDate()+off); return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }
    function toLocalStr(d)   { return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }

    const today=todayStr();
    if (dateFrom) dateFrom.max=today; if (dateTo) dateTo.max=today;

    const allBtns=[btnAll,btnToday,btnYesterday,btnThisWeek,btnCustom];
    function setActive(name) {
        allBtns.forEach(b=>{ if(b) b.classList.remove('active'); });
        const map={all:btnAll,today:btnToday,yesterday:btnYesterday,week:btnThisWeek,custom:btnCustom};
        if (map[name]) map[name].classList.add('active');
        if (dateCustomRange) dateCustomRange.style.display=name==='custom'?'flex':'none';
    }

    function applyDate() {
        const f=dateFrom?.value||'', t=dateTo?.value||'';
        if (f&&t&&f>t) { if(document.activeElement===dateFrom){if(dateTo)dateTo.value=f;}else{if(dateFrom)dateFrom.value=t;} }
        currentDateFrom=dateFrom?.value||''; currentDateTo=dateTo?.value||'';
        if(dateFrom&&currentDateTo) dateFrom.max=currentDateTo; if(dateTo&&currentDateFrom) dateTo.min=currentDateFrom;
        if(dateFrom&&!currentDateTo) dateFrom.max=today; if(dateTo&&!currentDateFrom) dateTo.min='';
        loadPage(1);
    }

    dateFrom?.addEventListener('change',applyDate); dateTo?.addEventListener('change',applyDate);
    btnAll?.addEventListener('click',()=>{ setActive('all'); currentDateFrom=''; currentDateTo=''; if(dateFrom){dateFrom.value='';dateFrom.min='';dateFrom.max=today;} if(dateTo){dateTo.value='';dateTo.min='';dateTo.max=today;} loadPage(1); });
    btnToday?.addEventListener('click',()=>{ setActive('today'); currentDateFrom=today; currentDateTo=today; if(dateFrom){dateFrom.value=today;dateFrom.min='';dateFrom.max=today;} if(dateTo){dateTo.value=today;dateTo.min=today;dateTo.max=today;} loadPage(1); });
    btnYesterday?.addEventListener('click',()=>{ setActive('yesterday'); const y=todayStr(-1); currentDateFrom=y; currentDateTo=y; if(dateFrom){dateFrom.value=y;dateFrom.min='';dateFrom.max=today;} if(dateTo){dateTo.value=y;dateTo.min=y;dateTo.max=today;} loadPage(1); });
    btnThisWeek?.addEventListener('click',()=>{
        setActive('week'); const now=new Date(), day=now.getDay(), mon=new Date(now); mon.setDate(now.getDate()-((day+6)%7));
        const eow=new Date(mon); eow.setDate(mon.getDate()+6); const sun=eow>now?now:eow;
        const ms=toLocalStr(mon), ss=toLocalStr(sun); currentDateFrom=ms; currentDateTo=ss;
        if(dateFrom){dateFrom.value=ms;dateFrom.min='';dateFrom.max=ss;} if(dateTo){dateTo.value=ss;dateTo.min=ms;dateTo.max=today;} loadPage(1);
    });
    btnCustom?.addEventListener('click',()=>{ setActive('custom'); currentDateFrom=''; currentDateTo=''; if(dateFrom){dateFrom.value='';dateFrom.min='';dateFrom.max=today;} if(dateTo){dateTo.value='';dateTo.min='';dateTo.max=today;} loadPage(1); });

    setActive('today'); currentDateFrom=today; currentDateTo=today;

    const searchInput=document.getElementById('searchInput');
    if (searchInput) { let deb; searchInput.addEventListener('input',function(){ clearTimeout(deb); deb=setTimeout(()=>{ currentSearch=this.value.toLowerCase().trim(); loadPage(1); },300); }); }

    document.querySelectorAll('.filter-tab').forEach(tab=>{
        tab.addEventListener('click',function(){ document.querySelectorAll('.filter-tab').forEach(t=>t.classList.remove('active')); this.classList.add('active'); currentFilter=this.getAttribute('data-status')||'all'; syncProcessedByVisibility(); loadPage(1); });
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.items-manage-btn')) { const btn=e.target.closest('.items-manage-btn'), row=btn.closest('tr'); if(!row) return; openKitchenItemsModal(row.getAttribute('data-order-id'),row.getAttribute('data-order-number'),row.getAttribute('data-status')); }
        if (e.target.closest('.view-order-btn'))   { const btn=e.target.closest('.view-order-btn'); viewOrderDetails(btn.dataset.orderId,btn.dataset.orderNumber); }
        if (e.target.closest('.mark-served-btn'))  { const btn=e.target.closest('.mark-served-btn'); if(!btn.disabled) markAsServed(btn.dataset.orderId,btn.dataset.orderNumber); }
        if (e.target.closest('.cancel-order-btn')) { const btn=e.target.closest('.cancel-order-btn'); showCancelOrderModal(btn.dataset.orderId,btn.dataset.orderNumber); }
        if (e.target.closest('.void-order-btn'))   { const btn=e.target.closest('.void-order-btn'); if(!btn.disabled) voidOrder(btn.dataset.orderId,btn.dataset.orderNumber); }
        if (e.target.closest('.item-status-btn'))  {
            const btn=e.target.closest('.item-status-btn'); if(btn.disabled) return;
            if (btn.classList.contains('item-action-cancel-item')||btn.classList.contains('item-action-void-item')) { voidRefundOrderItem(btn.getAttribute('data-order-item-id'),btn.getAttribute('data-action'),btn.getAttribute('data-item-name'),btn); return; }
            updateItemStatus(btn.getAttribute('data-order-item-id'),btn.getAttribute('data-next-status'),btn);
        }
    });

    document.addEventListener('visibilitychange',()=>{ document.hidden ? stopAutoRefresh() : startAutoRefresh(); });
    setupModalBackdropCleanup();
}

// ============================================
// UTILITIES
// ============================================
window.capitalize    = str => str ? str.charAt(0).toUpperCase()+str.slice(1).toLowerCase() : '';
window.refreshOrders    = refreshOrders;
window.startAutoRefresh = startAutoRefresh;
window.stopAutoRefresh  = stopAutoRefresh;
window.toggleVoidRefund = toggleVoidRefund;