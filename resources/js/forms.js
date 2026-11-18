// ============================================================
// Forms handling (Order, Feedback, Comment, Auth)
// ============================================================

import { App } from './app.js';

// Base form handler
class FormHandler {
  constructor(formSelector, options = {}) {
    this.form = document.querySelector(formSelector);
    if (!this.form) return;
    
    this.endpoint = options.endpoint || this.form.action;
    this.onSuccess = options.onSuccess || (() => {});
    this.onError = options.onError || (() => {});
    this.resetOnSuccess = options.resetOnSuccess ?? true;
    this.showToast = options.showToast ?? true;
    
    this.bindEvents();
  }
  
  bindEvents() {
    this.form.addEventListener('submit', e => this.handleSubmit(e));
    
    // Real-time validation
    this.form.querySelectorAll('input, select, textarea').forEach(input => {
      input.addEventListener('blur', () => this.validateField(input));
      input.addEventListener('input', () => this.clearFieldError(input));
    });
  }
  
  async handleSubmit(e) {
    e.preventDefault();
    
    if (!this.validateForm()) return;
    
    const submitBtn = this.form.querySelector('[type="submit"]');
    const originalText = submitBtn?.innerHTML;
    submitBtn?.classList.add('loading');
    submitBtn?.setAttribute('disabled', 'true');
    
    const formData = new FormData(this.form);
    formData.append('_token', App.csrfToken);
    
    try {
      const response = await fetch(this.endpoint, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      });
      
      const data = await response.json();
      
      if (!response.ok) throw new Error(data.message || 'Ошибка отправки');
      
      if (this.showToast) {
        App.toast(data.message || 'Успешно отправлено', 'success');
      }
      
      if (this.resetOnSuccess) this.form.reset();
      this.onSuccess(data);
      
    } catch (error) {
      if (this.showToast) {
        App.toast(error.message, 'error');
      }
      this.onError(error);
    } finally {
      submitBtn?.classList.remove('loading');
      submitBtn?.removeAttribute('disabled');
      if (originalText) submitBtn.innerHTML = originalText;
    }
  }
  
  validateForm() {
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
    
    // Required
    if (field.hasAttribute('required') && !value) {
      isValid = false;
      message = 'Это поле обязательно';
    }
    
    // Email
    if (isValid && field.type === 'email' && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        message = 'Некорректный email';
      }
    }
    
    // Phone
    if (isValid && field.type === 'tel' && value) {
      const phoneRegex = /^[\d\s\-\(\)\+]{10,}$/;
      if (!phoneRegex.test(value.replace(/\s/g, ''))) {
        isValid = false;
        message = 'Некорректный телефон';
      }
    }
    
    // Min length
    if (isValid && field.hasAttribute('minlength') && value.length < parseInt(field.minLength)) {
      isValid = false;
      message = `Минимум ${field.minLength} символов`;
    }
    
    this.showFieldError(field, isValid ? null : message);
    return isValid;
  }
  
  showFieldError(field, message) {
    const group = field.closest('.form-group, .mb-3, .consultant-field-group');
    if (!group) return;
    
    let errorEl = group.querySelector('.form-error, .invalid-feedback');
    if (!errorEl) {
      errorEl = document.createElement('div');
      errorEl.className = 'invalid-feedback form-error';
      group.appendChild(errorEl);
    }
    
    if (message) {
      errorEl.textContent = message;
      field.classList.add('is-invalid');
      field.classList.remove('is-valid');
      field.setAttribute('aria-invalid', 'true');
    } else {
      errorEl.textContent = '';
      field.classList.remove('is-invalid');
      field.classList.add('is-valid');
      field.setAttribute('aria-invalid', 'false');
    }
  }
  
  clearFieldError(field) {
    field.classList.remove('is-invalid');
    const group = field.closest('.form-group, .mb-3, .consultant-field-group');
    group?.querySelector('.form-error, .invalid-feedback')?.remove();
  }
}

// Order form
const orderForm = document.querySelector('#order-form, form.order-form');
if (orderForm) {
  new FormHandler('#order-form, form.order-form', {
    endpoint: '/api/forms/order',
    onSuccess: () => {
      // Scroll to success message or redirect
      const successEl = document.querySelector('.order-success');
      successEl?.scrollIntoView({ behavior: 'smooth' });
    },
  });
}

// Feedback form
const feedbackForm = document.querySelector('#feedback-form, form.feedback-form');
if (feedbackForm) {
  new FormHandler('#feedback-form, form.feedback-form', {
    endpoint: '/api/forms/feedback',
    onSuccess: () => {
      const modal = bootstrap.Modal.getInstance(document.querySelector('#feedbackModal'));
      modal?.hide();
    },
  });
}

// Comment form
const commentForm = document.querySelector('#comment-form, form.comment-form');
if (commentForm) {
  new FormHandler('#comment-form, form.comment-form', {
    endpoint: '/api/forms/comment',
    onSuccess: (data) => {
      if (data.comment) {
        // Prepend new comment to list
        const list = document.querySelector('.comments-list');
        if (list) {
          list.insertAdjacentHTML('afterbegin', data.commentHtml);
        }
      }
    },
  });
}

// Auth forms (login, register, forgot, reset)
document.querySelectorAll('form.auth-form').forEach(form => {
  new FormHandler(form, {
    endpoint: form.action,
    onSuccess: (data) => {
      if (data.redirect) {
        window.location.href = data.redirect;
      } else {
        window.location.reload();
      }
    },
  });
});

// File upload preview
document.querySelectorAll('input[type="file"]').forEach(input => {
  input.addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;
    
    const preview = input.parentElement.querySelector('.file-preview');
    if (preview) preview.remove();
    
    if (file.type.startsWith('image/')) {
      const img = document.createElement('img');
      img.className = 'file-preview img-thumbnail mt-2';
      img.style.maxWidth = '200px';
      img.src = URL.createObjectURL(file);
      input.parentElement.appendChild(img);
    }
  });
});

// Character counter for textareas
document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
  const counter = document.createElement('div');
  counter.className = 'form-text text-end';
  textarea.parentElement.appendChild(counter);
  
  const updateCounter = () => {
    const remaining = textarea.maxLength - textarea.value.length;
    counter.textContent = `${textarea.value.length} / ${textarea.maxLength}`;
    counter.style.color = remaining < 20 ? 'var(--orange)' : '';
  };
  
  textarea.addEventListener('input', updateCounter);
  updateCounter();
});

// Export for use in other modules
export { FormHandler };