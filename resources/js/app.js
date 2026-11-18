// ============================================================
// Main frontend entry point
// ============================================================

import '../scss/app.scss';
import 'bootstrap-icons/font/bootstrap-icons.css';
import './bootstrap-components';
import './navigation';
// './consultant' включён — порт согласован с API /api/consultant.
// './forms' не в бандле: формы грузятся отдельным Vite-entry 'forms'
// (resources/js/forms.js) в production-ветке лейаута, иначе двойная
// инициализация.
import './consultant';
import './lazy-images';
import './scroll-animations';

// Global utilities
const App = {
  csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
  
  // Show toast notification
  toast(message, type = 'info') {
    const container = this.getToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${this.escapeHtml(message)}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;
    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 4000 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
  },
  
  getToastContainer() {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
      container.style.zIndex = '2000';
      document.body.appendChild(container);
    }
    return container;
  },
  
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
    const defaultOptions = { day: '2-digit', month: '2-digit', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('ru-RU', { ...defaultOptions, ...options });
  },
  
  // Debounce
  debounce(fn, delay) {
    let timeoutId;
    return (...args) => {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => fn.apply(this, args), delay);
    };
  },
};

// Export for modules, also expose globally for inline scripts
export { App };
window.App = App;

// Initialize tooltips and popovers
document.addEventListener('DOMContentLoaded', () => {
  // Bootstrap tooltips
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
  });
  
  // Bootstrap popovers
  document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
    new bootstrap.Popover(el);
  });
});

console.log('🚀 MyBlog frontend initialized');