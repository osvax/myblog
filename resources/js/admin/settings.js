// ============================================================
// Admin settings: forms, validation, live preview
// ============================================================

class SettingsManager {
  constructor() {
    this.forms = new Map();
    this.init();
  }
  
  init() {
    this.setupTabs();
    this.setupForms();
    this.setupLivePreview();
    this.setupColorPickers();
    this.setupFileInputs();
    this.setupTestButtons();
  }
  
  setupTabs() {
    // Bootstrap tabs with URL hash
    const triggerTabList = document.querySelectorAll('#settings-tabs button[data-bs-toggle="tab"]');
    triggerTabList.forEach(trigger => {
      trigger.addEventListener('shown.bs.tab', e => {
        const target = e.target.getAttribute('data-bs-target');
        history.replaceState(null, null, target);
      });
    });
    
    // Activate tab from hash
    const hash = window.location.hash;
    if (hash) {
      const tab = document.querySelector(`#settings-tabs button[data-bs-target="${hash}"]`);
      tab && new bootstrap.Tab(tab).show();
    }
  }
  
  setupForms() {
    document.querySelectorAll('.settings-form').forEach(form => {
      const section = form.dataset.section;
      this.forms.set(section, form);
      
      // Auto-save draft
      new AutoSave(form, {
        endpoint: `/api/admin/settings/${section}/draft`,
        interval: 30000,
      });
      
      // Submit handler
      form.addEventListener('submit', e => this.handleSubmit(e, section));
      
      // Reset handler
      form.querySelector('[type="reset"]')?.addEventListener('click', () => this.resetForm(section));
    });
  }
  
