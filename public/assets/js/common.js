/**
 * ============================================
 * COMMON UTILITY FUNCTIONS
 * ============================================
 */

// Utility functions
window.escapeHtml = function(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
};

window.formatPrice = function(amount) {
    const n = parseFloat(amount || 0);
    return isNaN(n) ? '0.00' : n.toFixed(2);
};

window.formatDate = function(dateString) {
    try { 
        return new Date(dateString).toLocaleString('en-US', { 
            month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit' 
        }); 
    } catch { return ''; }
};

// Auto-dismiss alerts utility
window.autoDismissAlerts = function(timeout = 3000) {
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            try { 
                bootstrap.Alert.getOrCreateInstance(alert).close(); 
            } catch (e) {}
        });
    }, timeout);
};

// Sidebar toggle utility
window.setupSidebarToggle = function() {
    const menuToggle     = document.getElementById('menuToggle');
    const sidebar        = document.getElementById('sidebar');
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
};

// Modal backdrop cleanup utility
window.setupModalBackdropCleanup = function() {
    document.addEventListener('hidden.bs.modal', function() {
        if (document.querySelector('.modal.show')) return;
        document.querySelectorAll('.modal-backdrop').forEach(function(el) { 
            el.remove(); 
        });
        document.body.classList.remove('modal-open');
        document.body.style.overflow     = '';
        document.body.style.paddingRight = '';
    });
};
