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

});