  async handleSubmit(e, section) {
    e.preventDefault();
    const form = this.forms.get(section);
    const submitBtn = form.querySelector('[type="submit"]');
    
    Admin.setButtonLoading(submitBtn, true);
    
    const formData = new FormData(form);
    formData.append('_token', Admin.csrfToken);
    
    try {
      const response = await fetch(`/api/admin/settings/${section}`, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      });
      
      const data = await response.json();
      if (!response.ok) throw new Error(data.message);
      
      Admin.toast(data.message || 'Настройки сохранены', 'success');
      
      // Update live preview if applicable
      this.updatePreview(section, data.settings);
      
    } catch (error) {
      Admin.toast(error.message, 'error');
    } finally {
      Admin.setButtonLoading(submitBtn, false);
    }
  }
  
  resetForm(section) {
    const form = this.forms.get(section);
    form.reset();
    
    // Reset color pickers
    form.querySelectorAll('input[type="color"]').forEach(input => {
      input.value = input.dataset.default || input.value;
      input.dispatchEvent(new Event('input'));
    });
    
    // Reset file previews
    form.querySelectorAll('.file-preview').forEach(preview => preview.remove());
  }
  
  setupLivePreview() {
    // Theme color preview
    const colorInputs = document.querySelectorAll('.settings-form input[type="color"]');
    colorInputs.forEach(input => {
      input.addEventListener('input', () => {
        const property = input.dataset.cssProperty;
        const selector = input.dataset.cssSelector || ':root';
        if (property) {
          document.documentElement.style.setProperty(property, input.value);
        }
      });
    });
    
    // Font preview
    const fontSelect = document.querySelector('.settings-form select[name="font_family"]');
    const previewText = document.querySelector('.font-preview-text');
    if (fontSelect && previewText) {
      fontSelect.addEventListener('change', () => {
        previewText.style.fontFamily = `'${fontSelect.value}', sans-serif`;
      });
    }
    
    // Logo preview
    const logoInput = document.querySelector('.settings-form input[name="logo"]');
    const logoPreview = document.querySelector('.logo-preview img');
    if (logoInput && logoPreview) {
      logoInput.addEventListener('change', e => {
        const file = e.target.files[0];
        if (file) {
          logoPreview.src = URL.createObjectURL(file);
        }
      });
    }
    
    // Favicon preview
    const faviconInput = document.querySelector('.settings-form input[name="favicon"]');
    const faviconPreview = document.querySelector('.favicon-preview');
    if (faviconInput && faviconPreview) {
      faviconInput.addEventListener('change', e => {
        const file = e.target.files[0];
        if (file) {
          faviconPreview.innerHTML = `<img src="${URL.createObjectURL(file)}" width="32" height="32">`;
        }
      });
    }
  }
  
  updatePreview(section, settings) {
    // Apply saved settings to preview area
    Object.entries(settings).forEach(([key, value]) => {
      const previewEl = document.querySelector(`[data-preview="${section}.${key}"]`);
      if (previewEl) {
        if (previewEl.tagName === 'INPUT' || previewEl.tagName === 'SELECT') {
          previewEl.value = value;
        } else {
          previewEl.textContent = value;
        }
      }
    });
  }
  
  setupColorPickers() {
    // Enhanced color picker with presets
    document.querySelectorAll('.color-picker-group').forEach(group => {
      const input = group.querySelector('input[type="color"]');
      const presets = group.querySelectorAll('.color-preset');
      
      presets.forEach(preset => {
        preset.addEventListener('click', () => {
          input.value = preset.dataset.color;
          input.dispatchEvent(new Event('input'));
          presets.forEach(p => p.classList.remove('active'));
          preset.classList.add('active');
        });
      });
    });
  }
  
  setupFileInputs() {
    // Drag & drop for file inputs
    document.querySelectorAll('.settings-form .file-upload-area').forEach(area => {
      const input = area.querySelector('input[type="file"]');
      const preview = area.querySelector('.file-preview');
      
      ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(event => {
        area.addEventListener(event, e => e.preventDefault());
      });
      
      area.addEventListener('dragover', () => area.classList.add('border-primary'));
      area.addEventListener('dragleave', () => area.classList.remove('border-primary'));
      
      area.addEventListener('drop', e => {
        area.classList.remove('border-primary');
        const file = e.dataTransfer.files[0];
        if (file) {
          input.files = e.dataTransfer.files;
          this.showFilePreview(file, preview);
        }
      });
      
      input?.addEventListener('change', e => {
        const file = e.target.files[0];
        if (file) this.showFilePreview(file, preview);
      });
      
      area.addEventListener('click', e => {
        if (e.target === area || e.target.closest('.upload-label')) {
          input?.click();
        }
      });
    });
  }
  
  showFilePreview(file, preview) {
    if (!preview) return;
    
    if (file.type.startsWith('image/')) {
      preview.innerHTML = `<img src="${URL.createObjectURL(file)}" class="img-thumbnail" style="max-height: 150px;">`;
    } else {
      preview.innerHTML = `
        <div class="d-flex align-items-center p-3 bg-light rounded">
          <i class="bi bi-file-earmark fs-1 text-muted me-3"></i>
          <div>
            <div class="fw-bold">${file.name}</div>
            <small class="text-muted">${this.formatSize(file.size)}</small>
          </div>
        </div>
      `;
    }
    preview.classList.remove('d-none');
  }
  
  setupTestButtons() {
    // Test email button
    document.querySelector('[data-test="email"]')?.addEventListener('click', async btn => {
      const form = btn.target.closest('form');
      const email = form?.querySelector('input[name="test_email"]')?.value;
      
      if (!email || !email.includes('@')) {
        Admin.toast('Введите корректный email для теста', 'error');
        return;
      }
      
      Admin.setButtonLoading(btn.target, true);
      try {
        const data = await Admin.api('/api/admin/settings/test-email', {
          method: 'POST',
          body: JSON.stringify({ email, _token: Admin.csrfToken }),
        });
        Admin.toast(data.message || 'Тестовое письмо отправлено', 'success');
      } catch (error) {
        Admin.toast(error.message, 'error');
      } finally {
        Admin.setButtonLoading(btn.target, false);
      }
    });
    
    // Test SMTP connection
    document.querySelector('[data-test="smtp"]')?.addEventListener('click', async btn => {
      Admin.setButtonLoading(btn.target, true);
      try {
        const data = await Admin.api('/api/admin/settings/test-smtp', { method: 'POST' });
        Admin.toast(data.message || 'SMTP соединение успешно', 'success');
      } catch (error) {
        Admin.toast(error.message, 'error');
      } finally {
        Admin.setButtonLoading(btn.target, false);
      }
    });
    
    // Test Redis
    document.querySelector('[data-test="redis"]')?.addEventListener('click', async btn => {
      Admin.setButtonLoading(btn.target, true);
      try {
        const data = await Admin.api('/api/admin/settings/test-redis', { method: 'POST' });
        Admin.toast(data.message || 'Redis подключен', 'success');
      } catch (error) {
        Admin.toast(error.message, 'error');
      } finally {
        Admin.setButtonLoading(btn.target, false);
      }
    });
    
    // Test database
    document.querySelector('[data-test="database"]')?.addEventListener('click', async btn => {
      Admin.setButtonLoading(btn.target, true);
      try {
        const data = await Admin.api('/api/admin/settings/test-database', { method: 'POST' });
        Admin.toast(data.message || 'База данных доступна', 'success');
      } catch (error) {
        Admin.toast(error.message, 'error');
      } finally {
        Admin.setButtonLoading(btn.target, false);
      }
    });
  }
  
  formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1024 / 1024).toFixed(1) + ' MB';
  }
}

