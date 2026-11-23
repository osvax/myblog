$(document).ready(function(){
	window.onload = function() {
	    document.body.className += " loaded";
	}

	// Main menu
	$(".va_header__switcher").click(function(){
		$(this).toggleClass("active");
		$("body").toggleClass("lock");
		$(".va_menu").toggleClass("active");
	});

	// Discounts slider
	if($('.va_discounts__slider').length > 0){
		$('.va_discounts__slider').slick({
			slidesToShow: 1,
			autoplaySpeed: 3000,
			fade: true,
			dots: false,
			arrows: true,
			nextArrow:'<button type="button" class="slick-next"></button>',
			prevArrow:'<button type="button" class="slick-prev"></button>',
			accessibility: false,
			//autoplay: true,
			//infinite: false,
			responsive: [{
				breakpoint: 768,
				settings: {}
			}]
		});
	}

	// Mainworks slider (before/after)
	if($('.va_mainworks__slider').length > 0){
		$('.va_mainworks__slider').slick({
			slidesToShow: 1,
			autoplaySpeed: 3000,
			fade: true,
			dots: false,
			arrows: true,
			nextArrow:'<button type="button" class="slick-next"></button>',
			prevArrow:'<button type="button" class="slick-prev"></button>',
			accessibility: false,
			//autoplay: true,
			//infinite: false,
			responsive: [{
				breakpoint: 768,
				settings: {}
			}]
		});
	}

	// Reviews slider
	if($('.va_reviews__slider').length > 0){
		$('.va_reviews__slider').slick({
			slidesToShow: 3,
			slidesToScroll: 1,
			autoplaySpeed: 3000,
			dots: false,
			arrows: true,
			nextArrow:'<button type="button" class="slick-next"></button>',
			prevArrow:'<button type="button" class="slick-prev"></button>',
			accessibility: false,
			responsive: [{
				breakpoint: 1024,
				settings: {
					slidesToShow: 2
				}
			}, {
				breakpoint: 768,
				settings: {
					slidesToShow: 1
				}
			}]
		});
	}

	// Animations
	AOS.init({
        offset: 250,
        duration: 1000,
        easing: "ease-out-cubic",
        delay: 100,
        disable: "mobile",
        once: "true"
    })

	// Img to background (for .va_ibg elements)
	$.each($('.va_ibg'), function(index, val) {
		$(this).css('background-image','url("'+$(this).find('img').attr('src')+'")');
	});

	// Object-fit:cover; for IE (legacy support)
	var userAgent, ieReg, ie;
	userAgent = window.navigator.userAgent;
	ieReg = /msie|Trident.*rv[ :]*11\./gi;
	ie = ieReg.test(userAgent);
	if(ie) {
		$(".va_ibg").each(function () {
			var $container = $(this),
			    imgUrl = $container.find("img").prop("src");
			if (imgUrl) {
			  $container.css("backgroundImage", 'url(' + imgUrl + ')').addClass("custom-object-fit");
			}
		});
	}

	// Custom Select (new BEM markup)
	$(document).on('click', '.va_select__trigger', function(e) {
		e.stopPropagation();
		var $select = $(this).closest('.va_select');
		$('.va_select').not($select).removeClass('active').attr('aria-expanded', 'false').find('.va_select__options').slideUp(150);
		$select.toggleClass('active');
		var isExpanded = $select.hasClass('active');
		$select.attr('aria-expanded', isExpanded);
		$select.find('.va_select__options').slideToggle(150);
	});

	$(document).on('click', '.va_select__option', function() {
		var $select = $(this).closest('.va_select');
		var value = $(this).text();
		$select.find('.va_select__value').text(value);
		$select.find('input[type="hidden"]').val(value);
		$select.removeClass('active').attr('aria-expanded', 'false');
		$select.find('.va_select__options').slideUp(150);
	});

	$(document).click(function(e) {
		if (!$(e.target).closest('.va_select').length) {
			$('.va_select').removeClass('active').attr('aria-expanded', 'false');
			$('.va_select__options').slideUp(150);
		}
	});

	// Legacy custom select support (for old markup if any)
	$('.select').click(function(event) {
		if(!$(this).hasClass('disabled')){
			$('.select').not(this).removeClass('active').find('.select-options').slideUp(50);
			$(this).toggleClass('active');
			$(this).find('.select-options').slideToggle(50);
		}
	});
	$('.select-options__value').click(function() {
		$(this).parents('.select').find('.select-title__value').html($(this).html());
		if($.trim($(this).data('value'))!=''){
			$(this).parents('.select').find('input').val($(this).data('value'));
		}else{
			$(this).parents('.select').find('input').val($(this).html());
		}
	});
	$(document).click(function(e) {
		if (!$(e.target).is(".select *")) {
			$('.select').removeClass('active');
			$('.select-options').slideUp(50);
		};
	});

	// Modal window - callback (new markup uses data-arcticmodal attribute)
	$(document).on('click', '[data-arcticmodal="modal-callback"]', function(e){
		e.preventDefault();
		$('#modal-callback').arcticmodal();
	});

	// Modal window - callback big
	$(document).on('click', '[data-arcticmodal="modal-callback-big"]', function(e){
		e.preventDefault();
		$('#modal-callback-big').arcticmodal();
	});

	// Legacy modal support
	$(".btn-callback").click(function(e){
		e.preventDefault();
		$('#modal-callback').arcticmodal();
	});

	$(".btn-callback-big").click(function(e){
		e.preventDefault();
		$('#modal-callback-big').arcticmodal();
	});

	// Phone mask
	$(".phone-mask").mask("+7 (999) 999 99 99");

	// Accordion (FAQ page)
    if ($(".va_accordion__trigger").length > 0) {
        $('.va_accordion__trigger').on('click', function(e){
			e.preventDefault();
			var $item = $(this).closest('.va_accordion__item');
			if($item.hasClass('active')){
				$item.removeClass('active');
				$item.find('.va_accordion__content').slideUp();
			}
			else{
				$('.va_accordion__item').removeClass('active');
				$('.va_accordion__content').slideUp();
				$item.addClass('active').find('.va_accordion__content').slideDown();
			}
		});
    }

	// Legacy accordion support
    if ($(".accordion__box").length > 0) {
        $('.accordion__box').on('click', function(e){
			e.preventDefault();
			if($(this).hasClass('active')){
				$(".accordion__box").removeClass('active');
				$(".accordion__inner").slideUp();
			}
			else{
				$(".accordion__box").removeClass('active');
				$(".accordion__inner").slideUp();
				$(this).addClass('active').next().slideDown();
			}
		});
    }

	// News "Show more" button
	$(document).on('click', '.va_news-list__show', function(e) {
		e.preventDefault();
		// In a real implementation, this would load more items via AJAX
		// For now, just hide the button
		$(this).hide();
	});

	// Reviews "Show more" button (legacy)
	$(".reviews__btn").click(function(e) {
		e.preventDefault();
		$(this).hide();
		$(this).parents(".reviews").find(".hidden").slideDown();
	});

});