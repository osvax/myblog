/*
 * АГРОПРОСПЕКТ — сценарии сайта
 *
 * Сценарии:
 *  - плавные якорные переходы;
 *  - выпадающий каталог техники (desktop, две кнопки-бургеры);
 *  - полноэкранное мобильное меню + каталог внутри него;
 *  - слайдеры hero и обложек (Owl Carousel);
 *  - модальная форма «Заказать звонок» / «Запросить предложение»;
 *  - блокировка прокрутки, закрытие по Esc / клику вне.
 */
"use strict";

$(function () {
  var $body = $("body");
  var $modal = $(".va_modal");
  var $submenu = $(".va_submenu");
  var $navPopup = $(".va_nav--popup");

  function isMobile() {
    return window.innerWidth <= 768;
  }

  /* ---------- Плавные якорные переходы ---------- */
  $(document).on("click", 'a[href^="#"]', function (e) {
    var target = this.getAttribute("href");

    // Пустые якоря "href='#'": без действий
    if (!target || target === "#" || $(target).length === 0) {
      e.preventDefault();
      return;
    }

    e.preventDefault();
    var top = Math.max($(target).offset().top - 20, 0);
    $("html, body").animate({ scrollTop: top }, 500);
    closeNav();
  });

  /* ---------- Мобильное меню ---------- */
  function openNav() {
    $navPopup.addClass("va_nav--visible");
    if (isMobile()) {
      $body.addClass("va_scroll-disabled va_menu-open");
    }
    $(".va_header__burger")
      .children(".va_burger")
      .addClass("va_burger--active")
      .end()
      .attr("aria-expanded", "true");
  }

  function closeNav() {
    $navPopup.removeClass("va_nav--visible");
    $body.removeClass("va_scroll-disabled va_menu-open");
    $(".va_header__burger")
      .children(".va_burger")
      .removeClass("va_burger--active")
      .end()
      .attr("aria-expanded", "false");
    $(".va_nav__catalog").removeClass("va_nav__catalog--open");
    $(".js-open-catalog").attr("aria-expanded", "false");
  }

  $(".js-close-nav").on("click", closeNav);

  /* ---------- Каталог: desktop-выпадашка / мобильный список ---------- */
  $(".js-burger-btn").on("click", function (e) {
    e.stopPropagation();
    var $btn = $(this);

    // На мобильных бургер шапки открывает меню
    if (isMobile()) {
      if ($navPopup.hasClass("va_nav--visible")) {
        closeNav();
      } else {
        openNav();
      }
      return;
    }

    // Desktop: переключение выпадающего каталога
    $submenu.toggleClass("va_submenu--visible");
    var opened = $submenu.hasClass("va_submenu--visible");
    $(".js-burger-btn")
      .children(".va_burger")
      .toggleClass("va_burger--active", opened)
      .end()
      .attr("aria-expanded", opened ? "true" : "false");
  });

  // Каталог внутри мобильного меню
  $(".js-open-catalog").on("click", function (e) {
    e.stopPropagation();
    var $list = $(".va_nav__catalog");
    $list.toggleClass("va_nav__catalog--open");
    $(this).attr("aria-expanded", $list.hasClass("va_nav__catalog--open") ? "true" : "false");
  });

  // Клик вне закрывает выпадающий каталог
  $(document).on("click", function (e) {
    if ($(e.target).closest(".va_submenu, .js-burger-btn").length) {
      return;
    }
    $submenu.removeClass("va_submenu--visible");
    $(".js-burger-btn")
      .children(".va_burger")
      .removeClass("va_burger--active")
      .end()
      .attr("aria-expanded", "false");
  });

  /* ---------- Слайдеры ---------- */
  var carouselSettings = {
    items: 1,
    rewind: true,
    mouseDrag: false,
    touchDrag: true,
    nav: true,
    dots: false
  };

  $(".js-presentation-slider").addClass("owl-carousel").owlCarousel(carouselSettings);

  $(".js-hero-slider").addClass("owl-carousel").owlCarousel($.extend({}, carouselSettings, {
    autoplay: false,
    autoplayTimeout: 2000,
    autoplayHoverPause: true
  }));

  /* ---------- Модальное окно ---------- */
  function openModal(title) {
    if (title) {
      $(".js-form-title").text(title);
    }
    $modal.addClass("va_modal--visible");
    $body.addClass("va_scroll-disabled");

    window.setTimeout(function () {
      $(".va_input").first().trigger("focus");
    }, 250);
  }

  function closeModal() {
    $modal.removeClass("va_modal--visible");
    $body.removeClass("va_scroll-disabled");
  }

  $(".js-open-popup").on("click", function () {
    openModal($(this).attr("data-form-title"));
  });

  $(".js-close-popup").on("click", closeModal);

  $modal.on("click", function (e) {
    if (e.target === this) {
      closeModal();
    }
  });

  // Esc закрывает попап и мобильное меню
  $(document).on("keydown", function (e) {
    if (e.key === "Escape" || e.keyCode === 27) {
      if ($modal.hasClass("va_modal--visible")) {
        closeModal();
      }
      closeNav();
    }
  });

  // Отправка формы (демо)
  $(".js-send").on("click", function (e) {
    e.preventDefault();
    console.log("Sent!");
    $(this).closest("form")[0].reset();
  });

  /* ---------- Сброс состояний при смене брейкпоинта ---------- */
  $(window).on("resize", function () {
    if (!isMobile()) {
      $navPopup.removeClass("va_nav--visible");
      $body.removeClass("va_scroll-disabled va_menu-open");
      $(".va_header__burger")
        .children(".va_burger")
        .removeClass("va_burger--active")
        .end()
        .attr("aria-expanded", "false");
    }
  });
});
