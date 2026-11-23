/* ============================================================
   FORMA STUDIO — клиентская логика (префикс va_)
   ============================================================ */
(function () {
  "use strict";

  /* Класс для отложенных анимаций: без JS контент остаётся видимым */
  document.body.classList.add("has-js");

  /* ------------------------------------------------------------
     1. Шапка: тёмный фон при скролле
     ------------------------------------------------------------ */
  var header = document.getElementById("header");
  var burger = document.getElementById("burger");
  var nav = document.getElementById("nav");

  function onScrollHeader() {
    if (window.scrollY > 40) {
      header.classList.add("is-scrolled");
    } else {
      header.classList.remove("is-scrolled");
    }
  }

  window.addEventListener("scroll", onScrollHeader, { passive: true });
  onScrollHeader();

  /* ------------------------------------------------------------
     2. Бургер-меню
     ------------------------------------------------------------ */
  function closeMenu() {
    header.classList.remove("is-open");
    document.body.classList.remove("is-locked");
    burger.setAttribute("aria-expanded", "false");
    burger.setAttribute("aria-label", "Открыть меню");
  }

  function toggleMenu() {
    var isOpen = header.classList.toggle("is-open");
    document.body.classList.toggle("is-locked", isOpen);
    burger.setAttribute("aria-expanded", String(isOpen));
    burger.setAttribute("aria-label", isOpen ? "Закрыть меню" : "Открыть меню");
  }

  burger.addEventListener("click", toggleMenu);

  nav.querySelectorAll("a").forEach(function (link) {
    link.addEventListener("click", closeMenu);
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && header.classList.contains("is-open")) {
      closeMenu();
      burger.focus();
    }
  });

  /* ------------------------------------------------------------
     3. Плавная прокрутка с учётом высоты шапки
     ------------------------------------------------------------ */
  document.querySelectorAll('a[href^="#"]').forEach(function (link) {
    link.addEventListener("click", function (event) {
      var id = link.getAttribute("href");
      if (!id || id === "#") return;

      var target = document.querySelector(id);
      if (!target) return;

      event.preventDefault();
      var offset = header.offsetHeight + 10;
      var top = target.getBoundingClientRect().top + window.pageYOffset - offset;

      window.scrollTo({
        top: Math.max(top, 0),
        behavior: "smooth"
      });
    });
  });

  /* ------------------------------------------------------------
     4. Карусель проектов
     ------------------------------------------------------------ */
  var track = document.getElementById("projectsTrack");
  var prevBtn = document.getElementById("prevProject");
  var nextBtn = document.getElementById("nextProject");

  function stepSize() {
    var card = track.querySelector(".va-card");
    if (!card) return 0;
    var gap = parseFloat(getComputedStyle(track).columnGap || "24") || 24;
    return card.getBoundingClientRect().width + gap;
  }

  function updateArrows() {
    var maxScroll = track.scrollWidth - track.clientWidth - 1;
    prevBtn.disabled = track.scrollLeft <= 1;
    nextBtn.disabled = track.scrollLeft >= maxScroll;
  }

  if (track && prevBtn && nextBtn) {
    prevBtn.addEventListener("click", function () {
      track.scrollBy({ left: -stepSize(), behavior: "smooth" });
    });

    nextBtn.addEventListener("click", function () {
      track.scrollBy({ left: stepSize(), behavior: "smooth" });
    });

    track.addEventListener("scroll", updateArrows, { passive: true });
    window.addEventListener("resize", updateArrows);
    updateArrows();

    /* Перетаскивание мышью (на тач-устройствах работает нативный свайп) */
    var isDown = false;
    var startX = 0;
    var startScroll = 0;
    var moved = false;

    track.addEventListener("pointerdown", function (event) {
      if (event.pointerType !== "mouse") return;
      isDown = true;
      moved = false;
      startX = event.clientX;
      startScroll = track.scrollLeft;
      track.style.cursor = "grabbing";
    });

    window.addEventListener("pointermove", function (event) {
      if (!isDown) return;
      var delta = event.clientX - startX;
      if (Math.abs(delta) > 4) moved = true;
      track.scrollLeft = startScroll - delta;
    });

    window.addEventListener("pointerup", function () {
      if (!isDown) return;
      isDown = false;
      track.style.cursor = "";
      if (moved) {
        /* гасим случайный клик по ссылке после перетаскивания */
        track.addEventListener(
          "click",
          function (e) {
            e.preventDefault();
          },
          { once: true }
        );
      }
    });
  }

  /* ------------------------------------------------------------
     5. Появление блоков при скролле
     ------------------------------------------------------------ */
  var revealItems = document.querySelectorAll(".va-reveal");

  if ("IntersectionObserver" in window && revealItems.length) {
    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        });
      },
      { rootMargin: "0px 0px -8% 0px", threshold: 0.08 }
    );

    revealItems.forEach(function (item, index) {
      item.style.transitionDelay = (index % 4) * 60 + "ms";
      observer.observe(item);
    });
  } else {
    revealItems.forEach(function (item) {
      item.classList.add("is-visible");
    });
  }

  /* ------------------------------------------------------------
     6. Валидация форм
     ------------------------------------------------------------ */
  var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[A-Za-zА-Яа-яЁё]{2,}$/;

  function setFieldError(field, message) {
    var error = field.querySelector(".va-field__error");
    if (!error) return;
    error.textContent = message;
    field.classList.toggle("is-invalid", Boolean(message));
  }

  /* --- Форма каталога --- */
  var catalogForm = document.getElementById("catalogForm");

  if (catalogForm) {
    var catalogEmail = document.getElementById("catalogEmail");
    var catalogAgree = document.getElementById("catalogAgree");
    var catalogAgreeError = document.getElementById("catalogAgreeError");
    var catalogSuccess = document.getElementById("catalogSuccess");
    var catalogEmailField = catalogEmail.closest(".va-field");

    catalogEmail.addEventListener("input", function () {
      setFieldError(catalogEmailField, "");
      catalogSuccess.hidden = true;
    });

    catalogAgree.addEventListener("change", function () {
      catalogAgreeError.textContent = "";
      catalogAgreeError.classList.remove("is-visible");
    });

    catalogForm.addEventListener("submit", function (event) {
      event.preventDefault();

      var ok = true;
      var email = catalogEmail.value.trim();

      if (!email) {
        setFieldError(catalogEmailField, "Укажите e-mail.");
        ok = false;
      } else if (!EMAIL_RE.test(email)) {
        setFieldError(catalogEmailField, "Проверьте формат e-mail.");
        ok = false;
      } else {
        setFieldError(catalogEmailField, "");
      }

      if (!catalogAgree.checked) {
        catalogAgreeError.textContent = "Необходимо согласие на обработку данных.";
        catalogAgreeError.classList.add("is-visible");
        ok = false;
      } else {
        catalogAgreeError.textContent = "";
        catalogAgreeError.classList.remove("is-visible");
      }

      if (!ok) {
        catalogSuccess.hidden = true;
        return;
      }

      catalogSuccess.hidden = false;
      catalogForm.reset();
      catalogEmail.focus({ preventScroll: true });
    });
  }

  /* --- Форма заявки --- */
  var ctaForm = document.getElementById("ctaForm");

  if (ctaForm) {
    var ctaEmail = document.getElementById("ctaEmail");
    var ctaMsg = document.getElementById("ctaMsg");
    var ctaTimer = null;

    ctaEmail.addEventListener("input", function () {
      ctaEmail.style.outline = "";
      ctaMsg.textContent = "";
      ctaMsg.classList.remove("is-error");
    });

    ctaForm.addEventListener("submit", function (event) {
      event.preventDefault();

      var email = ctaEmail.value.trim();

      if (!EMAIL_RE.test(email)) {
        ctaEmail.style.outline = "1px solid #ffb4a8";
        ctaMsg.textContent = "Укажите корректный e-mail.";
        ctaMsg.classList.add("is-error");
        ctaEmail.focus();
        return;
      }

      ctaEmail.style.outline = "";
      ctaMsg.classList.remove("is-error");
      ctaMsg.textContent = "Спасибо! Мы свяжемся с вами в ближайшее время.";
      ctaForm.reset();

      if (ctaTimer) window.clearTimeout(ctaTimer);
      ctaTimer = window.setTimeout(function () {
        ctaMsg.textContent = "";
      }, 8000);
    });
  }

  /* ------------------------------------------------------------
     7. Подсветка активного пункта меню
     ------------------------------------------------------------ */
  var sections = ["services", "projects", "about", "contacts"]
    .map(function (id) {
      return document.getElementById(id);
    })
    .filter(Boolean);

  var navLinks = nav.querySelectorAll(".va-nav__link");

  function setActiveLink() {
    var pos = window.scrollY + header.offsetHeight + 80;
    var current = null;

    sections.forEach(function (section) {
      if (section.offsetTop <= pos) current = section.id;
    });

    navLinks.forEach(function (link) {
      link.classList.toggle(
        "is-active",
        current !== null && link.getAttribute("href") === "#" + current
      );
    });
  }

  window.addEventListener("scroll", setActiveLink, { passive: true });
  setActiveLink();
})();