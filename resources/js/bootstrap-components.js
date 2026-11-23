// ============================================================
// Bootstrap components initialization
// ============================================================

import 'bootstrap';

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.alert-dismissible:not(.alert-permanent)').forEach(alert => {
    setTimeout(() => {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      bsAlert.close();
    }, 5000);
  });
});

// Navbar scroll effect
const navbar = document.querySelector('.navbar');
if (navbar) {
  let lastScroll = 0;
  window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    if (currentScroll > 100) {
      navbar.classList.add('navbar-scrolled');
    } else {
      navbar.classList.remove('navbar-scrolled');
    }
    lastScroll = currentScroll;
  }, { passive: true });
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    const targetId = this.getAttribute('href');
    if (targetId === '#') return;
    
    const target = document.querySelector(targetId);
    if (target) {
      e.preventDefault();
      const navbarHeight = navbar?.offsetHeight || 0;
      const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
      window.scrollTo({ top: targetPosition, behavior: 'smooth' });
    }
  });
});

// Active nav link highlighting
const currentPath = window.location.pathname;
document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
  const href = link.getAttribute('href');
  if (href && href !== '/' && currentPath.startsWith(href)) {
    link.classList.add('active');
  } else if (href === '/' && currentPath === '/') {
    link.classList.add('active');
  }
});

// Form validation enhancement
document.querySelectorAll('form.needs-validation').forEach(form => {
  form.addEventListener('submit', event => {
    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add('was-validated');
  }, false);
});

// Confirm actions (data-confirm) — Bootstrap-модалка вместо нативного confirm()
let confirmModalEl = null;

function buildConfirmModal() {
  if (confirmModalEl) return confirmModalEl;
  confirmModalEl = document.createElement('div');
  confirmModalEl.className = 'modal fade';
  confirmModalEl.setAttribute('tabindex', '-1');
  confirmModalEl.setAttribute('aria-hidden', 'true');
  confirmModalEl.innerHTML =
    '<div class="modal-dialog modal-dialog-centered">' +
    '<div class="modal-content">' +
    '<div class="modal-header"><h5 class="modal-title">Подтверждение</h5>' +
    '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button></div>' +
    '<div class="modal-body"><p class="mb-0" data-confirm-text></p></div>' +
    '<div class="modal-footer">' +
    '<button type="button" class="btn btn-secondary" data-confirm-no>Отмена</button>' +
    '<button type="button" class="btn btn-danger" data-confirm-yes>Подтвердить</button>' +
    '</div></div></div>';
  document.body.appendChild(confirmModalEl);
  return confirmModalEl;
}

function askConfirmModal(message) {
  return new Promise((resolve) => {
    // Глобальный bootstrap — из CDN-бандла; без него просто не блокируем действие
    if (!window.bootstrap || !window.bootstrap.Modal) {
      resolve(true);
      return;
    }
    const el = buildConfirmModal();
    const answer = { ok: false };
    let settled = false;
    const onHidden = function() {
      el.removeEventListener('hidden.bs.modal', onHidden);
      if (!settled) {
        settled = true;
        resolve(answer.ok);
      }
    };
    el.querySelector('[data-confirm-text]').textContent = message || 'Вы уверены?';
    el.querySelector('[data-confirm-no]').onclick = function() {
      answer.ok = false;
      window.bootstrap.Modal.getInstance(el).hide();
    };
    el.querySelector('[data-confirm-yes]').onclick = function() {
      answer.ok = true;
      window.bootstrap.Modal.getInstance(el).hide();
    };
    el.addEventListener('hidden.bs.modal', onHidden);
    window.bootstrap.Modal.getOrCreateInstance(el).show();
  });
}

// Повторный клик после подтверждения (WeakSet — защита от бесконечного цикла)
const confirmedElements = new WeakSet();

document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (confirmedElements.has(el)) return; // повторное срабатывание — пропускаем
    e.preventDefault();
    e.stopImmediatePropagation();
    const message = el.dataset.confirm || 'Вы уверены?';
    askConfirmModal(message).then(ok => {
      if (!ok) return;
      confirmedElements.add(el);
      el.click();
    });
  });
});

// Back to top button
const backToTop = document.createElement('button');
backToTop.className = 'btn btn-orange btn-back-to-top';
backToTop.innerHTML = '<i class="bi bi-arrow-up"></i>';
backToTop.setAttribute('aria-label', 'Наверх');
backToTop.style.cssText = `
  position: fixed;
  bottom: 124px;
  right: 20px;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: none;
  z-index: 1000;
  box-shadow: 0 4px 12px rgba(255, 140, 26, 0.3);
`;
document.body.appendChild(backToTop);

window.addEventListener('scroll', () => {
  if (window.pageYOffset > 300) {
    backToTop.style.display = 'flex';
  } else {
    backToTop.style.display = 'none';
  }
}, { passive: true });

backToTop.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});