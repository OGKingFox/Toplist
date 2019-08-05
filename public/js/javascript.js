$(document).ready(function() {
	$('[data-toggle="tooltip"]').tooltip();
    $('.carousel').carousel();

    $('#toTop').on('click',function (e) {
        e.preventDefault();
        var target = this.hash;
        var $target = $(target);
        $('html, body').stop().animate({
            'scrollTop': 0
        }, 900, 'swing');
    });

    $(document).on("click", 'button', function(e) {
        if ($(this).hasClass("disabled")) {
            e.preventDefault();
            return true;
        }
    });

    var windowTop = $(window).scrollTop();
    var windowBottom = ($(window).height() - windowTop);
    var navbar = $('.navbar');


    updateNavbar();

    $(window).scroll(function() {
        updateNavbar();
    });

    function updateNavbar() {
        windowTop = $(window).scrollTop();
        if (windowTop > 300) {
            navbar.addClass("bg-light shadow navbar-light");
            navbar.removeClass("bg-transparent navbar-dark");
        } else {
            navbar.removeClass("bg-light shadow navbar-light");
            navbar.addClass("bg-transparent navbar-dark");
        }
    }

});
