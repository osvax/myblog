// ============================================================
// Лайтбокс изображений: клик по элементу с data-lightbox-src
// открывает картинку в полноэкранном модальном окне (увеличение
// сертификатов на страницах проектов портфолио)
// ============================================================

import { Modal } from 'bootstrap';

const LIGHTBOX_ID = 'imageLightboxModal';

function ensureLightbox() {
  let modal = document.getElementById(LIGHTBOX_ID);
  if (modal) {
    return modal;
  }
  modal = document.createElement('div');
  modal.className = 'modal fade';
  modal.id = LIGHTBOX_ID;
  modal.tabIndex = -1;
  modal.setAttribute('aria-hidden', 'true');
  modal.innerHTML = `
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content bg-dark border-0">
        <div class="modal-header border-0">
          <h5 class="modal-title text-white fw-bold mb-0" id="imageLightboxTitle"></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Закрыть"></button>
        </div>
        <div class="modal-body p-2 p-md-3 text-center">
          <img src="" alt="" class="img-fluid rounded" style="max-height: 80vh;">
        </div>
      </div>
    </div>`;
  document.body.appendChild(modal);
  return modal;
}

document.addEventListener('click', (event) => {
  const trigger = event.target.closest('[data-lightbox-src]');
  if (!trigger) {
    return;
  }
  event.preventDefault();
  const modal = ensureLightbox();
  const image = modal.querySelector('img');
  const title = document.getElementById('imageLightboxTitle');
  image.src = trigger.dataset.lightboxSrc;
  image.alt = trigger.dataset.lightboxAlt || '';
  if (title) {
    title.textContent = trigger.dataset.lightboxAlt || '';
  }
  Modal.getOrCreateInstance(modal).show();
});
