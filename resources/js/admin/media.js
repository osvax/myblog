// ============================================================
// Admin media manager
// ============================================================

class MediaManager {
  constructor(options = {}) {
    this.options = {
      container: options.container || '#media-manager',
      apiUrl: options.apiUrl || '/api/admin/media',
      uploadUrl: options.uploadUrl || '/api/admin/media/upload',
      ...options,
    };
    
    this.container = document.querySelector(this.options.container);
    if (!this.container) return;
    
    this.currentFolder = '';
    this.viewMode = 'grid'; // grid | list
    this.selectedItems = new Set();
    
    this.init();
  }
  
  init() {
    this.render();
    this.bindEvents();
    this.loadFolder('');
  }
  
  render() {
    this.container.innerHTML = `
      <div class="media-manager">
        <!-- Toolbar -->
        <div class="media-toolbar d-flex flex-wrap gap-2 mb-3 p-3 bg-white rounded border">
          <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-orange" data-action="upload">
              <i class="bi bi-upload me-1"></i> Загрузить
            </button>
            <button class="btn btn-outline-secondary" data-action="new-folder">
              <i class="bi bi-folder-plus me-1"></i> Папка
            </button>
            <div class="vr mx-2"></div>
            <button class="btn btn-outline-secondary" data-action="view-grid" title="Сетка">
              <i class="bi bi-grid-3x3-gap"></i>
            </button>
            <button class="btn btn-outline-secondary" data-action="view-list" title="Список">
              <i class="bi bi-list"></i>
            </button>
          </div>
          
          <div class="ms-auto d-flex gap-2 flex-wrap align-items-center">
            <input type="search" class="form-control form-control-sm" style="width: 250px;" 
                   placeholder="Поиск..." data-action="search">
            <select class="form-select form-select-sm" style="width: auto;" data-action="sort">
              <option value="name">По имени</option>
              <option value="date">По дате</option>
              <option value="size">По размеру</option>
              <option value="type">По типу</option>
            </select>
          </div>
        </div>
        
        <!-- Breadcrumb -->
        <nav class="media-breadcrumb mb-2" aria-label="Путь">
          <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item" data-folder=""><i class="bi bi-house"></i> Корень</li>
          </ol>
        </nav>
        
        <!-- Grid/List view -->
        <div class="media-grid row g-3" role="grid"></div>
        
        <!-- Empty state -->
        <div class="media-empty text-center py-5 text-muted d-none">
          <i class="bi bi-folder2-open fs-1"></i>
          <p class="mt-2">Папка пуста</p>
          <button class="btn btn-orange btn-sm" data-action="upload">Загрузить файлы</button>
        </div>
        
        <!-- Upload modal -->
        <div class="modal fade" id="mediaUploadModal" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-orange text-white">
                <h5 class="modal-title">Загрузка файлов</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="drop-zone border-2 border-dashed border-orange rounded p-5 text-center mb-3"
                     style="cursor: pointer;">
                  <i class="bi bi-cloud-upload fs-1 text-orange"></i>
                  <p class="mt-2">Перетащите файлы сюда или нажмите для выбора</p>
                  <small class="text-muted">Макс. 10MB, JPG/PNG/WebP/GIF/PDF/DOC/XLS/ZIP</small>
                </div>
                <input type="file" id="mediaFileInput" multiple hidden accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                <div class="upload-progress d-none">
                  <div class="progress mb-2">
                    <div class="progress-bar bg-orange" role="progressbar" style="width: 0%"></div>
                  </div>
                  <div class="upload-status small text-muted"></div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Context menu -->
        <div class="media-context-menu dropdown-menu position-fixed d-none" style="z-index: 1050;">
          <h6 class="dropdown-header">Действия</h6>
          <a class="dropdown-item" data-action="preview"><i class="bi bi-eye me-2"></i> Просмотр</a>
          <a class="dropdown-item" data-action="download"><i class="bi bi-download me-2"></i> Скачать</a>
          <a class="dropdown-item" data-action="rename"><i class="bi bi-pencil me-2"></i> Переименовать</a>
          <a class="dropdown-item" data-action="copy-url"><i class="bi bi-link-45deg me-2"></i> Копировать ссылку</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-danger" data-action="delete"><i class="bi bi-trash me-2"></i> Удалить</a>
        </div>
      </div>
    `;
    
    this.grid = this.container.querySelector('.media-grid');
    this.emptyState = this.container.querySelector('.media-empty');
    this.breadcrumb = this.container.querySelector('.media-breadcrumb');
    this.contextMenu = this.container.querySelector('.media-context-menu');
  }
  
