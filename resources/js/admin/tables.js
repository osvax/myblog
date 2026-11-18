// ============================================================
// Admin tables: sorting, filtering, pagination, bulk actions
// ============================================================

class AdminTable {
  constructor(tableSelector, options = {}) {
    this.table = document.querySelector(tableSelector);
    if (!this.table) return;
    
    this.options = {
      sortable: options.sortable !== false,
      filterable: options.filterable !== false,
      pageSize: options.pageSize || 25,
      ...options,
    };
    
    this.currentPage = 1;
    this.sortColumn = null;
    this.sortDirection = 'asc';
    this.filters = {};
    
    this.init();
  }
  
  init() {
    this.setupSorting();
    this.setupFiltering();
    this.setupBulkActions();
    this.setupRowActions();
    this.setupPagination();
    this.setupKeyboardNavigation();
  }
  
  setupSorting() {
    if (!this.options.sortable) return;
    
    this.table.querySelectorAll('th[data-sort]').forEach(th => {
      th.style.cursor = 'pointer';
      th.addEventListener('click', () => this.handleSort(th));
      
      // Add sort indicator
      const indicator = document.createElement('span');
      indicator.className = 'sort-indicator ms-1';
      indicator.innerHTML = '<i class="bi bi-arrow-down-up"></i>';
      th.appendChild(indicator);
    });
  }
  
  handleSort(th) {
    const column = th.dataset.sort;
    
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    
    // Update UI
    this.table.querySelectorAll('th[data-sort]').forEach(h => {
      h.classList.remove('sort-asc', 'sort-desc');
      h.querySelector('.sort-indicator i').className = 'bi bi-arrow-down-up';
    });
    
    th.classList.add(`sort-${this.sortDirection}`);
    th.querySelector('.sort-indicator i').className = 
      this.sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down';
    
    this.reload();
  }
  
  setupFiltering() {
    if (!this.options.filterable) return;
    
    const filterForm = document.querySelector(`[data-table-filter="${this.table.id}"]`);
    if (!filterForm) return;
    
    filterForm.addEventListener('submit', e => {
      e.preventDefault();
      this.filters = {};
      new FormData(filterForm).forEach((value, key) => {
        if (value) this.filters[key] = value;
      });
      this.currentPage = 1;
      this.reload();
    });
    
    // Reset filters
    const resetBtn = filterForm.querySelector('[type="reset"]');
    resetBtn?.addEventListener('click', () => {
      this.filters = {};
      this.currentPage = 1;
      filterForm.reset();
      this.reload();
    });
  }
  
