require([
    'jquery'
], function (jQuery) {
    (function ($) {
        $(document).ready(function () {
            // Menu
            $('.nav-toggle').click(function (e) {
                $('.top-bar-abs').addClass('show');
                $('body').addClass('show-popup');
            });

            $('.nav-toggle-close').click(function () {
                $(this).parent('.top-bar-abs').removeClass('show');
                $('body').removeClass('show-popup');
            });
            
            //Responsive menu
            $('.top-bar-abs li.static-menu.dropdown > .dropdown-menu li > .toggle-menu > a').click(function () {
                    if ($(this).hasClass('active')) {
                        $(this).removeClass('active');
                        $(this).parent().siblings('ul').slideUp('fade');
                    } else {
                        $(this).parent().siblings('ul').slideDown('fade');
                        $(this).addClass('active');
                    }
                });
            if ($(window).width() <= 991) {
                if (navigator.vendor == "Apple Computer, Inc.") {
                    $('.top-bar-abs li.static-menu.dropdown > .dropdown-menu li > .toggle-menu > a, .header:not(.header1) .navigation li.static-menu.dropdown > .dropdown-menu li > .toggle-menu > a').click(function () {
                        if ($(this).hasClass('active')) {
                            $('.navigation .nav-main li.dropdown .dropdown-menu').css('min-width', '253px')
                        } else {
                            $('.navigation .nav-main li.dropdown .dropdown-menu').css('min-width', '233px')
                        }
                    });
                }
                $('.top-bar-abs li.static-menu.dropdown > .dropdown-menu li > .toggle-menu > a, .header:not(.header1) .navigation li.static-menu.dropdown > .dropdown-menu li > .toggle-menu > a').click(function () {
                    if ($(this).hasClass('active')) {
                        $(this).removeClass('active');
                        $(this).parent().siblings('ul').slideUp('fade');
                    } else {
                        $(this).parent().siblings('ul').slideDown('fade');
                        $(this).addClass('active');
                    }
                });
                $('.btn-responsive-nav').click(function (e) {
                    $('body').addClass('show-popup');
                    $('body').css('cursor', 'pointer');
                    $('.navigation').addClass('show');
                    e.stopPropagation();
                });

                $(".navigation").on("click", function (e) {
                    e.stopPropagation();
                });
                $("body").on("click", function () {
                    $('.navigation').removeClass('show');
                    $(this).removeClass('show-popup');
                    $(this).css('cursor', 'auto');
                });

                $('.navigation .nav-toggle-close').click(function () {
                    $('.navigation').removeClass('show');
                    $('body').removeClass('show-popup');
                    $('body').css('cursor', 'auto');
                });
            }

            //  sticky menu
            var width_body = $(window).width();
            var width_container = $('body .container').width();
            var pos_cart = Math.round((width_body - width_container) / 2);
            $(window).scroll(function () {
                if ($(this).scrollTop() > 40 && $(this).width() >= 992) {
                    $('.header3.header-sticky-menu .block-cart-header').css('right', pos_cart + 'px');
                    $('.header:not(.header1).header-sticky-menu .logo').css('left', pos_cart + 'px');

                } else {
                    $('.header3 .block-cart-header').css('right', 'inherit');
                    $('.header:not(.header1) .logo').css('left', 'inherit');

                }
                if ($('.header4').hasClass('header-sticky-menu')) {
                    $('.header4 .block-cart-header').css('right', pos_cart + 'px');
                } else {
                    $('.header4 .block-cart-header').css('right', 'inherit');
                }
            });

        });
        //catalog
        $(document).ready(function () {
            if ($(window).width() <= 1023) {
                $('.product-item-info').hover(function () {
                    $(".product-item-photo").attr('href', '').css({'cursor': 'pointer', 'pointer-events': 'none'});
                });
                $(".product-item-photo").click(function (e) {
                    e.preventDefault();
                });
            }

        });

        $(window).resize(function () {
            if ($(window).width() <= 1023) {
                $('.product-item-info').hover(function () {
                    $(".product-item-photo").attr('href', '').css({'cursor': 'pointer', 'pointer-events': 'none'});
                });
                $(".product-item-photo").click(function (e) {
                    e.preventDefault();
                });
            }
        });

    })(jQuery);

});
function setLocation(url) {
    require([
        'jquery'
    ], function (jQuery) {
        (function () {
            window.location.href = url;
        })(jQuery);
    });
}
require([
    'jquery',
    'magnificPopup'
], function (jQuery) {
    (function ($) {
        $(document).ready(function () {
            $('.popup-video').magnificPopup({
                disableOn: 700,
		type: 'iframe',
		mainClass: 'mfp-fade mfp-video-popup',
		removalDelay: 160,
		preloader: false,
		fixedContentPos: false
            });
        });
    })(jQuery);
});
