/* TEXTILE interactions: dependency-free vanilla JavaScript. */
(function () {
  'use strict';

  const body = document.body;
  const menu = document.querySelector('.va_header-menu');
  const switcher = document.querySelector('.va_header-menu__switcher');
  const close = document.querySelector('.va_header-menu__close');

  const setMenu = (open) => {
    if (!menu) return;
    menu.classList.toggle('va_is-open', open);
    menu.classList.toggle('va_active', open);
    body.classList.toggle('va_lock', open);
    if (switcher) switcher.setAttribute('aria-expanded', String(open));
  };

  switcher?.addEventListener('click', () => setMenu(true));
  close?.addEventListener('click', () => setMenu(false));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setMenu(false);
  });

  document.querySelectorAll('a.va_scrollto').forEach((link) => {
    link.addEventListener('click', (event) => {
      const target = document.querySelector(link.getAttribute('href'));
      if (!target) return;
      event.preventDefault();
      setMenu(false);
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  const header = document.querySelector('.va_header');
  window.addEventListener('scroll', () => {
    header?.classList.toggle('va_header_scroll', window.scrollY >= 10);
  }, { passive: true });

  const createSlider = (selector, linkedSelector) => {
    const slider = document.querySelector(selector);
    if (!slider) return;
    const slides = [...slider.children];
    if (slides.length < 2) return;
    let active = 0;
    const linked = linkedSelector ? document.querySelector(linkedSelector) : null;
    const render = (index) => {
      active = (index + slides.length) % slides.length;
      slides.forEach((slide, i) => {
        slide.hidden = i !== active;
        slide.classList.toggle('va_is-active', i === active);
      });
      linked && [...linked.children].forEach((slide, i) => { slide.hidden = i !== active; });
    };
    render(0);
    window.setInterval(() => render(active + 1), selector.includes('mainscreen') ? 5000 : 7000);
  };
  createSlider('.va_mainscreen-slider', '.va_mainscreen-bgslider');
  createSlider('.va_reviews-slider');

  document.querySelectorAll('.fancybox').forEach((link) => {
    link.addEventListener('click', (event) => {
      const source = link.getAttribute('href');
      if (!source || !/\.(jpg|jpeg|png|gif|svg)(\?.*)?$/i.test(source)) return;
      event.preventDefault();
      const overlay = document.createElement('div');
      overlay.className = 'va_lightbox';
      overlay.innerHTML = `<button class="va_lightbox__close" type="button" aria-label="Закрыть">×</button><img src="${source}" alt="Увеличенное изображение">`;
      document.body.appendChild(overlay);
      const remove = () => overlay.remove();
      overlay.addEventListener('click', (e) => { if (e.target === overlay || e.target.classList.contains('va_lightbox__close')) remove(); });
      document.addEventListener('keydown', function escape(e) { if (e.key === 'Escape') { remove(); document.removeEventListener('keydown', escape); } });
    });
  });

  document.querySelectorAll('.va_select').forEach((select) => {
    const title = select.querySelector('.va_select-title');
    const value = select.querySelector('.va_select-title__value');
    const options = select.querySelector('.va_select-options');
    if (!title || !options) return;
    title.setAttribute('tabindex', '0');
    const toggle = () => select.classList.toggle('va_active');
    title.addEventListener('click', toggle);
    title.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); toggle(); }
    });
    select.querySelectorAll('.va_select-options__value').forEach((option) => {
      option.setAttribute('tabindex', '0');
      option.addEventListener('click', () => {
        if (value) { value.textContent = option.textContent; value.classList.add('va_select-full'); }
        const input = select.querySelector('input[type="hidden"]');
        if (input) input.value = option.dataset.value || option.textContent.trim();
        select.classList.remove('va_active');
      });
    });
  });
  document.addEventListener('click', (event) => {
    document.querySelectorAll('.va_select.va_active').forEach((select) => {
      if (!select.contains(event.target)) select.classList.remove('va_active');
    });
  });

  const teamColumns = [...document.querySelectorAll('.va_team-column')];
  const showTeam = document.querySelector('.va_team__show');
  const hideTeam = document.querySelector('.va_team__hide');
  const updateTeam = (count) => {
    teamColumns.forEach((item, index) => item.classList.toggle('va_is-hidden', index >= count));
    if (showTeam) showTeam.hidden = count >= teamColumns.length;
    if (hideTeam) hideTeam.hidden = count <= 3;
  };
  updateTeam(3);
  showTeam?.addEventListener('click', () => updateTeam(teamColumns.length));
  hideTeam?.addEventListener('click', () => updateTeam(3));

  document.querySelectorAll('.va_profile-row').forEach((row) => {
    const items = [...row.querySelectorAll('.va_profile-item')];
    const images = [...row.closest('.va_profile')?.querySelectorAll('.va_profile-item__image') || []];
    items.forEach((item, index) => item.addEventListener('click', () => {
      items.forEach((entry) => entry.classList.remove('va_active'));
      images.forEach((image) => image.classList.remove('va_active'));
      item.classList.add('va_active');
      images[index]?.classList.add('va_active');
    }));
  });

  const works = [...document.querySelectorAll('.va_works-item')];
  const worksShow = document.querySelector('.va_works-show__btn');
  const worksHide = document.querySelector('.va_works-hide__btn');
  worksShow?.addEventListener('click', () => {
    works.forEach((item) => item.classList.remove('va_hidden'));
    worksShow.hidden = true;
    if (worksHide) worksHide.hidden = false;
  });
  worksHide?.addEventListener('click', () => {
    works.slice(4).forEach((item) => item.classList.add('va_hidden'));
    worksShow.hidden = false;
    worksHide.hidden = true;
  });

  document.querySelectorAll('.va_phone-mask').forEach((input) => {
    input.addEventListener('input', () => {
      const digits = input.value.replace(/\D/g, '').replace(/^7/, '').slice(0, 10);
      input.value = digits ? `+7 (${digits.slice(0, 3)}${digits.length > 3 ? ') ' : ''}${digits.slice(3, 6)}${digits.length > 6 ? '-' : ''}${digits.slice(6, 8)}${digits.length > 8 ? '-' : ''}${digits.slice(8, 10)}` : '';
    });
  });
}());