// Maintenance mode toggle
document.addEventListener('DOMContentLoaded', () => {
  const maintenanceToggle = document.querySelector('#maintenance-mode-toggle');
  const maintenanceOptions = document.querySelector('#maintenance-options');
  
  if (maintenanceToggle && maintenanceOptions) {
    maintenanceToggle.addEventListener('change', () => {
      maintenanceOptions.style.display = maintenanceToggle.checked ? 'block' : 'none';
    });
  }
  
  // IP allowlist validation
  const ipInputs = document.querySelectorAll('input[name="allowed_ips[]"]');
  ipInputs.forEach(input => {
    input.addEventListener('blur', () => {
      const value = input.value.trim();
      if (value && !isValidIP(value) && !isValidCIDR(value)) {
        input.classList.add('is-invalid');
        let feedback = input.parentElement.querySelector('.invalid-feedback');
        if (!feedback) {
          feedback = document.createElement('div');
          feedback.className = 'invalid-feedback';
          input.parentElement.appendChild(feedback);
        }
        feedback.textContent = 'Некорректный IP или CIDR (пример: 192.168.1.1 или 192.168.1.0/24)';
      } else {
        input.classList.remove('is-invalid');
      }
    });
  });
  
  function isValidIP(ip) {
    const ipv4 = /^(\d{1,3}\.){3}\d{1,3}$/;
    const ipv6 = /^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$/;
    if (!ipv4.test(ip) && !ipv6.test(ip)) return false;
    if (ipv4.test(ip)) return ip.split('.').every(octet => octet <= 255);
    return true;
  }
  
  function isValidCIDR(cidr) {
    const [ip, bits] = cidr.split('/');
    return isValidIP(ip) && bits && parseInt(bits) >= 0 && parseInt(bits) <= (ip.includes(':') ? 128 : 32);
  }
});

// Two-factor authentication setup
document.addEventListener('DOMContentLoaded', () => {
  const enable2faBtn = document.querySelector('[data-action="enable-2fa"]');
  const disable2faBtn = document.querySelector('[data-action="disable-2fa"]');
  const qrCodeContainer = document.querySelector('#qr-code-container');
  const verifyForm = document.querySelector('#verify-2fa-form');
  
  if (enable2faBtn) {
    enable2faBtn.addEventListener('click', async () => {
      try {
        const data = await Admin.api('/api/admin/2fa/enable', { method: 'POST' });
        if (data.qr_code) {
          qrCodeContainer.innerHTML = `<img src="${data.qr_code}" alt="QR Code" class="img-fluid">`;
          qrCodeContainer.classList.remove('d-none');
          verifyForm?.classList.remove('d-none');
          enable2faBtn.classList.add('d-none');
        }
      } catch (error) {
        Admin.toast(error.message, 'error');
      }
    });
  }
  
  if (verifyForm) {
    verifyForm.addEventListener('submit', async e => {
      e.preventDefault();
      const code = verifyForm.querySelector('input[name="code"]').value;
      
      try {
        await Admin.api('/api/admin/2fa/verify', {
          method: 'POST',
          body: JSON.stringify({ code, _token: Admin.csrfToken }),
        });
        Admin.toast('2FA включена', 'success');
        verifyForm.classList.add('d-none');
        disable2faBtn?.classList.remove('d-none');
      } catch (error) {
        Admin.toast(error.message, 'error');
      }
    });
  }
  
  if (disable2faBtn) {
    disable2faBtn.addEventListener('click', async () => {
      if (!await Admin.confirm('Отключить двухфакторную аутентификацию?', 'warning')) return;
      
      try {
        await Admin.api('/api/admin/2fa/disable', { method: 'POST' });
        Admin.toast('2FA отключена', 'success');
        disable2faBtn.classList.add('d-none');
        enable2faBtn?.classList.remove('d-none');
        qrCodeContainer?.classList.add('d-none');
      } catch (error) {
        Admin.toast(error.message, 'error');
      }
    });
  }
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  if (document.querySelector('.settings-page')) {
    window.settingsManager = new SettingsManager();
  }
});

export { SettingsManager };