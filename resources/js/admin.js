// ============================================================
// Admin panel entry point
// ============================================================

import '../scss/admin.scss';
import './admin/navigation';
import './admin/tables';
import './admin/forms';
import './admin/media';
import './admin/dashboard';
import './admin/settings';

// Global admin utilities
window.Admin = {
  csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
  
  // Confirm delete with SweetAlert-style modal
  confirm(message = 'Вы уверены?', type = 'warning') {
    return new Promise(resolve => {
      const modal = this.createConfirmModal(message, type);
      document.body.appendChild(modal);
      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();
      
      modal.addEventListener('hidden.bs.modal', () => modal.remove());
      
      modal.querySelector('.btn-confirm').addEventListener('click', () => {
        bsModal.hide();
        resolve(true);
      });
      
      modal.querySelector('.btn-cancel').addEventListener('click', () => {
        bsModal.hide();
        resolve(false);
      });
    });
  },
  
  createConfirmModal(message, type) {
    const colors = {
      warning: 'orange',
      danger: 'danger',
      info: 'info',
      success: 'success',
    };
    const color = colors[type] || 'orange';
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    modal.innerHTML = `
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-${color} text-white">
            <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Подтверждение</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">${this.escapeHtml(message)}</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">Отмена</button>
            <button type="button" class="btn btn-${color} btn-confirm">Подтвердить</button>
          </div>
        </div>
      </div>
    `;
    return modal;
  },
  
  // Toast notification
  toast(message, type = 'info') {
    const container = this.getToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${this.escapeHtml(message)}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    `;
    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
  },
  
  getToastContainer() {
    let container = document.getElementById('admin-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'admin-toast-container';
      container.className = 'toast-container position-fixed top-0 end-0 p-3';
      container.style.zIndex = '2000';
      document.body.appendChild(container);
    }
    return container;
  },
  
  // API helper
  async api(endpoint, options = {}) {
    const defaults = {
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': this.csrfToken,
      },
      credentials: 'same-origin',
    };
    const response = await fetch(endpoint, { ...defaults, ...options });
    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Request failed' }));
      throw new Error(error.message || `HTTP ${response.status}`);
    }
    return response.json();
  },
  
  // Format date
  formatDate(dateString, options = {}) {
    const defaultOptions = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('ru-RU', { ...defaultOptions, ...options });
  },
  
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  },
  
  // Loading state for buttons
  setButtonLoading(btn, loading = true) {
    if (loading) {
      btn.dataset.originalHtml = btn.innerHTML;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Загрузка...';
      btn.disabled = true;
    } else {
      btn.innerHTML = btn.dataset.originalHtml || btn.innerHTML;
      btn.disabled = false;
    }
  },
};

// Initialize tooltips
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
  });
  
  // Initialize popovers
  document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
    new bootstrap.Popover(el);
  });
  
  // Sidebar toggle on mobile
  const sidebarToggle = document.querySelector('[data-bs-toggle="sidebar"]');
  const sidebar = document.querySelector('.admin-sidebar');
  const overlay = document.querySelector('.admin-sidebar-overlay');
  
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('show');
      overlay?.classList.toggle('show');
    });
    
    overlay?.addEventListener('click', () => {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });
  }
  
  // Auto-dismiss flash messages
  document.querySelectorAll('.alert-dismissible:not(.alert-permanent)').forEach(alert => {
    setTimeout(() => {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      bsAlert.close();
    }, 5000);
  });
});

console.log('🔧 MyBlog admin initialized');