(function ($) {
    "use strict";

    jQuery(document).ready(function ($) {

		jQuery(".header-slider").owlCarousel({
            items: 1,
            loop: true,
            dots: true,
            nav: false,
            navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
            autoplay: false,
            autoplayTimeout: 3000,
            smartSpeed: 250
        });
		
        /*------------------------------------
            jQuery MeanMenu
        --------------------------------------*/
        $('.mobile-menu-active').meanmenu({
            meanScreenWidth: "991",
            meanMenuContainer: '.mobile-menu'
        });

        /* last  2 li child add class */
        $('ul.menu > li').slice(-2).addClass('last-elements');

		
		 // MagnificPopup
        $('.photo-gallery').each(function () {
            $(this).magnificPopup({
                delegate: '.gallerypopup',
                mainClass: 'mfp-zoom-in',
                type: 'image',
                tLoading: '',
                gallery: {
                    enabled: true
                },
                removalDelay: 300
            });
        });
		
        // Rocket ScrolltoTop

        $('.scrolltotop').on("click", function () {
            $('html, body').animate({
                scrollTop: 0
            }, 'slow', function () {
            });
        });
		
		 // 11.0 countdown active code
        $('[data-countdown]').each(function () {
            var $this = $(this),
                finalDate = $(this).data('countdown');
            $this.countdown(finalDate, function (event) {
                $(this).find(".days").html(event.strftime("%D"));
                $(this).find(".hours").html(event.strftime("%H"));
                $(this).find(".minutes").html(event.strftime("%M"));
                $(this).find(".seconds").html(event.strftime("%S"));
            });
        });


    });


    jQuery(window).on('load', function () {

        // Sticky Nav
        jQuery(".sticky-nav").sticky({
            topSpacing: 0
        });

    });
	
	// Add/Remove .focus class for accessibility
	jQuery('.navbar').find( 'a' ).on( 'focus blur', function() {
		jQuery( this ).parents( 'ul, li' ).toggleClass( 'focus' );
	} );
	
  /*------------------------------------
		Cart
	--------------------------------------*/

	function overlayToggle() {
		if (jQuery('.cart-overlay').hasClass('active')) {
			jQuery('.cart-overlay').removeClass('active');
		} else {
			jQuery('.cart-overlay').addClass('active');
		}
	}
	$('.cart--open, .cart-overlay, .close-sidenav').on('click', function (e) {
		var $sidecart = $('.sidenav.cart');
		if ($sidecart.hasClass('active')) {
			$sidecart.removeClass('active');
		} else {
			$sidecart.addClass('active');
		}
		overlayToggle();
		e.preventDefault();
	});

	/*------------------------------------
		Search
	--------------------------------------*/

	$('.search__open').on('click', function () {
		$('body').toggleClass('search__box__show__hide');
		return false;
	});

	$('.search__close__btn .search__close__btn_icon').on('click', function () {
		$('body').toggleClass('search__box__show__hide');
		return false;
	});


}(jQuery));