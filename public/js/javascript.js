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

    var navbar = $('.navbar:first');

    let head_height = $('header').outerHeight();
    let nav_height = navbar.outerHeight();

    let nav_start = (head_height - nav_height);

    updateNavbar();

    $(window).scroll(function() {
        updateNavbar();
    });

    function updateNavbar() {
        windowTop = $(window).scrollTop();
        if (windowTop > nav_start) {
            navbar.addClass("bg-dark shadow");
        } else {
            navbar.removeClass("bg-dark shadow")
        }
    }

});