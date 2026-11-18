// ============================================================
// Admin forms: validation, image preview, rich text, etc.
// ============================================================

// Auto-save draft
class AutoSave {
  constructor(formSelector, options = {}) {
    this.form = document.querySelector(formSelector);
    if (!this.form) return;
    
    this.endpoint = options.endpoint || this.form.action + '/draft';
    this.interval = options.interval || 30000; // 30 seconds
    this.lastSaved = null;
    this.isSaving = false;
    
    this.init();
  }
  
  init() {
    // Track changes
    this.form.querySelectorAll('input, textarea, select').forEach(input => {
      input.addEventListener('change', () => this.markChanged());
      input.addEventListener('input', () => this.markChanged());
    });
    
    // Save on interval
    setInterval(() => this.save(), this.interval);
    
    // Save on beforeunload
    window.addEventListener('beforeunload', () => {
      if (this.hasChanges) this.save(true);
    });
    
    // Show saved indicator
    this.createIndicator();
  }
  
  markChanged() {
    this.hasChanges = true;
    this.updateIndicator('unsaved');
  }
  
  createIndicator() {
    this.indicator = document.createElement('div');
    this.indicator.className = 'autosave-indicator position-fixed bottom-0 end-0 m-3';
    this.indicator.style.zIndex = '1000';
    this.indicator.innerHTML = `
      <div class="badge bg-secondary px-3 py-2" style="display: none;">
        <i class="bi bi-circle me-1"></i> <span>Не сохранено</span>
      </div>
    `;
    document.body.appendChild(this.indicator);
  }
  
  updateIndicator(status) {
    const badge = this.indicator.querySelector('.badge');
    const icon = this.indicator.querySelector('i');
    const text = this.indicator.querySelector('span');
    
    badge.style.display = 'block';
    
    switch (status) {
      case 'unsaved':
        badge.className = 'badge bg-warning text-dark px-3 py-2';
        icon.className = 'bi bi-circle me-1';
        text.textContent = 'Не сохранено';
        break;
      case 'saving':
        badge.className = 'badge bg-info px-3 py-2';
        icon.className = 'bi bi-arrow-repeat spin me-1';
        text.textContent = 'Сохранение...';
        break;
      case 'saved':
        badge.className = 'badge bg-success px-3 py-2';
        icon.className = 'bi bi-check-circle me-1';
        text.textContent = `Сохранено ${new Date().toLocaleTimeString('ru-RU', {hour: '2-digit', minute: '2-digit'})}`;
        setTimeout(() => badge.style.display = 'none', 3000);
        break;
      case 'error':
        badge.className = 'badge bg-danger px-3 py-2';
        icon.className = 'bi bi-x-circle me-1';
        text.textContent = 'Ошибка сохранения';
        break;
    }
  }
  
  async save(synchronous = false) {
    if (!this.hasChanges || this.isSaving) return;
    
    this.isSaving = true;
    this.updateIndicator('saving');
    
    const formData = new FormData(this.form);
    formData.append('_token', Admin.csrfToken);
    formData.append('_autosave', '1');
    
    try {
      const response = await fetch(this.endpoint, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      });
      
      if (!response.ok) throw new Error('Auto-save failed');
      
      this.hasChanges = false;
      this.lastSaved = new Date();
      this.updateIndicator('saved');
    } catch (error) {
      console.error('Auto-save error:', error);
      this.updateIndicator('error');
    } finally {
      this.isSaving = false;
    }
  }
}

// Image upload with preview and crop
class ImageUploader {
  constructor(inputSelector, options = {}) {
    this.input = document.querySelector(inputSelector);
    if (!this.input) return;
    
    this.options = {
      maxSize: options.maxSize || 5 * 1024 * 1024, // 5MB
      allowedTypes: options.allowedTypes || ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
      previewSelector: options.previewSelector || '.image-preview',
      crop: options.crop || false,
      aspectRatio: options.aspectRatio || 1,
      ...options,
    };
    
    this.preview = document.querySelector(this.options.previewSelector);
    this.cropper = null;
    
    this.init();
  }
  
