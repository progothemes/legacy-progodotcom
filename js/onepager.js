var p1bs, p1bn = 0, p1on = 0;


jQuery(function($) {
	// set up blocks
	p1bs = new Array();
	$('.block').each(function(i) {
		var bo = $(this).offset();
		p1bs[i] = bo.top - 98;
		p1bn++;
	});
	
	$(window).bind('scroll.p1', function() {
		var oldon = p1on;
		var wt = $(window).scrollTop();
		$.each( p1bs, function( i, v ) {
			if ( v <= wt ) {
				p1on = i - 1;
			}
		});
		if ( p1on != oldon ) { // was a change
			$('#nav .on').removeClass();
			if ( ( p1on >= 0 ) && ( p1on < p1bn ) ) {
				$('#nav li:eq('+p1on+')').addClass('on');
			}
		}
	}).trigger('scroll.p1');
	
	$('#nav a').each(function(i) {
		$(this).click(function() {
			$('html,body').animate({ scrollTop: p1bs[i+1] }, 600);
			return false;
		});
	});
	$('#logo').click(function() {
		$('html,body').animate({ scrollTop: 0 }, 600);
		return false;
	});
	
	// fix BUY to go straight to Checkout
	$("form.product_form, .wpsc-add-to-cart-button-form").die('submit').live('submit', function() {
		return true; //confirm('Proceed to Checkout?');
	});
});