  bindEvents() {
    // Toolbar actions
    this.container.querySelector('[data-action="upload"]')?.addEventListener('click', () => this.openUploadModal());
    this.container.querySelector('[data-action="new-folder"]')?.addEventListener('click', () => this.createFolder());
    this.container.querySelector('[data-action="view-grid"]')?.addEventListener('click', () => this.setViewMode('grid'));
    this.container.querySelector('[data-action="view-list"]')?.addEventListener('click', () => this.setViewMode('list'));
    this.container.querySelector('[data-action="search"]')?.addEventListener('input', App.debounce(e => this.search(e.target.value), 300));
    this.container.querySelector('[data-action="sort"]')?.addEventListener('change', e => this.sort(e.target.value));
    
    // Upload modal
    const dropZone = this.container.querySelector('.drop-zone');
    const fileInput = this.container.querySelector('#mediaFileInput');
    
    dropZone?.addEventListener('click', () => fileInput?.click());
    dropZone?.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-primary'); });
    dropZone?.addEventListener('dragleave', () => dropZone.classList.remove('border-primary'));
    dropZone?.addEventListener('drop', e => {
      e.preventDefault();
      dropZone.classList.remove('border-primary');
      [...e.dataTransfer.files].forEach(file => this.uploadFile(file));
    });
    
    fileInput?.addEventListener('change', e => {
      [...e.target.files].forEach(file => this.uploadFile(file));
      e.target.value = '';
    });
    
    // Context menu
    document.addEventListener('click', () => this.hideContextMenu());
    document.addEventListener('contextmenu', e => {
      const item = e.target.closest('[data-media-item]');
      if (item) {
        e.preventDefault();
        this.showContextMenu(e.clientX, e.clientY, item);
      }
    });
    
    this.contextMenu?.querySelectorAll('[data-action]').forEach(btn => {
      btn.addEventListener('click', e => {
        e.preventDefault();
        const action = btn.dataset.action;
        const item = this.contextMenu.dataset.targetItem;
        this.handleContextAction(action, item);
        this.hideContextMenu();
      });
    });
    
    // Keyboard
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') this.hideContextMenu();
      if (e.key === 'Delete' && this.selectedItems.size) this.deleteSelected();
    });
  }
  
  async loadFolder(folder) {
    this.currentFolder = folder;
    this.updateBreadcrumb();
    
    try {
      const response = await fetch(`${this.options.apiUrl}?folder=${encodeURIComponent(folder)}`, {
        headers: { 'X-CSRF-Token': Admin.csrfToken },
        credentials: 'same-origin',
      });
      
      const data = await response.json();
      this.renderItems(data.items || []);
    } catch (error) {
      console.error('Media load failed:', error);
      Admin.toast('Ошибка загрузки медиа', 'error');
    }
  }
  
  renderItems(items) {
    this.grid.innerHTML = '';
    this.emptyState.classList.toggle('d-none', items.length > 0);
    
    items.forEach(item => {
      const col = document.createElement('div');
      col.className = this.viewMode === 'grid' ? 'col-6 col-md-4 col-lg-3 col-xl-2' : 'col-12';
      col.dataset.mediaItem = item.path;
      col.innerHTML = this.renderItem(item);
      this.grid.appendChild(col);
    });
    
    // Bind item events
    this.grid.querySelectorAll('[data-media-item]').forEach(item => {
      item.addEventListener('click', e => {
        if (e.ctrlKey || e.metaKey) {
          this.toggleSelect(item);
        } else if (!e.target.closest('button, a')) {
          this.clearSelection();
          this.toggleSelect(item);
        }
      });
      
      item.addEventListener('dblclick', () => {
        const itemData = JSON.parse(item.dataset.mediaData);
        if (itemData.is_folder) this.loadFolder(itemData.path);
        else this.previewItem(itemData);
      });
    });
  }
  
  renderItem(item) {
    const isImage = item.mime_type?.startsWith('image/');
    const isFolder = item.is_folder;
    const size = item.size ? this.formatSize(item.size) : '';
    const date = item.modified ? new Date(item.modified).toLocaleDateString('ru-RU') : '';
    
    const icon = isFolder ? 'bi-folder-fill text-warning' :
                 isImage ? 'bi-image-fill text-primary' :
                 item.mime_type === 'application/pdf' ? 'bi-file-earmark-pdf-fill text-danger' :
                 item.mime_type?.includes('word') ? 'bi-file-earmark-word-fill text-primary' :
                 item.mime_type?.includes('excel') ? 'bi-file-earmark-excel-fill text-success' :
                 item.mime_type?.includes('zip') ? 'bi-file-earmark-zip-fill text-orange' :
                 'bi-file-earmark-fill text-secondary';
    
    const preview = isImage && item.thumb_url 
      ? `<img src="${item.thumb_url}" alt="" class="img-fluid rounded" loading="lazy">`
      : `<i class="bi ${icon} fs-1"></i>`;
    
    return `
      <div class="media-item card h-100 border-0 shadow-sm ${this.viewMode === 'list' ? 'd-flex align-items-center' : ''}" 
           data-media-data='${JSON.stringify(item).replace(/'/g, '&apos;')}'
           tabindex="0">
        <div class="${this.viewMode === 'grid' ? 'position-relative' : 'd-flex align-items-center'}">
          ${this.viewMode === 'grid' ? `
            <div class="media-preview aspect-ratio-1x1 bg-light d-flex align-items-center justify-content-center overflow-hidden">
              ${preview}
            </div>
            <div class="card-body p-2">
              <input type="checkbox" class="media-select form-check-input position-absolute top-0 end-0 m-2" value="${item.path}">
              <h6 class="card-title text-truncate mb-1" title="${this.escapeHtml(item.name)}">${this.escapeHtml(item.name)}</h6>
              <small class="text-muted d-block">${size}${size && date ? ' • ' : ''}${date}</small>
            </div>
          ` : `
            <div class="media-preview me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
              ${preview}
            </div>
            <div class="flex-grow-1 min-w-0">
              <input type="checkbox" class="media-select form-check-input me-2" value="${item.path}">
              <h6 class="mb-0 text-truncate" title="${this.escapeHtml(item.name)}">${this.escapeHtml(item.name)}</h6>
              <small class="text-muted">${size} • ${date}</small>
            </div>
            <div class="text-end ms-3">
              <button class="btn btn-sm btn-outline-secondary" data-action="preview" title="Просмотр"><i class="bi bi-eye"></i></button>
              <button class="btn btn-sm btn-outline-secondary" data-action="download" title="Скачать"><i class="bi bi-download"></i></button>
            </div>
          `}
        </div>
      </div>
    `;
  }
  
  updateBreadcrumb() {
    const parts = this.currentFolder.split('/').filter(Boolean);
    let html = '<ol class="breadcrumb mb-0 bg-transparent p-0">';
    html += '<li class="breadcrumb-item" data-folder=""><i class="bi bi-house"></i> Корень</li>';
    
    let currentPath = '';
    parts.forEach((part, i) => {
      currentPath += (currentPath ? '/' : '') + part;
      const isLast = i === parts.length - 1;
      html += `<li class="breadcrumb-item ${isLast ? 'active' : ''}" ${!isLast ? `data-folder="${currentPath}"` : 'aria-current="page"'}>${this.escapeHtml(part)}</li>`;
    });
    
    html += '</ol>';
    this.breadcrumb.innerHTML = html;
    
    this.breadcrumb.querySelectorAll('[data-folder]').forEach(li => {
      li.style.cursor = 'pointer';
      li.addEventListener('click', () => this.loadFolder(li.dataset.folder));
    });
  }
  
  setViewMode(mode) {
    this.viewMode = mode;
    this.container.querySelectorAll('[data-action^="view-"]').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.action === `view-${mode}`);
    });
    this.renderItems([...this.grid.querySelectorAll('[data-media-item]')].map(el => JSON.parse(el.dataset.mediaData)));
  }
  
  toggleSelect(item) {
    const checkbox = item.querySelector('.media-select');
    const path = item.dataset.mediaItem;
    
    if (checkbox.checked) {
      this.selectedItems.delete(path);
      item.classList.remove('selected');
    } else {
      this.selectedItems.add(path);
      item.classList.add('selected');
    }
    
    checkbox.checked = this.selectedItems.has(path);
  }
  
  clearSelection() {
    this.selectedItems.clear();
    this.grid.querySelectorAll('.media-item').forEach(item => {
      item.classList.remove('selected');
      item.querySelector('.media-select').checked = false;
    });
  }
  
  showContextMenu(x, y, item) {
    this.contextMenu.dataset.targetItem = item.dataset.mediaItem;
    this.contextMenu.style.left = `${x}px`;
    this.contextMenu.style.top = `${y}px`;
    this.contextMenu.classList.remove('d-none');
  }
  
  hideContextMenu() {
    this.contextMenu.classList.add('d-none');
    delete this.contextMenu.dataset.targetItem;
  }
  
  handleContextAction(action, path) {
    const itemData = JSON.parse(this.grid.querySelector(`[data-media-item="${path}"]`)?.dataset.mediaData || '{}');
    
    switch (action) {
      case 'preview': this.previewItem(itemData); break;
      case 'download': this.downloadItem(itemData); break;
      case 'rename': this.renameItem(itemData); break;
      case 'copy-url': this.copyUrl(itemData); break;
      case 'delete': this.deleteItem(itemData); break;
    }
  }
  
  async previewItem(item) {
    if (item.is_folder) {
      this.loadFolder(item.path);
      return;
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
      <div class="modal-dialog modal-xl modal-fullscreen-xxl-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">${this.escapeHtml(item.name)}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-0 d-flex align-items-center justify-content-center" style="min-height: 70vh;">
            ${item.mime_type?.startsWith('image/') 
              ? `<img src="${item.url}" alt="" class="img-fluid" style="max-height: 80vh;">`
              : `<div class="text-center p-4"><i class="bi bi-file-earmark fs-1 text-muted"></i><p class="mt-2">Превью недоступно</p><a href="${item.url}" class="btn btn-orange" target="_blank">Открыть</a></div>`
            }
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    new bootstrap.Modal(modal).show();
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
  }
  
  downloadItem(item) {
    const a = document.createElement('a');
    a.href = item.url;
    a.download = item.name;
    a.click();
  }
  
  copyUrl(item) {
    navigator.clipboard.writeText(item.url);
    Admin.toast('Ссылка скопирована', 'success');
  }
  
  async renameItem(item) {
    const newName = prompt('Новое имя:', item.name);
    if (!newName || newName === item.name) return;
    
    try {
      await Admin.api(`${this.options.apiUrl}/${encodeURIComponent(item.path)}/rename`, {
        method: 'PATCH',
        body: JSON.stringify({ name: newName, _token: Admin.csrfToken }),
      });
      Admin.toast('Переименовано', 'success');
      this.loadFolder(this.currentFolder);
    } catch (error) {
      Admin.toast(error.message, 'error');
    }
  }
  
  async deleteItem(item) {
    if (!await Admin.confirm(`Удалить "${item.name}"?`, 'danger')) return;
    
    try {
      await Admin.api(`${this.options.apiUrl}/${encodeURIComponent(item.path)}`, { method: 'DELETE' });
      Admin.toast('Удалено', 'success');
      this.loadFolder(this.currentFolder);
    } catch (error) {
      Admin.toast(error.message, 'error');
    }
  }
  
  async deleteSelected() {
    if (!this.selectedItems.size) return;
    if (!await Admin.confirm(`Удалить ${this.selectedItems.size} элементов?`, 'danger')) return;
    
    try {
      await Admin.api(`${this.options.apiUrl}/bulk-delete`, {
        method: 'DELETE',
        body: JSON.stringify({ paths: [...this.selectedItems], _token: Admin.csrfToken }),
      });
      Admin.toast('Удалено', 'success');
      this.selectedItems.clear();
      this.loadFolder(this.currentFolder);
    } catch (error) {
      Admin.toast(error.message, 'error');
    }
  }
  
  async createFolder() {
    const name = prompt('Имя папки:');
    if (!name) return;
    
    try {
      await Admin.api(this.options.apiUrl, {
        method: 'POST',
        body: JSON.stringify({ 
          action: 'create_folder', 
          folder: this.currentFolder, 
          name, 
          _token: Admin.csrfToken 
        }),
      });
      Admin.toast('Папка создана', 'success');
      this.loadFolder(this.currentFolder);
    } catch (error) {
      Admin.toast(error.message, 'error');
    }
  }
  
  openUploadModal() {
    new bootstrap.Modal(this.container.querySelector('#mediaUploadModal')).show();
  }
  
  async uploadFile(file) {
    const progressBar = this.container.querySelector('.progress-bar');
    const status = this.container.querySelector('.upload-status');
    const progressContainer = this.container.querySelector('.upload-progress');
    
    progressContainer.classList.remove('d-none');
    progressBar.style.width = '0%';
    status.textContent = `Загрузка ${file.name}...`;
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('folder', this.currentFolder);
    formData.append('_token', Admin.csrfToken);
    
    try {
      const response = await fetch(this.options.uploadUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      });
      
      const data = await response.json();
      if (!response.ok) throw new Error(data.message);
      
      progressBar.style.width = '100%';
      status.textContent = 'Готово!';
      Admin.toast('Загружено', 'success');
      this.loadFolder(this.currentFolder);
    } catch (error) {
      status.textContent = `Ошибка: ${error.message}`;
      status.classList.add('text-danger');
      Admin.toast(error.message, 'error');
    } finally {
      setTimeout(() => progressContainer.classList.add('d-none'), 2000);
    }
  }
  
  search(query) {
    // Implement search - could filter client-side or request from server
    this.grid.querySelectorAll('[data-media-item]').forEach(item => {
      const name = item.querySelector('.card-title, h6')?.textContent.toLowerCase() || '';
      item.style.display = name.includes(query.toLowerCase()) ? '' : 'none';
    });
  }
  
  sort(field) {
    const items = [...this.grid.querySelectorAll('[data-media-item]')];
    items.sort((a, b) => {
      const dataA = JSON.parse(a.dataset.mediaData);
      const dataB = JSON.parse(b.dataset.mediaData);
      
      let valA, valB;
      switch (field) {
        case 'name': valA = dataA.name.toLowerCase(); valB = dataB.name.toLowerCase(); break;
        case 'date': valA = new Date(dataA.modified || 0); valB = new Date(dataB.modified || 0); break;
        case 'size': valA = dataA.size || 0; valB = dataB.size || 0; break;
        case 'type': valA = dataA.mime_type || ''; valB = dataB.mime_type || ''; break;
      }
      
      if (valA < valB) return -1;
      if (valA > valB) return 1;
      return 0;
    });
    
    items.forEach(item => this.grid.appendChild(item));
  }
  
  formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1024 / 1024).toFixed(1) + ' MB';
  }
  
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}

// Initialize if container exists
document.addEventListener('DOMContentLoaded', () => {
  if (document.querySelector('#media-manager')) {
    window.mediaManager = new MediaManager();
  }
});

export { MediaManager };