  init() {
    this.input.addEventListener('change', e => this.handleFileSelect(e));
    
    // Drag and drop
    if (this.preview) {
      ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(event => {
        this.preview.addEventListener(event, e => e.preventDefault());
      });
      
      this.preview.addEventListener('drop', e => {
        const file = e.dataTransfer.files[0];
        if (file) this.handleFile(file);
      });
    }
  }
  
  handleFileSelect(e) {
    const file = e.target.files[0];
    if (file) this.handleFile(file);
  }
  
  async handleFile(file) {
    // Validate
    if (!this.options.allowedTypes.includes(file.type)) {
      Admin.toast('Неподдерживаемый формат. Разрешены: JPG, PNG, WebP, GIF', 'error');
      return;
    }
    
    if (file.size > this.options.maxSize) {
      Admin.toast(`Файл слишком большой. Максимум: ${this.options.maxSize / 1024 / 1024}MB`, 'error');
      return;
    }
    
    // Preview
    if (this.preview) {
      const url = URL.createObjectURL(file);
      this.preview.innerHTML = `<img src="${url}" alt="Preview" style="max-width: 100%; max-height: 300px;">`;
      
      if (this.options.crop) {
        this.initCropper(this.preview.querySelector('img'));
      }
    }
    
    // Upload if endpoint provided
    if (this.options.uploadUrl) {
      await this.upload(file);
    }
  }
  
  initCropper(img) {
    if (typeof Cropper !== 'undefined') {
      this.cropper = new Cropper(img, {
        aspectRatio: this.options.aspectRatio,
        viewMode: 1,
        responsive: true,
      });
    }
  }
  
  async upload(file) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', Admin.csrfToken);
    
    try {
      const response = await fetch(this.options.uploadUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      });
      
      const data = await response.json();
      if (!response.ok) throw new Error(data.message);
      
      // Update hidden input with file path
      if (this.options.hiddenInput) {
        document.querySelector(this.options.hiddenInput).value = data.path;
      }
      
      Admin.toast('Изображение загружено', 'success');
      return data;
    } catch (error) {
      Admin.toast(error.message, 'error');
    }
  }
  
  getCroppedCanvas() {
    return this.cropper?.getCroppedCanvas();
  }
  
  destroy() {
    this.cropper?.destroy();
  }
}

// Rich text editor (SimpleMDE / Quill / TinyMCE wrapper)
class RichTextEditor {
  constructor(textareaSelector, options = {}) {
    this.textarea = document.querySelector(textareaSelector);
    if (!this.textarea) return;
    
    this.options = {
      toolbar: options.toolbar || [
        'bold', 'italic', 'strikethrough', '|',
        'heading-1', 'heading-2', 'heading-3', '|',
        'quote', 'code', 'unordered-list', 'ordered-list', '|',
        'link', 'image', 'table', '|',
        'preview', 'fullscreen', 'guide'
      ],
      ...options,
    };
    
    this.editor = null;
    this.init();
  }
  
  init() {
    // Try to load SimpleMDE if available
    if (typeof SimpleMDE !== 'undefined') {
      this.editor = new SimpleMDE({
        element: this.textarea,
        toolbar: this.options.toolbar,
        spellChecker: false,
        status: false,
        ...this.options,
      });
    } else if (typeof Quill !== 'undefined') {
      // Quill alternative
      this.initQuill();
    }
  }
  
  initQuill() {
    const container = document.createElement('div');
    container.id = `quill-${this.textarea.id}`;
    this.textarea.parentNode.insertBefore(container, this.textarea.nextSibling);
    this.textarea.style.display = 'none';
    
    this.editor = new Quill(container, {
      modules: { toolbar: this.options.toolbar },
      theme: 'snow',
      placeholder: 'Напишите содержание...',
    });
    
    // Sync with textarea on form submit
    this.textarea.form?.addEventListener('submit', () => {
      this.textarea.value = this.editor.root.innerHTML;
    });
  }
  
  getContent() {
    if (this.editor?.value) return this.editor.value();
    if (this.editor?.getText) return this.editor.root.innerHTML;
    return this.textarea.value;
  }
  