  setupBulkActions() {
    const selectAll = this.table.querySelector('thead [type="checkbox"]');
    const rowCheckboxes = this.table.querySelectorAll('tbody [type="checkbox"]');
    const bulkActions = document.querySelector(`[data-bulk-actions="${this.table.id}"]`);
    
    if (!selectAll || !bulkActions) return;
    
    selectAll.addEventListener('change', () => {
      rowCheckboxes.forEach(cb => {
        cb.checked = selectAll.checked;
        cb.closest('tr')?.classList.toggle('selected', cb.checked);
      });
      this.updateBulkActions(bulkActions);
    });
    
    rowCheckboxes.forEach(cb => {
      cb.addEventListener('change', () => {
        cb.closest('tr')?.classList.toggle('selected', cb.checked);
        const allChecked = [...rowCheckboxes].every(c => c.checked);
        const someChecked = [...rowCheckboxes].some(c => c.checked);
        selectAll.checked = allChecked;
        selectAll.indeterminate = someChecked && !allChecked;
        this.updateBulkActions(bulkActions);
      });
    });
    
    // Bulk action buttons
    bulkActions.querySelectorAll('[data-bulk-action]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const action = btn.dataset.bulkAction;
        const ids = [...rowCheckboxes].filter(c => c.checked).map(c => c.value);
        
        if (!ids.length) return;
        
        if (btn.dataset.confirm && !confirm(btn.dataset.confirm)) return;
        
        await this.performBulkAction(action, ids);
      });
    });
  }
  
  updateBulkActions(bulkActions) {
    const selectedCount = this.table.querySelectorAll('tbody [type="checkbox"]:checked').length;
    bulkActions.querySelectorAll('[data-bulk-action]').forEach(btn => {
      btn.disabled = selectedCount === 0;
      const badge = btn.querySelector('.selected-count');
      if (badge) badge.textContent = selectedCount;
    });
  }
  
  async performBulkAction(action, ids) {
    const btn = document.querySelector(`[data-bulk-action="${action}"]`);
    Admin.setButtonLoading(btn, true);
    
    try {
      const response = await Admin.api('/api/admin/bulk-action', {
        method: 'POST',
        body: JSON.stringify({ action, ids, _token: Admin.csrfToken }),
      });
      
      Admin.toast(response.message || 'Действие выполнено', 'success');
      this.reload();
    } catch (error) {
      Admin.toast(error.message, 'error');
    } finally {
      Admin.setButtonLoading(btn, false);
    }
  }
  
  setupRowActions() {
    // Delete buttons
    this.table.querySelectorAll('[data-action="delete"]').forEach(btn => {
      btn.addEventListener('click', async e => {
        e.preventDefault();
        const id = btn.dataset.id;
        const confirmMsg = btn.dataset.confirm || 'Удалить эту запись?';
        
        if (!await Admin.confirm(confirmMsg, 'danger')) return;
        
        try {
          await Admin.api(btn.href || `/api/admin/${this.table.dataset.resource}/${id}`, {
            method: 'DELETE',
          });
          Admin.toast('Запись удалена', 'success');
          this.reload();
        } catch (error) {
          Admin.toast(error.message, 'error');
        }
      });
    });
    
    // Toggle status (publish/unpublish)
    this.table.querySelectorAll('[data-action="toggle"]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.dataset.id;
        const field = btn.dataset.field || 'is_active';
        const value = btn.dataset.value === 'true' ? 0 : 1;
        
        try {
          await Admin.api(`/api/admin/${this.table.dataset.resource}/${id}/toggle`, {
            method: 'PATCH',
            body: JSON.stringify({ field, value, _token: Admin.csrfToken }),
          });
          Admin.toast('Статус обновлен', 'success');
          this.reload();
        } catch (error) {
          Admin.toast(error.message, 'error');
        }
      });
    });
  }
  
  setupPagination() {
    const pagination = document.querySelector(`[data-table-pagination="${this.table.id}"]`);
    if (!pagination) return;
    
    pagination.querySelectorAll('.page-link').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        const page = link.dataset.page;
        if (page) {
          this.currentPage = parseInt(page);
          this.reload();
        }
      });
    });
  }
  
  setupKeyboardNavigation() {
    this.table.querySelectorAll('tbody tr').forEach(row => {
      row.addEventListener('keydown', e => {
        if (e.key === 'Enter' && e.target === row) {
          const link = row.querySelector('a[href]');
          link?.click();
        }
      });
      row.setAttribute('tabindex', '0');
    });
  }
  
  async reload() {
    const tbody = this.table.querySelector('tbody');
    const loadingRow = document.createElement('tr');
    loadingRow.innerHTML = `<td colspan="100%" class="text-center py-4"><div class="spinner-border text-orange"></div></td>`;
    tbody.innerHTML = '';
    tbody.appendChild(loadingRow);
    
    try {
      const params = new URLSearchParams({
        page: this.currentPage,
        ...(this.sortColumn && { sort: this.sortColumn, dir: this.sortDirection }),
        ...this.filters,
      });
      
      const response = await fetch(`${this.table.dataset.apiUrl}?${params}`, {
        headers: { 'X-CSRF-Token': Admin.csrfToken },
        credentials: 'same-origin',
      });
      
      const data = await response.json();
      
      if (data.html) {
        tbody.innerHTML = data.html;
      } else if (data.rows) {
        this.renderRows(tbody, data.rows);
      }
      
      this.updatePagination(data.pagination);
      this.init(); // Re-bind events for new rows
      
    } catch (error) {
      console.error('Table reload failed:', error);
      tbody.innerHTML = '<tr><td colspan="100%" class="text-center text-danger py-4">Ошибка загрузки</td></tr>';
    }
  }
  
  renderRows(tbody, rows) {
    // This should be customized per table
    tbody.innerHTML = rows.map(row => `
      <tr data-id="${row.id}">
        ${Object.values(row).map(val => `<td>${this.escapeHtml(val)}</td>`).join('')}
      </tr>
    `).join('');
  }
  
  updatePagination(pagination) {
    const container = document.querySelector(`[data-table-pagination="${this.table.id}"]`);
    if (!container || !pagination) return;
    
    // Update pagination HTML (implementation depends on backend format)
    container.innerHTML = this.renderPagination(pagination);
  }
  
  renderPagination(pagination) {
    // Simplified pagination rendering
    let html = '<nav><ul class="pagination pagination-sm mb-0">';
    
    if (pagination.current_page > 1) {
      html += `<li class="page-item"><a class="page-link" data-page="${pagination.current_page - 1}">&laquo;</a></li>`;
    }
    
    for (let i = 1; i <= pagination.last_page; i++) {
      if (
        i === 1 || i === pagination.last_page ||
        (i >= pagination.current_page - 1 && i <= pagination.current_page + 1)
      ) {
        html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">`;
        html += `<a class="page-link" data-page="${i}">${i}</a></li>`;
      } else if (
        i === pagination.current_page - 2 || i === pagination.current_page + 2
      ) {
        html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
      }
    }
    
    if (pagination.current_page < pagination.last_page) {
      html += `<li class="page-item"><a class="page-link" data-page="${pagination.current_page + 1}">&raquo;</a></li>`;
    }
    
    html += '</ul></nav>';
    return html;
  }
  
  escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
  }
}

// Auto-initialize tables
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.admin-table table[data-api-url]').forEach(table => {
    new AdminTable(table);
  });
});

export { AdminTable };