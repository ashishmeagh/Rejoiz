$(document).ready(function() {
	/* scroll to */
	setTimeout(function() {
		var target = window.location.hash.substr(1);
		goTo(target);
	}, 10);

	$('.nav a').click(function() {
		target = $(this).attr('href').split('#')[1];
		goTo(target);
	});

	/* toTop button */
	var scrolltop;
	$(document).scroll(function() {
		scrolltop = $(this).scrollTop();
		if (scrolltop > 600) {
			$('.to-top').fadeIn(300);
		} else {
			$('.to-top').fadeOut(300);
		}
	});

	$('.to-top').click(function() {
		$("html, body").stop().animate({scrollTop:0}, 500);
	})

	if(navigator.userAgent.match(/Trident\/7\./)) { // if IE
        $('body').on("mousewheel", function () {
            // remove default behavior
            event.preventDefault(); 

            //scroll without smoothing
            var wheelDelta = event.wheelDelta;
            var currentScrollPosition = window.pageYOffset;
            window.scrollTo(0, currentScrollPosition - wheelDelta);
        });
	}

	/* show js button */
	$('.btn-showjs').click(function() {
		var target = $(this).data('target');

		$('div[data-target="'+target+'"]').slideToggle(200);

		return false;
	});
});



function goTo(target) {
	$('h2').each(function() {
		if ($(this).data('target') == target) {
			var scroll = $(this).offset().top;
			$("html, body").stop().animate({scrollTop:scroll}, 500);
			return false;
		}
	})
}