  setContent(html) {
    if (this.editor?.value) this.editor.value(html);
    else if (this.editor?.setContents) this.editor.clipboard.dangerouslyPasteHTML(html);
    else this.textarea.value = html;
  }
}

// Form validation enhancement
class AdminFormValidator {
  constructor(formSelector) {
    this.form = document.querySelector(formSelector);
    if (!this.form) return;
    
    this.init();
  }
  
  init() {
    this.form.addEventListener('submit', e => {
      if (!this.validate()) {
        e.preventDefault();
        e.stopPropagation();
      }
      this.form.classList.add('was-validated');
    });
    
    // Real-time validation
    this.form.querySelectorAll('.form-control, .form-select').forEach(input => {
      input.addEventListener('blur', () => this.validateField(input));
      input.addEventListener('input', () => this.clearError(input));
    });
  }
  
  validate() {
    let isValid = true;
    this.form.querySelectorAll('[required]').forEach(field => {
      if (!this.validateField(field)) isValid = false;
    });
    return isValid;
  }
  
  validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let message = '';
    
    if (field.hasAttribute('required') && !value) {
      isValid = false;
      message = 'Обязательное поле';
    }
    
    if (isValid && field.type === 'email' && value) {
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        isValid = false;
        message = 'Некорректный email';
      }
    }
    
    if (isValid && field.type === 'url' && value) {
      try { new URL(value); } catch { isValid = false; message = 'Некорректный URL'; }
    }
    
    if (isValid && field.hasAttribute('minlength') && value.length < field.minLength) {
      isValid = false;
      message = `Минимум ${field.minLength} символов`;
    }
    
    if (isValid && field.hasAttribute('pattern')) {
      const regex = new RegExp(field.pattern);
      if (!regex.test(value)) {
        isValid = false;
        message = field.title || 'Неверный формат';
      }
    }
    
    this.showError(field, isValid ? null : message);
    return isValid;
  }
  
  showError(field, message) {
    const group = field.closest('.mb-3, .form-group');
    if (!group) return;
    
    let feedback = group.querySelector('.invalid-feedback');
    if (!feedback) {
      feedback = document.createElement('div');
      feedback.className = 'invalid-feedback';
      group.appendChild(feedback);
    }
    
    if (message) {
      feedback.textContent = message;
      field.classList.add('is-invalid');
      field.classList.remove('is-valid');
    } else {
      field.classList.remove('is-invalid');
      field.classList.add('is-valid');
    }
  }
  
  clearError(field) {
    field.classList.remove('is-invalid');
  }
}

// Slug auto-generation from title
document.addEventListener('DOMContentLoaded', () => {
  const titleInput = document.querySelector('#title, input[name="title"]');
  const slugInput = document.querySelector('#slug, input[name="slug"]');
  
  if (titleInput && slugInput && !slugInput.value) {
    let slugGenerated = false;
    
    titleInput.addEventListener('input', () => {
      if (!slugGenerated || !slugInput.dataset.manual) {
        slugInput.value = generateSlug(titleInput.value);
      }
    });
    
    slugInput.addEventListener('input', () => {
      slugInput.dataset.manual = 'true';
    });
  }
  
  function generateSlug(text) {
    return text
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '')
      .substring(0, 100);
  }
});

// Character counter
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('textarea[data-max-length], input[data-max-length]').forEach(input => {
    const maxLength = parseInt(input.dataset.maxLength);
    const counter = document.createElement('div');
    counter.className = 'form-text text-end';
    input.parentElement.appendChild(counter);
    
    const update = () => {
      const remaining = maxLength - input.value.length;
      counter.textContent = `${input.value.length} / ${maxLength}`;
      counter.style.color = remaining < 20 ? 'var(--orange)' : '';
      counter.style.fontWeight = remaining < 0 ? 'bold' : '';
    };
    
    input.addEventListener('input', update);
    update();
  });
});

// Initialize validators
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form.needs-validation, form.admin-form').forEach(form => {
    new AdminFormValidator(form);
  });
});

export { AutoSave, ImageUploader, RichTextEditor, AdminFormValidator };